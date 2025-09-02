<?php

namespace App\Tests\Functional;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Entity\Task;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// Test fonctionnel du contrôleur User (authentification)
// Test fonctionnel du contrôleur User (authentification et gestion des droits)
class UserControllerTest extends WebTestCase
    // Test : suppression d'un utilisateur rattache ses tâches à l'utilisateur anonyme
    {
    public function testDeleteUserAttachesTasksToAnonymous()
        // Nettoyage de l'admin existant pour éviter les doublons
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $existingAdmin = $entityManager->getRepository(User::class)->findOneBy(['username' => 'admin2']);
        if ($existingAdmin) {
            $entityManager->remove($existingAdmin);
            $entityManager->flush();
        }

        // Nettoyage préalable
        foreach (['deleteuser@test.com', 'anonyme@todo-co.local'] as $email) {
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingUser) {
                $entityManager->remove($existingUser);
            }
        }
        // Supprime la tâche existante si elle existe
        $existingTask = $entityManager->getRepository(Task::class)->findOneBy(['title' => 'Tâche à rattacher']);
        if ($existingTask) {
            $entityManager->remove($existingTask);
        }
        $entityManager->flush();

        // Création de l'utilisateur à supprimer
        $user = new User();
        $user->setEmail('deleteuser@test.com');
        $user->setUsername('deleteuser');
        $user->setPassword($passwordHasher->hashPassword($user, 'deletepass'));
        $user->setRoles(['ROLE_USER']);
        $entityManager->persist($user);

        // Création d'une tâche associée
        $task = new Task();
        $task->setTitle('Tâche à rattacher');
        $task->setContent('Contenu de la tâche à rattacher');
        $task->setAuthor($user);
        $entityManager->persist($task);
        $entityManager->flush();

        // Création d'un admin pour la suppression
        $admin = new User();
        $admin->setEmail('admin2@test.com');
        $admin->setUsername('admin2');
        $admin->setPassword($passwordHasher->hashPassword($admin, 'adminpass'));
        $admin->setRoles(['ROLE_ADMIN']);
        $entityManager->persist($admin);
        $entityManager->flush();

        // Connexion en tant qu'admin
        $client->loginUser($admin);
        // Suppression de l'utilisateur
        $client->request('GET', '/users/' . $user->getId() . '/delete');
        $this->assertResponseRedirects('/users');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "L'utilisateur a été supprimé et ses tâches ont été rattachées à l'utilisateur anonyme.");

        // Vérifie que l'utilisateur anonyme existe
        $anonymous = $entityManager->getRepository(User::class)->findOneBy(['username' => 'anonyme']);
        $this->assertNotNull($anonymous, "L'utilisateur anonyme doit être créé automatiquement.");

        // Vérifie que la tâche est rattachée à l'utilisateur anonyme
        $updatedTask = $entityManager->getRepository(Task::class)->findOneBy(['title' => 'Tâche à rattacher']);
        $this->assertEquals($anonymous->getId(), $updatedTask->getAuthor()->getId(), "La tâche doit être rattachée à l'utilisateur anonyme.");
    }
    // Test : accès admin à la gestion des utilisateurs
    public function testAdminCanAccessUserManagement()
    {
        // Création d'un admin
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $existingAdmin = $entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@test.com']);
        $existingUsernameAdmin = $entityManager->getRepository(User::class)->findOneBy(['username' => 'admin']);
        if ($existingAdmin ) {
            $entityManager->remove($existingAdmin);
            $entityManager->flush();
        } elseif ($existingUsernameAdmin){
            $entityManager->remove($existingUsernameAdmin);
            $entityManager->flush();
        }
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setUsername('admin');
        $admin->setPassword($passwordHasher->hashPassword($admin, 'adminpass'));
        $admin->setRoles(['ROLE_ADMIN']);
        $entityManager->persist($admin);
        $entityManager->flush();
        // Connexion et accès à /users
        $client->loginUser($admin);
        $client->request('GET', '/users');
        $this->assertResponseIsSuccessful();
    }

    // Test : accès interdit pour un utilisateur simple
    public function testUserCannotAccessUserManagement()
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'user@test.com']);
        if ($existingUser) {
            $entityManager->remove($existingUser);
            $entityManager->flush();
        }
        $user = new User();
        $user->setEmail('user@test.com');
        $user->setUsername('user');
        $user->setPassword($passwordHasher->hashPassword($user, 'userpass'));
        $user->setRoles(['ROLE_USER']);
        $entityManager->persist($user);
        $entityManager->flush();
        $client->loginUser($user);
        $client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(403);
    }

    // Test : création d'un utilisateur via le formulaire
    public function testAdminCanCreateUser()
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        // Nettoyage de l'utilisateur de test pour éviter les doublons
        $existingAdmin = $entityManager->getRepository(User::class)->findOneBy(['username' => 'admincreate']);
        if ($existingAdmin) {
            $entityManager->remove($existingAdmin);
            $entityManager->flush();
        }
        // Création d'un admin
        $admin = new User();
        $admin->setEmail('admincreate@test.com');
        $admin->setUsername('admincreate');
        $admin->setPassword($passwordHasher->hashPassword($admin, 'adminpass'));
        $admin->setRoles(['ROLE_ADMIN']);
        $entityManager->persist($admin);
        $entityManager->flush();
        $client->loginUser($admin);
        $crawler = $client->request('GET', '/users/create');
        $this->assertResponseIsSuccessful();
        // Nettoyage de l'utilisateur cible pour éviter les doublons
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'newuser@test.com']);
        if ($existingUser) {
            $entityManager->remove($existingUser);
            $entityManager->flush();
        }
        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'newuser',
            'user[password][first]' => 'password',
            'user[password][second]' => 'password',
            'user[email]' => 'newuser@test.com',
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/users');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "L'utilisateur a bien été ajouté.");
    }

    // Test : édition d'un utilisateur
    public function testAdminCanEditUser()
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        // Nettoyage de l'admin et de l'utilisateur de test pour éviter les doublons
        $existingAdmin = $entityManager->getRepository(User::class)->findOneBy(['username' => 'adminedit']);
        if ($existingAdmin) {
            $entityManager->remove($existingAdmin);
            $entityManager->flush();
        }
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['username' => 'edituser']);
        if ($existingUser) {
            $entityManager->remove($existingUser);
            $entityManager->flush();
        }
        // Création d'un admin et d'un user
        $admin = new User();
        $admin->setEmail('adminedit@test.com');
        $admin->setUsername('adminedit');
        $admin->setPassword($passwordHasher->hashPassword($admin, 'adminpass'));
        $admin->setRoles(['ROLE_ADMIN']);
        $entityManager->persist($admin);
        $user = new User();
        $user->setEmail('edituser@test.com');
        $user->setUsername('edituser');
        $user->setPassword($passwordHasher->hashPassword($user, 'userpass'));
        $user->setRoles(['ROLE_USER']);
        $entityManager->persist($user);
        $entityManager->flush();
        $client->loginUser($admin);
        $crawler = $client->request('GET', '/users/' . $user->getId() . '/edit');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Modifier')->form([
            'user[username]' => 'edituser',
            'user[password][first]' => 'newpass',
            'user[password][second]' => 'newpass',
            'user[email]' => 'edituser@test.com',
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/users');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "L'utilisateur a bien été modifié");
    }

    
    public function testCreateUserFormValidationError()
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        // Nettoyage de l'utilisateur adminform pour éviter les doublons
        $existingAdmin = $entityManager->getRepository(User::class)->findOneBy(['username' => 'adminform']);
        if ($existingAdmin) {
            $entityManager->remove($existingAdmin);
            $entityManager->flush();
        }
        $admin = new User();
        $admin->setEmail('adminform@test.com');
        $admin->setUsername('adminform');
        $admin->setPassword($passwordHasher->hashPassword($admin, 'adminpass'));
        $admin->setRoles(['ROLE_ADMIN']);
        $entityManager->persist($admin);
        $entityManager->flush();
        $client->loginUser($admin);
        $crawler = $client->request('GET', '/users/create');
        $this->assertResponseIsSuccessful();
        // Soumission du formulaire avec mot de passe non concordant
        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'baduser',
            'user[password][first]' => 'password1',
            'user[password][second]' => 'password2',
            'user[email]' => 'baduser@test.com',
        ]);
        $client->submit($form);
        // Vérifie la présence du message d'erreur
        $this->assertSelectorTextContains('.form-error-message', 'Les deux mots de passe doivent correspondre.');
    }
    // Vérifie que la connexion échoue avec des identifiants invalides
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
        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
        }
        // Vérifie que le message d'erreur d'authentification est affiché
        $this->assertStringContainsString('Invalid credentials', $client->getResponse()->getContent());
    }

}