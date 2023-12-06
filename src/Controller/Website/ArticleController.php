<?php

namespace App\Controller\Website;

use App\Data\SearchData;
use App\Entity\Article;
use App\Form\SearchForm;
use App\Repository\ArticleRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArticleController extends AbstractController
{

    /**
     * @var ArticleRepository
     */
    private $articleRepository;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(
        ArticleRepository $articleRepository,
        PaginatorInterface $paginator
        )
    {
        $this->articleRepository = $articleRepository;
        $this->paginator = $paginator;
    }

    /**
     * @Route("/bog/articles", name="web_articles")
     */
    public function index(Request $request): Response
    {
        $data = new SearchData();
        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchForm::class, $data);
        $form->handleRequest($request);

        $articles = $this->articleRepository->findSearch($data);

        return $this->render('website/article/index.html.twig', [
            'articles' => $articles,
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/blog/article/{slug}", name="web_article")
     */
    public function detail(Request $request, Article $article,$slug): Response
    {
        $data = new SearchData();
        $data->page = $request->get('page', 1);
        $data->limit=5;
        $form = $this->createForm(SearchForm::class, $data);
        $form->handleRequest($request);

        $articles = $this->articleRepository->findSearch($data);
        
        $article=$this->articleRepository->findOneBy(['slug'=>$slug]);
        return $this->render('website/article/detail.html.twig', [
            'article' => $article,
            'articles' => $articles,
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/bog/articles/recherche", name="web_articles_recherche")
     */
    public function search(Request $request): Response
    {
        $data = new SearchData();
        $data->page = $request->get('page', 1);
        $data->limit=12;
        $form = $this->createForm(SearchForm::class, $data);
        $form->handleRequest($request);

        $articles = $this->articleRepository->findSearch($data);

        return $this->render('website/article/search.html.twig', [
            'articles' => $articles,
            'form'=>$form->createView()
        ]);
    }

}
