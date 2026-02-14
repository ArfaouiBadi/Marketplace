<?php

namespace App\Services;

use App\Entity\Order;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Psr\Log\LoggerInterface;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger
    )
    {
    }

    public function sendOrderConfirmation(Order $order, User $client): void
    {
        try {
            $email = new TemplatedEmail();
            $email
                ->from('noreply@marketplace.com')
                ->to($client->getEmail())
                ->subject('Confirmation de votre commande #' . $order->getReference())
                ->htmlTemplate('emails/order_confirmation.html.twig')
                ->context([
                    'order' => $order,
                    'client' => $client,
                ]);

            $this->mailer->send($email);
            $this->logger->info('Order confirmation email sent to ' . $client->getEmail() . ' for order ' . $order->getReference());
        } catch (\Exception $e) {
            // Log error but don't throw - order was already created
            $this->logger->error('Failed to send order confirmation email: ' . $e->getMessage(), [
                'exception' => $e,
                'email' => $client->getEmail(),
                'order_reference' => $order->getReference(),
            ]);
        }
    }
}
