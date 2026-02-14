<?php

namespace App\Controller\Client;

use App\Entity\User;
use App\Services\CartService;
use App\Services\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart')]
#[IsGranted('ROLE_CLIENT')]
class CartController extends AbstractController
{
    #[Route('', name: 'cart_index')]
    public function index(CartService $cartService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $cart = $cartService->getOrCreateCart($user);

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/add/{id}', name: 'cart_add', methods: ['POST'])]
    public function add(int $id, Request $request, CartService $cartService, ProductService $productService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $product = $productService->getById($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        $quantity = $request->request->getInt('quantity', 1);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addItem($cart, $product, $quantity);

        $this->addFlash('success', 'Produit ajouté au panier !');
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/update/{id}', name: 'cart_update', methods: ['POST'])]
    public function update(int $id, Request $request, CartService $cartService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $cart = $cartService->getOrCreateCart($user);
        $quantity = $request->request->getInt('quantity', 1);

        $cartService->updateQuantity($cart, $id, $quantity);

        $this->addFlash('success', 'Panier mis à jour.');
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/remove/{id}', name: 'cart_remove', methods: ['POST'])]
    public function remove(int $id, CartService $cartService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $cart = $cartService->getOrCreateCart($user);

        $cartService->removeItem($cart, $id);

        $this->addFlash('success', 'Produit retiré du panier.');
        return $this->redirectToRoute('cart_index');
    }
}
