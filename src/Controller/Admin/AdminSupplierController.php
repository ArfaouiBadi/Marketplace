<?php

namespace App\Controller\Admin;

use App\Repository\SupplierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/suppliers')]
#[IsGranted('ROLE_ADMIN')]
class AdminSupplierController extends AbstractController
{
    #[Route('', name: 'admin_suppliers')]
    public function list(SupplierRepository $supplierRepository): Response
    {
        return $this->render('admin/supplier/list.html.twig', [
            'suppliers' => $supplierRepository->findAll(),
        ]);
    }

    #[Route('/{id}/approve', name: 'admin_supplier_approve', methods: ['POST'])]
    public function approve(int $id, Request $request, SupplierRepository $supplierRepository, EntityManagerInterface $em): Response
    {
        $supplier = $supplierRepository->find($id);

        if (!$supplier) {
            throw $this->createNotFoundException('Fournisseur non trouvé');
        }

        if ($this->isCsrfTokenValid('approve' . $supplier->getId(), $request->request->get('_token'))) {
            $supplier->setIsApproved(true);
            $em->flush();
            $this->addFlash('success', "Fournisseur \"{$supplier->getCompanyName()}\" approuvé avec succès.");
        }

        return $this->redirectToRoute('admin_suppliers');
    }

    #[Route('/{id}/reject', name: 'admin_supplier_reject', methods: ['POST'])]
    public function reject(int $id, Request $request, SupplierRepository $supplierRepository, EntityManagerInterface $em): Response
    {
        $supplier = $supplierRepository->find($id);

        if (!$supplier) {
            throw $this->createNotFoundException('Fournisseur non trouvé');
        }

        if ($this->isCsrfTokenValid('reject' . $supplier->getId(), $request->request->get('_token'))) {
            $supplier->setIsApproved(false);
            $em->flush();
            $this->addFlash('success', "Fournisseur \"{$supplier->getCompanyName()}\" rejeté.");
        }

        return $this->redirectToRoute('admin_suppliers');
    }
}
