<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact_page')]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $email = (new Email())
                ->from('julienpasqua2a@gmail.com')
                ->replyTo($data['email'])
                ->to('julienpasqua2a@gmail.com')                                                     
                ->subject('📩 Nouveau message du formulaire de contact')
                ->text($data['message'])
                ->html("
                    <h2>Nouveau message de contact</h2>
                    <p><strong>Nom :</strong> {$data['nom']}</p>
                    <p><strong>Email :</strong> {$data['email']}</p>
                    <p><strong>Message :</strong><br>{$data['message']}</p>
                ");

            $mailer->send($email);

            $this->addFlash('success', 'Votre message a bien été envoyé ✔️');

            return $this->redirectToRoute('contact_page');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
