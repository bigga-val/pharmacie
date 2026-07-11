<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UsersController extends AbstractController
{
    #[Route('/list_users', name: 'app_user_index', methods: ['GET', 'POST'])]
    public function list_users(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('users/list_users.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/profile_user/{id}', name: 'app_user_profile', methods: ['GET', 'POST'])]
    public function profile_user($id, Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);
        $currentID = $this->getUser()->getId();

        if (count($request->query->all()) == 1) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
            $role = $request->query->all()['role'];
            $user->setRoles([$role]);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Role modifié avec succès');

            if ($user->getId() == $currentID) {
                return $this->redirectToRoute('app_logout');
            }
        }

        return $this->render('users/profile_user.html.twig', [
            'user' => $user,
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/profile_disconnect/{id}', name: 'app_user_disconnect', methods: ['GET', 'POST'])]
    public function profile_disconnect($id, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $userRepository->find($id);
        if ($user) {
            $user->setSessionId(null);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur déconnecté avec succès.');
        }

        return $this->redirectToRoute('app_user_index');
    }

    #[Route('/new_user', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, 'Afya@' . date('Y'))
            );
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur créé avec succès. Mot de passe par défaut : Afya@' . date('Y'));

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('users/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/unauthorized', name: 'app_user_unauthorized', methods: ['GET'])]
    public function unauthorized(): Response
    {
        return $this->render('users/unauthorized.html.twig');
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}/edit_user', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $isOwner = $this->getUser()->getId() === $user->getId();

        if (!$isAdmin && !$isOwner) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur modifié avec succès');

            return $this->redirectToRoute('app_user_profile', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('users/edit_user.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}/reset_password', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function reset_password(Request $request, $id, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $isOwner = $this->getUser()->getId() === $user->getId();

        if (!$isAdmin && !$isOwner) {
            throw $this->createAccessDeniedException();
        }

        if ($request->getMethod() === 'POST') {
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $request->request->get('nouveau'))
            );
            $entityManager->flush();
            $this->addFlash('success', 'Mot de passe réinitialisé avec succès');

            if ($this->getUser()->getId() === $user->getId()) {
                return $this->redirectToRoute('app_logout');
            }

            return $this->redirectToRoute('app_user_profile', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('users/reset_pwd.html.twig', [
            'user' => $user,
        ]);
    }
}
