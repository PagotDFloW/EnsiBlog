<?php

namespace App\Controller;

use App\Repository\ArticlesRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(private readonly ArticlesRepository $articlesRepository)
    {}

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $articles = $this->articlesRepository->findfirstfive();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'articles' => $articles
        ]);
    }

    //contact form
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $content = $request->get('content');
        $email = $request->get('email');
        $adminEmail = $this->getParameter('app.admin_email');

        if($content !== null && $email !== null){
            //send symfony mailer
            $message = (new Email)
                ->from(new Address($email))
                ->to($adminEmail)
                ->subject('Nouveau message')
                ->text($content);

            $mailer->send($message);

            $this->addFlash('success', 'Votre message a bien été envoyé');
            return $this->redirectToRoute('app_home');
        }
        return $this->render('home/contact.html.twig', [
            'controller_name' => 'HomeController',
        ]);



    }
}
