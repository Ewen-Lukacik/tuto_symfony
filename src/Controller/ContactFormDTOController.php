<?php

namespace App\Controller;

use App\DTO\ContactDTO;
use App\Form\ContactFormType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface;


class ContactFormDTOController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(Request $request, MailerInterface $mailer, LoggerInterface $logger): Response
    {
        $data = new ContactDTO();

        //TODO a supprimer plus tard
        $data->username = "john doe";
        $data->email = "john@doe.fr";
        $data->message = "Test du mailer";


        $form = $this->createForm(ContactFormType::class, $data);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){


        
            $email = (new TemplatedEmail())
                ->to('contact@demo.fr')
                ->from($data->email)
                ->subject('Email sending test from')
                ->htmlTemplate('emails/contact.html.twig')
                ->context(['data' => $data]);

           
            try {
                $mailer->send($email);
                $logger->info('Email envoyé avec succès');
                $this->addFlash('success', 'Email sent');
            } catch (\Exception $e) {
                $logger->error('Erreur d\'envoi d\'email : ' . $e->getMessage());
            }


           
            return $this->redirectToRoute('contact');
        }
        return $this->render('contact_form_dto/contact.html.twig', [
            'form' => $form,
        ]);
    }

}
