<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // --- Création de l’utilisateur anonyme ---
        $anonymous = new User();
        $anonymous->setUsername('anonyme');
        $anonymous->setEmail('anonyme@todoandco.com');
        $anonymous->setRoles(['ROLE_USER']);
        $anonymous->setPassword(
            $this->passwordHasher->hashPassword($anonymous, 'password')
        );
        $manager->persist($anonymous);

        $userRepository = $manager->getRepository(User::class);
        // --- Création d’un admin ---
        if (!$userRepository->findOneBy(['email' => 'admin@todoandco.com'])) {
            $admin = new User();
            $admin->setUsername('admin');
            $admin->setEmail('admin@todoandco.com');
            $admin->setPassword(
                $this->passwordHasher->hashPassword($admin, 'password')
            );
            $admin->setRoles(['ROLE_ADMIN']);
            $manager->persist($admin);
        }

        // --- Création de quelques utilisateurs classiques ---
        $users = [];
        for ($i = 1; $i <= 3; $i++) {
            $user = new User();
            $user->setUsername('user'.$i);
            $user->setEmail("user{$i}@todoandco.com");
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'userpass')
            );
            $manager->persist($user);
            $users[] = $user;
        }

        // --- Création de tâches pour l’admin ---
        for ($i = 1; $i <= 3; $i++) {
            $task = new Task();
            $task->setTitle("Tâche admin $i");
            $task->setContent("Ceci est une tâche créée par l’administrateur.");
            $task->setAuthor($admin);
            $task->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($task);
        }

        // --- Création de tâches pour les utilisateurs ---
        foreach ($users as $user) {
            for ($i = 1; $i <= 2; $i++) {
                $task = new Task();
                $task->setTitle("Tâche {$user->getUsername()} $i");
                $task->setContent("Ceci est une tâche pour ".$user->getUsername());
                $task->setAuthor($user);
                $task->setCreatedAt(new \DateTimeImmutable());
                $manager->persist($task);
            }
        }

        // --- Création de tâches pour l’anonyme ---
        for ($i = 1; $i <= 2; $i++) {
            $task = new Task();
            $task->setTitle("Tâche anonyme $i");
            $task->setContent("Ceci est une tâche associée à l’utilisateur anonyme.");
            $task->setAuthor($anonymous);
            $task->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($task);
        }

        // Sauvegarde en base
        $manager->flush();
    }
}
