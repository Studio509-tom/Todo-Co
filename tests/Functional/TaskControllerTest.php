<?php
namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Entity\Task;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// Test fonctionnel du contrôleur Task (gestion des droits d'accès)
class TaskControllerTest extends WebTestCase
{
    // Test fonctionnel : création d'une tâche par un utilisateur connecté
    public function testUserCanCreateTask()
    {
        // On crée le client et les services nécessaires
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Nettoyage de l'utilisateur et des tâches de test
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'createtask@test.com']);
        if ($existingUser) {
            $tasks = $entityManager->getRepository(Task::class)->findBy(['author' => $existingUser]);
            foreach ($tasks as $task) {
                $entityManager->remove($task);
            }
            $entityManager->remove($existingUser);
            $entityManager->flush();
        }

        // Création de l'utilisateur de test
        $user = new User();
        $user->setEmail('createtask@test.com');
        $user->setUsername('createtaskuser');
        $user->setPassword($passwordHasher->hashPassword($user, 'testpass'));
        $user->setRoles(['ROLE_USER']);
        $entityManager->persist($user);
        $entityManager->flush();

        // Connexion de l'utilisateur
        $client->loginUser($user);

        // Accès à la page de création de tâche
        $crawler = $client->request('GET', '/tasks/create');
        $this->assertResponseIsSuccessful(); // Vérifie que la page est accessible
        $this->assertSelectorExists('form'); // Vérifie que le formulaire est présent

        // Soumission du formulaire avec des données valides
        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => 'Nouvelle tâche de test',
            'task[content]' => 'Contenu de la tâche créée en test',
        ]);
        $client->submit($form);

        // Vérifie la redirection vers la liste des tâches
        $this->assertResponseRedirects('/tasks');
        $client->followRedirect();

        // Vérifie la présence du message de succès
        $this->assertSelectorTextContains('.alert-success', 'La tâche a été bien été ajoutée.');

        // Vérifie que la tâche a bien été créée en base
        $createdTask = $entityManager->getRepository(Task::class)->findOneBy([
            'title' => 'Nouvelle tâche de test',
            'author' => $user
        ]);
        $this->assertNotNull($createdTask, 'La tâche doit exister en base après création.');
        $this->assertEquals('Contenu de la tâche créée en test', $createdTask->getContent());
        $this->assertEquals($user->getId(), $createdTask->getAuthor()->getId());
       
    }


    public function testTaskDeletionByOwner()
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Supprime l'utilisateur owner existant et ses tâches si présent
        $existingOwner = $entityManager->getRepository(User::class)->findOneBy(['email' => 'owner@test.com']);
        if ($existingOwner) {
            $tasks = $entityManager->getRepository(Task::class)->findBy(['author' => $existingOwner]);
            foreach ($tasks as $task) {
                $entityManager->remove($task);
            }
            $entityManager->remove($existingOwner);
            $entityManager->flush();
        }
        $user = new User();
        $user->setEmail('owner@test.com');
        $user->setUsername('owner');
        $user->setPassword($passwordHasher->hashPassword($user, 'ownerpass'));
        $user->setRoles(['ROLE_USER']);
        $entityManager->persist($user);

        $task = new Task();
        $task->setTitle('Test Task');
        $task->setContent('Contenu de test pour la suppression par owner');
        $task->setAuthor($user);
        $entityManager->persist($task);
        $entityManager->flush();

        $client->loginUser($user);
        $client->request('POST', '/tasks/' . $task->getId() . '/delete');
        $this->assertResponseRedirects('/tasks');
    }
    public function testTaskDeletionByAdminForAnonymousTask()
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Supprime l'utilisateur admin2 existant et ses tâches si présent
        $existingAdmin = $entityManager->getRepository(User::class)->findOneBy(['email' => 'admin2@test.com']);
        if ($existingAdmin) {
            $tasks = $entityManager->getRepository(Task::class)->findBy(['author' => $existingAdmin]);
            foreach ($tasks as $task) {
                $entityManager->remove($task);
            }
            $entityManager->remove($existingAdmin);
            $entityManager->flush();
        }
        $admin = new User();
        $admin->setEmail('admin2@test.com');
        $admin->setUsername('admin2');
        $admin->setPassword($passwordHasher->hashPassword($admin, 'adminpass2'));
        $admin->setRoles(['ROLE_ADMIN']);
        $entityManager->persist($admin);

        // Supprime l'utilisateur anonymous existant et ses tâches si présent
        $existingAnonymous = $entityManager->getRepository(User::class)->findOneBy(['email' => 'anonymous@test.com']);
        if ($existingAnonymous) {
            $tasks = $entityManager->getRepository(Task::class)->findBy(['author' => $existingAnonymous]);
            foreach ($tasks as $task) {
                $entityManager->remove($task);
            }
            $entityManager->remove($existingAnonymous);
            $entityManager->flush();
        }
        $anonymous = new User();
        $anonymous->setEmail('anonymous@test.com');
        $anonymous->setUsername('anonymous');
        $anonymous->setPassword($passwordHasher->hashPassword($anonymous, 'anonpass'));
        $anonymous->setRoles(['ROLE_USER']);
        $entityManager->persist($anonymous);

        $task = new Task();
        $task->setTitle('Anonymous Task');
        $task->setContent('Contenu de test pour la suppression par admin');
        $task->setAuthor($anonymous);
        $entityManager->persist($task);
        $entityManager->flush();

        $client->loginUser($admin);
        $client->request('POST', '/tasks/' . $task->getId() . '/delete');
        $this->assertResponseRedirects('/tasks');
    }

    public function testTaskDeletionForbiddenForNonOwnerUser()
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        
        // Nettoyage de l'auteur existant
        $existingAuthor = $entityManager->getRepository(User::class)->findOneBy(['email' => 'author@test.com']);
        if ($existingAuthor) {
            $tasks = $entityManager->getRepository(Task::class)->findBy(['author' => $existingAuthor]);
            foreach ($tasks as $task) {
                $entityManager->remove($task);
            }
            $entityManager->remove($existingAuthor);
            $entityManager->flush();
        }
        // Nettoyage de l'autre utilisateur existant
        $existingOtherUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'otheruser@test.com']);
        if ($existingOtherUser) {
            $tasks = $entityManager->getRepository(Task::class)->findBy(['author' => $existingOtherUser]);
            foreach ($tasks as $task) {
                $entityManager->remove($task);
            }
            $entityManager->remove($existingOtherUser);
            $entityManager->flush();
        }
        // Crée l'auteur de la tâche
        $author = new User();
        $author->setEmail('author@test.com');
        $author->setUsername('author');
        $author->setPassword($passwordHasher->hashPassword($author, 'authorpass'));
        $author->setRoles(['ROLE_USER']);
        $entityManager->persist($author);

        // Crée un autre utilisateur
        $otherUser = new User();
        $otherUser->setEmail('otheruser@test.com');
        $otherUser->setUsername('otheruser');
        $otherUser->setPassword($passwordHasher->hashPassword($otherUser, 'otherpass'));
        $otherUser->setRoles(['ROLE_USER']);
        $entityManager->persist($otherUser);

        // Crée la tâche associée à l'auteur
        $task = new Task();
        $task->setTitle('Tâche non supprimable');
        $task->setContent('Contenu');
        $task->setAuthor($author);
        $entityManager->persist($task);

        $entityManager->flush();

        // Connecte l'autre utilisateur
        $client->loginUser($otherUser);

        // Tente de supprimer la tâche
        $client->request('POST', '/tasks/' . $task->getId() . '/delete');

        // Vérifie que la tâche existe toujours
        $existingTask = $entityManager->getRepository(Task::class)->find($task->getId());
        $this->assertNotNull($existingTask);

        // Vérifie que l'accès est interdit (403)
        $this->assertResponseStatusCodeSame(403);

        // Vérifie le message d'erreur affiché
        // $this->assertSelectorTextContains('body', 'Vous ne pouvez pas supprimer cette tâche.');
    }
}
   
