<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

#[AsCommand(
    name: 'app:test-email',
    description: 'Test email configuration by sending a test email',
)]
class TestEmailCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer,
        private UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', null, 'Email address to send test email to', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        if (!$email) {
            $io->error('Please provide an email address: php bin/console app:test-email your@email.com');
            return Command::FAILURE;
        }

        try {
            $testEmail = new TemplatedEmail();
            $testEmail
                ->from('noreply@marketplace.com')
                ->to($email)
                ->subject('🧪 Email de test - MarketPlace')
                ->htmlTemplate('emails/test_email.html.twig')
                ->context([
                    'recipient_email' => $email,
                ]);

            $this->mailer->send($testEmail);

            $io->success("✅ Test email sent successfully to: $email");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('❌ Failed to send test email: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
