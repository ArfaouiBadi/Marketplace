<?php

namespace App\Services;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;

class CartService
{
    public function __construct(
        private CartRepository $cartRepository,
        private EntityManagerInterface $em,
    ) {}

    public function getOrCreateCart(User $user): Cart
    {
        $cart = $this->cartRepository->findByUser($user);

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $this->em->persist($cart);
            $this->em->flush();
        }

        return $cart;
    }

    public function addItem(Cart $cart, Product $product, int $quantity = 1): void
    {
        // Check if the product already exists in the cart
        foreach ($cart->getItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                $item->setQuantity($item->getQuantity() + $quantity);
                $this->em->flush();
                return;
            }
        }

        $cartItem = new CartItem();
        $cartItem->setProduct($product);
        $cartItem->setQuantity($quantity);
        $cart->addItem($cartItem);
        $this->em->flush();
    }

    public function removeItem(Cart $cart, int $cartItemId): void
    {
        foreach ($cart->getItems() as $item) {
            if ($item->getId() === $cartItemId) {
                $cart->removeItem($item);
                $this->em->remove($item);
                $this->em->flush();
                return;
            }
        }
    }

    public function updateQuantity(Cart $cart, int $cartItemId, int $quantity): void
    {
        foreach ($cart->getItems() as $item) {
            if ($item->getId() === $cartItemId) {
                if ($quantity <= 0) {
                    $this->removeItem($cart, $cartItemId);
                    return;
                }
                $item->setQuantity($quantity);
                $this->em->flush();
                return;
            }
        }
    }

    public function clear(Cart $cart): void
    {
        $cart->clear();
        $this->em->flush();
    }

    public function getTotal(Cart $cart): string
    {
        return $cart->getTotal();
    }

    public function getItemCount(Cart $cart): int
    {
        return $cart->getItemCount();
    }
}
