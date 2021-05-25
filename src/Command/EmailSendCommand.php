<?php

/*
 * This file is part of rimi-itk/mailhogger.
 *
 * (c) 2018–2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailSendCommand extends Command
{
    protected static $defaultName = 'app:email:send';

    /** @var MailerInterface */
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        parent::__construct();
        $this->mailer = $mailer;
    }

    protected function configure()
    {
        $this->addArgument('from-email', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $from = $input->getArgument('from-email');
        $to = 'recipient@example.com';

        $email = (new Email())
            ->from($from)
            ->to($to)
            ->subject(sprintf('Test email %s', (new \DateTime())->format(\DateTime::ATOM)))
            ->text('Test email content');

        $this->mailer->send($email);
    }
}
