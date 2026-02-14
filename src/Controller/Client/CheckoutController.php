<?php

namespace App\Controller\Client;

use App\Entity\User;
use App\Services\CartService;
use App\Services\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/checkout')]
#[IsGranted('ROLE_CLIENT')]
class CheckoutController extends AbstractController
{
    #[Route('', name: 'checkout_index')]
    public function index(CartService $cartService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $cart = $cartService->getOrCreateCart($user);

        if ($cart->getItems()->isEmpty()) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('cart_index');
        }

        return $this->render('checkout/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/confirm', name: 'checkout_confirm', methods: ['POST'])]
    public function confirm(Request $request, CartService $cartService, OrderService $orderService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $cart = $cartService->getOrCreateCart($user);

        if ($cart->getItems()->isEmpty()) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('cart_index');
        }

        // Validate CSRF token
        if (!$this->isCsrfTokenValid('checkout', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('checkout_index');
        }

        try {
            $order = $orderService->createFromCart($user, $cart);
            $this->addFlash('success', 'Commande passée avec succès ! Référence : ' . $order->getReference() . '. Un email de confirmation vous a été envoyé.');
            return $this->redirectToRoute('client_order_show', ['reference' => $order->getReference()]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création de la commande : ' . $e->getMessage());
            return $this->redirectToRoute('checkout_index');
        }
    }
}
