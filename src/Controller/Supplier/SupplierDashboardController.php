<?php

namespace App\Controller\Supplier;

use App\Entity\User;
use App\Services\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/supplier')]
#[IsGranted('ROLE_SUPPLIER')]
class SupplierDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'supplier_dashboard')]
    public function index(ProductService $productService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $supplier = $user->getSupplier();

        if (!$supplier) {
            throw $this->createNotFoundException('Profil fournisseur introuvable.');
        }

        $products = $productService->getBySupplier($supplier);

        return $this->render('supplier/dashboard.html.twig', [
            'supplier' => $supplier,
            'products' => $products,
            'productCount' => count($products),
        ]);
    }
}
