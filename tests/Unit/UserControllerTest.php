<?php

namespace Tests\Unit;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
{
    public function testLoginInvalidCredentials()
    {
        $client = static::createClient();

        // Accès à la page de connexion
        $crawler = $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        // Soumission du formulaire avec mauvais identifiants
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'utilisateur_inexistant@test.com',
            '_password' => 'mauvaismotdepasse',
        ]);
        $client->submit($form);

        $this->assertStringContainsString('Invalid credentials', $client->getResponse()->getContent());
    }

    public function testLoginWithValidCredentials()
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Créer un utilisateur de test si non existant
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'test@test.test']);
        if (!$user) {
            $user = new User();
            $user->setEmail('test@test.test');
            $user->setUsername('testuser');
            $user->setPassword($passwordHasher->hashPassword($user, 'test'));
            $user->setRoles(['ROLE_USER']);
            $entityManager->persist($user);
            $entityManager->flush();
        }

        // Accès à la page de connexion
        $crawler = $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        // Soumission du formulaire avec bons identifiants
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'test@test.test',
            '_password' => 'test',
        ]);
        $client->submit($form);

        // Vérifie que l'utilisateur est connecté (adapte le sélecteur selon ton template)
        $this->assertSelectorTextContains('body', 'Se déconnecter');
    }

    public function testUserRoleAssignment()
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail('roleuser@test.com');
        $user->setUsername('roleuser');
        $user->setPassword($passwordHasher->hashPassword($user, 'test'));
        $user->setRoles(['ROLE_ADMIN']);
        $entityManager->persist($user);
        $entityManager->flush();

        $savedUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'roleuser@test.com']);
        $this->assertContains('ROLE_ADMIN', $savedUser->getRoles());
    }

    public function testAdminAccessUserManagement()
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $admin = new User();
        $admin->setEmail('admin2@test.com');
        $admin->setUsername('adminuser2');
        $admin->setPassword($passwordHasher->hashPassword($admin, 'test'));
        $admin->setRoles(['ROLE_ADMIN']);
        $entityManager->persist($admin);
        $entityManager->flush();

        $client->loginUser($admin);
        $crawler = $client->request('GET', '/users');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table'); // Adapte selon ta vue
    }

    public function testUserCannotAccessUserManagement()
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail('user@test.com');
        $user->setUsername('useruser');
        $user->setPassword($passwordHasher->hashPassword($user, 'test'));
        $user->setRoles(['ROLE_USER']);
        $entityManager->persist($user);
        $entityManager->flush();

        $client->loginUser($user);
        $client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(403); // ou redirection selon ta config
    }
}