<?php
namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// Test fonctionnel du UserRepository avec Doctrine
class UserRepositoryTest extends WebTestCase
{
    // Vérifie que findOneByEmail retourne bien l'utilisateur attendu
    public function testFindOneByEmailReturnsUser()
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Supprime l'utilisateur test existant si présent
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'finduser@test.com']);
        if ($existingUser) {
            $entityManager->remove($existingUser);
            $entityManager->flush();
        }

        // Crée un utilisateur de test
        $user = new User();
        $user->setEmail('finduser@test.com');
        $user->setUsername('finduser');
        $user->setPassword($passwordHasher->hashPassword($user, 'findpass'));
        $user->setRoles(['ROLE_USER']);
        $entityManager->persist($user);
        $entityManager->flush();

        // Teste la méthode personnalisée
        $repo = $entityManager->getRepository(User::class);
        $found = $repo->findOneByEmail('finduser@test.com');
        $this->assertNotNull($found);
        $this->assertEquals('finduser@test.com', $found->getEmail());
    }

    // Vérifie que findOneByEmail retourne null si l'utilisateur n'existe pas
    public function testFindOneByEmailReturnsNull()
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $repo = $entityManager->getRepository(User::class);
        $found = $repo->findOneByEmail('notfound@test.com');
        $this->assertNull($found);
    }
}