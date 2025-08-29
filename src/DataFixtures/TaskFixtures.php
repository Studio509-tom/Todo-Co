<?php

namespace App\DataFixtures;

use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TaskFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Tâches user standard
        for ($i = 1; $i <= 5; $i++) {
            $t = new Task();
            $t->setTitle("Tâche user $i");
            $t->setContent("Contenu user $i");

            // Utilise correctement la référence définie dans UserFixtures
            $user = $this->getReference(UserFixtures::REF_USER);
            $t->setAuthor($user);

            $t->toggle($i % 3 === 0);
            $t->setCreatedAt(new \DateTimeImmutable('-'.($i+1).' days'));
            $manager->persist($t);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}