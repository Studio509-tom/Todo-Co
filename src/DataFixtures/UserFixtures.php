<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const REF_ADMIN = 'user_admin';
    public const REF_USER  = 'user_standard';
    public const REF_ANON  = 'user_anonymous';

    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setUsername('admin');
        $admin->setPassword($this->hasher->hashPassword($admin, 'adminpass'));
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);
        $this->addReference(self::REF_ADMIN, $admin);

        // User standard
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setUsername('user');
        $user->setPassword($this->hasher->hashPassword($user, 'userpass'));
        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);
        $this->addReference(self::REF_USER, $user);

        // Utilisateur “anonymous” (si logique projet)
        $anon = new User();
        $anon->setEmail('anonymous@example.com');
        $anon->setUsername('anonymous');
        $anon->setPassword($this->hasher->hashPassword($anon, 'anonpass'));
        $anon->setRoles(['ROLE_USER']);
        $manager->persist($anon);
        $this->addReference(self::REF_ANON, $anon);

        $manager->flush();
    }
}