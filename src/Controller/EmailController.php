<?php

/*
 * This file is part of rimi-itk/mailhogger.
 *
 * (c) 2018–2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/email", name="email_")
 */
class EmailController extends AbstractController
{
    /** @var MailerInterface */
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @Route("/send", name="send")
     */
    public function send(Request $request)
    {
        $data = [
            'subject' => sprintf('Test email %s', (new \DateTime())->format(\DateTime::ATOM)),
            'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
            'to' => 'recipient@example.com',
        ];

        $form = $this->createFormBuilder($data)
            ->add('from', TextType::class)
            ->add('subject', TextType::class)
            ->add('text', TextareaType::class)
            ->add('to', TextType::class)
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $email = (new Email())
                ->from($data['from'])
                ->to($data['to'])
                ->subject($data['subject'])
                ->text($data['text']);

            $this->mailer->send($email);
            $this->addFlash('success', 'Email sent');

            return $this->redirectToRoute('email_send');
        }

        return $this->render('email/send.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
