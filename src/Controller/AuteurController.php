<?php

namespace App\Controller;

use App\Entity\Auteurs;
use App\Form\AuteurFormType;
use App\Repository\AuteursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuteurController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine, private readonly AuteursRepository $auteursRepository)
    {}

    #[Route('/auteur', name: 'single_auteur')]
    public function indexArticleByAuteur()
    {
        $auteurs = $this->auteursRepository->findAll();

        return $this->render('auteur/list.html.twig', [
            'controller_name' => 'AuteurController',
            'auteurs' => $auteurs
        ]);

    }

    #[Route('backoffice/auteur', name: 'app_auteur')]
    public function index(): Response
    {
        $auteurs = $this->doctrine->getRepository(Auteurs::class)->findAll();
        return $this->render('auteur/index.html.twig', [
            'controller_name' => 'AuteurController',
            'auteurs' => $auteurs
        ]);
    }

    //create auteur
    #[Route('backoffice/auteur/create', name: 'app_auteur_create')]
    #[Route('backoffice/auteur/update/{id}', name: 'app_auteur_update')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $auteur = !(int)$request->attributes->get('id') ? new Auteurs() : $this->auteursRepository->find($request->attributes->get('id'));
        $auteurForm = $this->createForm(AuteurFormType::class, $auteur);
        $auteurForm->handleRequest($request);
        if($auteurForm->isSubmitted()){
            $this->auteursRepository->save($auteur, true);

            $this->addFlash('success', 'Auteur créé avec succès');
            return $this->redirectToRoute('app_auteur');
        }

        return $this->render('auteur/create.html.twig', [
            'controller_name' => 'AuteurController',
            'form' => $auteurForm->createView()
        ]);
    }
}
