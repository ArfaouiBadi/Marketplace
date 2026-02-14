<?php

namespace App\Controller;

use App\Entity\Supplier;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\SupplierRegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register/client', name: 'app_register_client')]
    public function registerClient(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = new User();
            $user->setFirstName($form->get('firstName')->getData());
            $user->setLastName($form->get('lastName')->getData());
            $user->setEmail($form->get('email')->getData());
            $user->setRoles(['ROLE_CLIENT']);
            $user->setPassword(
                $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Votre compte client a été créé avec succès ! Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/client.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/register/supplier', name: 'app_register_supplier')]
    public function registerSupplier(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $userForm = $this->createForm(RegistrationFormType::class);
        $supplierForm = $this->createForm(SupplierRegistrationFormType::class);

        $userForm->handleRequest($request);
        $supplierForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid() && $supplierForm->isSubmitted() && $supplierForm->isValid()) {
            $user = new User();
            $user->setFirstName($userForm->get('firstName')->getData());
            $user->setLastName($userForm->get('lastName')->getData());
            $user->setEmail($userForm->get('email')->getData());
            $user->setRoles(['ROLE_SUPPLIER']);
            $user->setPassword(
                $passwordHasher->hashPassword($user, $userForm->get('plainPassword')->getData())
            );

            $supplier = new Supplier();
            $supplier->setCompanyName($supplierForm->get('companyName')->getData());
            $supplier->setDescription($supplierForm->get('description')->getData());
            $supplier->setPhone($supplierForm->get('phone')->getData());
            $supplier->setAddress($supplierForm->get('address')->getData());
            $supplier->setUser($user);

            $em->persist($user);
            $em->persist($supplier);
            $em->flush();

            $this->addFlash('success', 'Votre compte fournisseur a été créé ! Un administrateur doit approuver votre compte avant que vos produits soient visibles.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/supplier.html.twig', [
            'userForm' => $userForm,
            'supplierForm' => $supplierForm,
        ]);
    }
}
