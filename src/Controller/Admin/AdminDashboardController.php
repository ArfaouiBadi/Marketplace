<?php

namespace App\Controller\Admin;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\SupplierRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function index(
        UserRepository $userRepository,
        SupplierRepository $supplierRepository,
        ProductRepository $productRepository,
        OrderRepository $orderRepository,
    ): Response {
        return $this->render('admin/dashboard.html.twig', [
            'totalUsers' => count($userRepository->findAll()),
            'totalSuppliers' => count($supplierRepository->findAll()),
            'pendingSuppliers' => count($supplierRepository->findPendingApproval()),
            'totalProducts' => count($productRepository->findAll()),
            'totalOrders' => $orderRepository->countAll(),
            'totalRevenue' => $orderRepository->getTotalRevenue(),
        ]);
    }
}
