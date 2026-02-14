<?php

namespace App\Controller\Client;

use App\Entity\User;
use App\Services\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/client')]
#[IsGranted('ROLE_CLIENT')]
class OrderController extends AbstractController
{
    #[Route('/orders', name: 'client_orders')]
    public function list(OrderService $orderService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('client/order/list.html.twig', [
            'orders' => $orderService->getClientOrders($user),
        ]);
    }

    #[Route('/orders/{reference}', name: 'client_order_show')]
    public function show(string $reference, OrderService $orderService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $order = $orderService->getByReference($reference);

        if (!$order || $order->getClient()->getId() !== $user->getId()) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        return $this->render('client/order/show.html.twig', [
            'order' => $order,
        ]);
    }
}
