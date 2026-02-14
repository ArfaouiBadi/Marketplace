<?php

namespace App\Services;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class OrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private EntityManagerInterface $em,
        private CartService $cartService,
        private EmailService $emailService,
        private LoggerInterface $logger,
    ) {}

    public function createFromCart(User $client, Cart $cart): Order
    {
        $order = new Order();
        $order->setClient($client);
        $order->setReference($this->generateReference());

        foreach ($cart->getItems() as $cartItem) {
            $orderItem = new OrderItem();
            $orderItem->setProduct($cartItem->getProduct());
            $orderItem->setQuantity($cartItem->getQuantity());
            $orderItem->setUnitPrice($cartItem->getProduct()->getPrice());
            $order->addItem($orderItem);

            // Reduce stock
            $product = $cartItem->getProduct();
            $product->setStock($product->getStock() - $cartItem->getQuantity());
        }

        $order->calculateTotal();
        $this->em->persist($order);

        // Clear the cart
        $this->cartService->clear($cart);

        $this->em->flush();

        // Send confirmation email
        $this->logger->info('About to send order confirmation email', [
            'order_ref' => $order->getReference(),
            'client_email' => $client->getEmail(),
        ]);
        
        try {
            $this->emailService->sendOrderConfirmation($order, $client);
            $this->logger->info('Email service call completed');
        } catch (\Exception $e) {
            $this->logger->error('Exception when sending order email: ' . $e->getMessage());
        }

        return $order;
    }

    public function generateReference(): string
    {
        return 'ORD-' . strtoupper(bin2hex(random_bytes(4))) . '-' . date('Ymd');
    }

    /** @return Order[] */
    public function getClientOrders(User $client): array
    {
        return $this->orderRepository->findByClient($client);
    }

    public function getByReference(string $reference): ?Order
    {
        return $this->orderRepository->findOneByReference($reference);
    }

    public function updateStatus(Order $order, string $status): void
    {
        $order->setStatus($status);
        $order->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();
    }
}
