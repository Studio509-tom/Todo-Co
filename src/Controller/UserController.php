<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\TaskRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;
class UserController extends AbstractController
    
{
    private $passwordHasher;
    private $managerRegistry;

    public function __construct(UserPasswordHasherInterface $passwordHasher, ManagerRegistry $managerRegistry)
    {
        $this->passwordHasher = $passwordHasher;
        $this->managerRegistry = $managerRegistry;
    }
    
    #[Route('/users', name: 'user_list')]
    public function listAction(ManagerRegistry $registry): Response
    {
        // Restriction d'accès : seuls les admins peuvent accéder à la liste des utilisateurs
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Accès interdit : seuls les administrateurs peuvent accéder à cette page.");
        }

        $users = $registry->getRepository(User::class)->findAll();
        return $this->render('user/list.html.twig', [
            'users' => $users
        ]);
    }

 
    #[Route('/users/create', name: 'user_create')]
    public function createAction(Request $request , ManagerRegistry $registry, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
          $em = $registry->getManager();

            // Hash du mot de passe
            $password = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($password);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }

    
    #[Route('/users/{id}/edit', name: 'user_edit')]

    public function editAction(User $user, Request $request): Response
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Utilisation du service injecté pour hasher le mot de passe
            $password = $this->passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($password);

            $this->managerRegistry->getManager()->flush();

            $this->addFlash('success', "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }

    #[Route('/users/{id}/delete', name: 'user_delete')]
    public function deleteAction(User $user, ManagerRegistry $registry , UserRepository $userRepository , TaskRepository $taskRepository): Response
    // Suppression des tâches orphelines (sans auteur)
    {
        $em = $registry->getManager();

        // Recherche de l'utilisateur anonyme, création si absent
        $anonymous = $userRepository->findByUsername('anonyme');
        if (!$anonymous) {
            $anonymous = new User();
            $anonymous->setUsername('anonyme');
            $anonymous->setEmail('anonyme@todo-co.local');
            $anonymous->setRoles(['ROLE_USER']);
            $anonymous->setPassword(''); // Mot de passe vide, non connectable
            $em->persist($anonymous);
            $em->flush();
        }

        // 2. Rattachement de toutes les tâches à l'utilisateur anonyme via le repository
        $tasks = $taskRepository->findByAuthor($user);
        
        foreach ($tasks as $task) {
            $task->setAuthor($anonymous);
            $em->persist($task);
        }
        $em->flush();

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', "L'utilisateur a été supprimé et ses tâches ont été rattachées à l'utilisateur anonyme.");
        return $this->redirectToRoute('user_list');
    }

    
}
