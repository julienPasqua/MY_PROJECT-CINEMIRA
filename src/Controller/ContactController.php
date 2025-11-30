<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact_page')]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class, null, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $email = (new Email())
                ->from('julienpasqua2a@gmail.com')
                ->to('julienpasqua2a@gmail.com')
                ->replyTo($data['email'])
                ->subject("📩 Nouveau message CineMira")
                ->html("
                    <h2>📬 Nouveau message du formulaire</h2>
                    <p><strong>Nom :</strong> {$data['nom']}</p>
                    <p><strong>Email :</strong> {$data['email']}</p>
                    <p><strong>Message :</strong><br>{$data['message']}</p>
                ");

            $mailer->send($email);

            $this->addFlash('success', 'Votre message a été envoyé avec succès ✔️');

            return $this->redirectToRoute('contact_page');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
