<?php
namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Entity\Task;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TaskControllerTest extends WebTestCase
{
    public function testTaskCreation(): void
    {
        $client = static::createClient();

        // 1. Créer un utilisateur de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        
        $user = new User();
        $user->setEmail('tessszqsst@ddrgsdfgqdgsgfgdd.test');
        $user->setUsername('ddszqsssddrsdgfsqdgggddd');
        $hashedPassword = $passwordHasher->hashPassword($user, 'test');
        $user->setPassword($hashedPassword);
        // $user->setRoles(['ROLE_USER']);
        
        $entityManager->persist($user);
        $entityManager->flush();

        // 2. Connecter l'utilisateur
        $client->loginUser($user);

        // 3. Aller sur la page de création de tâche
        $crawler = $client->request('GET', '/tasks/create');
        $this->assertResponseIsSuccessful();

        // 4. Vérifier que le formulaire existe
        $this->assertSelectorExists('form');

        // 5. Remplir et soumettre le formulaire
        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => 'Ma tâche de test 3',
            'task[content]' => 'Description de ma tâche de test',
        ]);

        $client->submit($form);

        // 6. Vérifier la redirection
        $this->assertResponseRedirects();

        // 7. Vérifier que la tâche a été créée en base
        $task = $entityManager->getRepository(Task::class)->findOneBy([
            'title' => 'Ma tâche de test 3'
        ]);
        
        $this->assertNotNull($task);
        $this->assertEquals('Ma tâche de test 3', $task->getTitle());
        $this->assertEquals('Description de ma tâche de test', $task->getContent());
        $this->assertEquals($user->getId(), $task->getAuthor()->getId());
    }

    public function testTaskDeletionByOwner(): void
    {
       $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Créer un utilisateur propriétaire de la tâche
        $owner = new User();
        $owner->setEmail('owner2a@test.com');
        $owner->setUsername('owneaaruser2');
        $owner->setPassword($passwordHasher->hashPassword($owner, 'test'));
        $entityManager->persist($owner);

        // Créer un autre utilisateur
        $otherUser = new User();
        $otherUser->setEmail('other@ztest.com');
        $otherUser->setUsername('othezruser');
        $otherUser->setPassword($passwordHasher->hashPassword($otherUser, 'test'));
        $entityManager->persist($otherUser);

        // Créer une tâche associée au propriétaire
        $task = new Task();
        $task->setTitle('Tâche non supprimable');
        $task->setContent('Contenu');
        $task->setAuthor($owner); 
        $entityManager->persist($task);

        $entityManager->flush();

        // Connecter l'autre utilisateur
        $client->loginUser($otherUser);

        // Tenter de supprimer la tâche
        $client->request('GET', '/tasks/' . $task->getId() . '/delete');

        // Vérifier que la tâche existe toujours
        $existingTask = $entityManager->getRepository(Task::class)->find($task->getId());
        $this->assertNotNull($existingTask);
        // ? Faire la vérification que la tâche n'a pas été supprimée
        // // Vérifier qu'un accès interdit ou une redirection a eu lieu
        // $this->assertTrue(
        //     $client->getResponse()->getStatusCode() === 403 ||
        //     $client->getResponse()->isRedirect()
        // );
    }

    // public function testTaskDeletionByNonOwnerIsForbidden(): void
    // {
    //     $client = static::createClient();
    //     $entityManager = static::getContainer()->get('doctrine')->getManager();
    //     $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

    //     // Créer un utilisateur propriétaire de la tâche
    //     $owner = new User();
    //     $owner->setEmail('testddttt@test.com');
    //     $owner->setUsername('ownqsdettttrzduser2');
    //     $owner->setPassword($passwordHasher->hashPassword($owner, 'test'));
    //     $entityManager->persist($owner);

    //     // Créer un autre utilisateur
    //     $otherUser = new User();
    //     $otherUser->setEmail('othe1ddztttdr@test.com');
    //     $otherUser->setUsername('otdhztttdaeruser');
    //     $otherUser->setPassword($passwordHasher->hashPassword($otherUser, 'test'));
    //     $entityManager->persist($otherUser);

    //     // Créer une tâche associée au propriétaire
    //     $task = new Task();
    //     $task->setTitle('Tâche non supprimable');
    //     $task->setContent('Contenu');
    //     $task->setAuthor($owner);
    //     $entityManager->persist($task);

    //     $entityManager->flush();

    //     // Connecter l'autre utilisateur
    //     $client->loginUser($otherUser);

    //     // Tenter de supprimer la tâche
    //     $client->request('GET', '/tasks/' . $task->getId() . '/delete');

    //     // Vérifier que la tâche existe toujours
    //     $existingTask = $entityManager->getRepository(Task::class)->find($task->getId());
    //     $this->assertNotNull($existingTask);
    //     $this->assertSelectorTextContains('body', 'Vous ne pouvez pas supprimer cette tâche.');
    //     //? Vérifier qu'un accès interdit ou une redirection a eu lieu
    //     $this->assertTrue(
    //         $client->getResponse()->getStatusCode() === 403 ||
    //         $client->getResponse()->isRedirect()
    //     );
    // }
}