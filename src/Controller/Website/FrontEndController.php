<?php

namespace App\Controller\Website;

use App\Entity\Article;
use App\Data\SearchData;
use App\Form\SearchForm;
use Symfony\Component\Mime\Email;
use App\Repository\TeamRepository;
use App\Repository\ArticleRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FrontEndController extends AbstractController
{

    /**
     * @var ArticleRepository
     */
    private $articleRepository;

    /**
     * @var TeamRepository
     */
    private $teamRepository;
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(
        ArticleRepository $articleRepository,
        TeamRepository $teamRepository,
        PaginatorInterface $paginator
        )
    {
        $this->articleRepository = $articleRepository;
        $this->teamRepository = $teamRepository;
        $this->paginator = $paginator;
    }

    /**
     * @Route("/presentation", name="web_aboutus")
     */
    public function aboutus(Request $request): Response
    {
        
        $aboutus = $this->articleRepository->findByTag('aboutus');
        $teams=$this->teamRepository->findAll();

        return $this->render('website/aboutus.html.twig', [
            'aboutus'=>(count($aboutus)> 0)?$aboutus[0]:null,
            'teams'=>$teams
        ]);
    }

    /**
     * @Route("/bon_a_savoir", name="web_bon_a_savoir")
     */
    public function bonAsavoir(Request $request): Response
    {
        
        $aboutus = $this->articleRepository->findByTag('aboutus');
        $teams=$this->teamRepository->findAll();

        return $this->render('website/bon_a_savoir.html.twig', [
            'aboutus'=>(count($aboutus)> 0)?$aboutus[0]:null,
            'teams'=>$teams
        ]);
    }

    /**
     * @Route("/spécialités", name="web_specialites")
     */
    public function specialiteIndex(Request $request): Response
    {
        
        // $aboutus = $this->articleRepository->findByTag('aboutus');
        // $teams=$this->teamRepository->findAll();

        return $this->render('website/specialite/index.html.twig', [
            // 'aboutus'=>(count($aboutus)> 0)?$aboutus[0]:null,
            // 'teams'=>$teams
        ]);
    }

    /**
     * @Route("/services_rendus", name="web_services_rendus")
     */
    public function servicesRenduIndus(Request $request): Response
    {
        
        // $aboutus = $this->articleRepository->findByTag('aboutus');
        // $teams=$this->teamRepository->findAll();

        return $this->render('website/service/index.html.twig', [
            // 'aboutus'=>(count($aboutus)> 0)?$aboutus[0]:null,
            // 'teams'=>$teams
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


    /**
     * @Route("/contact/message", name="web_contact")
     */
    public function sendMessageAction(Request $request,MailerInterface $mailer )
    {
        $referer=$request->headers->get('referer');
        if($request->getMethod()=="POST") {
            $data=$request->request->all();
            $message_body="Message de {$data['email'] } ({$data['name'] }) :\n\n";
            $message_body.=$data['message'];
            // dump($message_body); die();



            $email = (new Email())
            ->subject($data['subject'])
            ->from('no-reply@universaquatic.com')
            ->to('contact@universaquatic.com')
            ->text($message_body);
            
            if($mailer->send($email)){
                // dump($message);die();
                $this->addFlash('success', 'Votre message a été envoyé avec succès. Nous allons vous répondre dans un instant.');
            }else{
                $this->addFlash('danger', 'Désolé ! Votre message n\'a pas pu être envoyé. Veuillez réessayer ou nous contacter à nos addresses ! ');
            }
            return $this->redirect($referer);
        }

    
    }    

}
