<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Form\ArticleFormType;
use App\Repository\ArticlesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine, private readonly ArticlesRepository $articlesRepository)
    {}

    #[Route('/article', name: 'list_article')]
    public function listArticle()
    {
        $articles = $this->doctrine->getRepository(Articles::class)->findAll();
        return $this->render('article/list.html.twig', [
            'controller_name' => 'ArticleController',
            'articles' => $articles
        ]);
    }

    #[Route('/article/{slug}', name: 'single_article')]
    public function singleArticle(Articles $article, Request $request)
    {
        $articleSlug = $request->attributes->get('slug');
        $article = $this->doctrine->getRepository(Articles::class)->findOneBy(['slug' => $articleSlug]);

        return $this->render('article/single.html.twig', [
            'controller_name' => 'ArticleController',
            'article' => $article
        ]);
    }


    #[Route('backoffice/article', name: 'app_article')]
    public function index(): Response
    {
        $articles = $this->doctrine->getRepository(Articles::class)->findAll();
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
            'articles' => $articles
        ]);
    }

    //create/Edit article
    #[Route('backoffice/article/create/', name: 'app_article_create')]
    #[Route('backoffice/article/update/{id}', name: 'app_article_update')]
    public function create(\Symfony\Component\HttpFoundation\Request $request): Response
    {
        $article = !(int)$request->attributes->get('id') ? new Articles() : $this->articlesRepository->find($request->attributes->get('id'));
        $articleForm = $this->createForm(ArticleFormType::class, $article);
        $articleForm->handleRequest($request);


        if($articleForm->isSubmitted()){
            if($article->getSlug() === null){
                //replace spaces with -
                $article->setSlug(str_replace(' ', '-', $article->getTitre()));
            }
            // if $article->getResume is null get the first 100 characters of $article->getContenu
            if($article->getResume() === null){
                $article->setResume(substr($article->getContenu(), 0, 100));
            }
            if($article->getDateCreation() === null){
                $article->setDateCreation(new \DateTime());
            }
            if($article->isIsPublished()){
                $article->setDatePublication(new \DateTime());
            }
            $article->setDateModification(new \DateTime());

            $this->articlesRepository->save($article, true);

            $this->addFlash('success', 'Article créé avec succès');
            return $this->redirectToRoute('app_article',[], Response::HTTP_MOVED_PERMANENTLY);
        }

        return $this->render('article/create.html.twig', [
            'controller_name' => 'ArticleController',
            'form' => $articleForm->createView()
        ]);
    }

    //remove article
    #[Route('backoffice/article/remove/{id}', name: 'app_article_remove')]
    public function remove(\Symfony\Component\HttpFoundation\Request $request): Response
    {
        $article = $this->articlesRepository->find($request->attributes->get('id'));
        $this->articlesRepository->remove($article, true);

        $this->addFlash('success', 'Article supprimé avec succès');
        return $this->redirectToRoute('app_article',[], Response::HTTP_MOVED_PERMANENTLY);
    }
}
