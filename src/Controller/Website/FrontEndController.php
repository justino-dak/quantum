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
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sulu\Bundle\CategoryBundle\Entity\CategoryRepositoryInterface;
use Sulu\Bundle\CategoryBundle\Entity\CategoryMetaRepositoryInterface;
use Sulu\Component\Content\Repository\Content;

class FrontEndController extends AbstractController
{
    const GR_URL = 'https://www.google.com/recaptcha/api/siteverify';
    const GR_SECRET="6Lcco4QpAAAAADr_j7Y0EsDzbRFsCbTR_OoTlgOp";


    /**
     * @var ArticleRepository
     */
    private $articleRepository;

    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @var CategoryMetaRepositoryInterface
     */
    private $categoryRepository;
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     *  @var HttpClientInterface
     */
    private $client;

    public function __construct(
        ArticleRepository $articleRepository,
        TeamRepository $teamRepository,
        PaginatorInterface $paginator,
        HttpClientInterface $client,
        CategoryRepositoryInterface $categoryRepository
        )
    {
        $this->articleRepository = $articleRepository;
        $this->teamRepository = $teamRepository;
        $this->categoryRepository = $categoryRepository;
        $this->paginator = $paginator;
        $this->client = $client;
    }

    /**
     * @Route("/presentation", name="web_aboutus")
     */
    public function aboutus(Request $request): Response
    {
        
        $aboutus = $this->articleRepository->findByTag('presentation');
        $teams=$this->teamRepository->findAll();

        return $this->render('website/aboutus.html.twig', [
            'article'=>(count($aboutus)> 0)?$aboutus[0]:null,
            'teams'=>$teams
        ]);
    }

    /**
     * @Route("/bon_a_savoir", name="web_bon_a_savoir")
     */
    public function bonAsavoir(Request $request): Response
    {
        
        $savoirs = $this->articleRepository->findByTag('bon_a_savoir');
        $faqs = $this->articleRepository->findByTag('faq');
        $articlesRecentes = $this->articleRepository->findByTag('article',6);



        return $this->render('website/bon_a_savoir.html.twig', [
            'savoirs'=>$savoirs,
            'faqs'=>$faqs,
            'articlesRecentes'=>$articlesRecentes,
        ]);
    }

    /**
     * @Route("/specialites", name="web_specialites")
     */
    public function specialiteIndex(Request $request): Response
    {
        
        $data = new SearchData();
        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchForm::class, $data);
        $form->handleRequest($request);

        $data->tag= "specialite";
        $data->limit = 9;
        $specialites = $this->articleRepository->findSearch($data);

        return $this->render('website/specialite/index.html.twig', [
            'specialites'=>$specialites,
        ]);
    }

    /**
     * @Route("/specialites/{slug}", name="web_specialite")
     */
    public function specialite(Request $request,$slug): Response    {

        $data = new SearchData();
        $data->page = $request->get('page', 1);
        $data->tag= "specialite";
        $data->limit = 12;
        $specialites = $this->articleRepository->findSearch($data);     
        
        $specialite=$this->articleRepository->findOneBy(['slug'=>$slug]);


        return $this->render('website/specialite/detail.html.twig', [
            'specialite'=>$specialite,
            'specialites'=>$specialites,
        ]);

    }




    /**
     * @Route("/services_rendus", name="web_services_rendus")
     */
    public function servicesRenduIndex(Request $request): Response
    {
        
        $data = new SearchData();
        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchForm::class, $data);
        $form->handleRequest($request);

        $data->tag= "projet";
        // $data->limit = 1;
        $projets  = $this->articleRepository->findSearch($data);

        $categories=$this->categoryRepository->findAll();

        return $this->render('website/service/index.html.twig', [
            'projets'=>$projets,
            'categories'=>$categories
        ]);
    }    

    /**
     * @Route("/services_rendus/{slug}", name="web_service_rendu")
     */
    public function serviceRenduDetail($slug, Request $request): Response
    {
        $projet=$this->articleRepository->findOneBy(['slug'=>$slug]);

        return $this->render('website/service/detail.html.twig', [
            'projet'=>$projet
        ]);
    }  




    // /**
    //  * @Route("/bog/articles/recherche", name="web_articles_recherche")
    //  */
    // public function search(Request $request): Response
    // {
    //     $data = new SearchData();
    //     $data->page = $request->get('page', 1);
    //     $data->limit=12;
    //     $form = $this->createForm(SearchForm::class, $data);
    //     $form->handleRequest($request);

    //     $articles = $this->articleRepository->findSearch($data);

    //     return $this->render('website/article/search.html.twig', [
    //         'articles' => $articles,
    //         'form'=>$form->createView()
    //     ]);
    // }


    /**
     * @Route("/contact/message", name="web_contact")
     */
    public function sendMessageAction(Request $request,MailerInterface $mailer )
    {
        $referer=$request->headers->get('referer');
        if($request->getMethod()=="POST") {
            $data=$request->request->all();

            if (isset($data['recaptcha-response'])) {
                $response= $this->client->request(
                    'POST',
                    self::GR_URL,
                    [
                        'headers'=>[
                            'Content-Type' => 'application/json; charset=utf-8',
                            'Accept' => 'application/json'
                        ],
                        'body'=>[
                            'response'=>$data['recaptcha-response'],
                            'secret'=>self::GR_SECRET,
   
                        ]
                    ]
                );
                $response =json_decode($response->getContent());

                if (!$response->success) {
                    return $this->redirect($referer);
                }

                $message_body="Message de \n{$data['email'] } ({$data['fullName'] }) ;\n";
                if ($data['telephone'] ?? null) {
                    $message_body.= "Téléphone : {$data['telephone']} ;\n";                
                }
                if ($data['entreprise'] ?? null) {
                    $message_body.= "Entreprise : {$data['entreprise']} ;\n";                
                }            
                $message_body.="Contenu : ====> \n\n";
                $message_body.=$data['message'];
    
                $email = (new Email())
                ->subject($data['objet'])
                ->from('no-reply@quantum-togo.com')
                ->to('contact@quantum-togo.com')
                ->text($message_body);
    
                try {
                    $mailer->send($email);
                    $this->addFlash('success', 'Votre message a été envoyé avec succès. Nous allons vous répondre dans un instant.');
                } catch (\Throwable $th) {
                    //throw $th;
                    $this->addFlash('danger', 'Désolé ! Votre message n\'a pas pu être envoyé. Veuillez réessayer ou nous contacter à nos addresses ! ');
                }


            }else{
                // return new Response(Response::HTTP_INTERNAL_SERVER_ERROR);
                return $this->redirect($referer);
            }

        }
        return $this->redirect($referer);

    
    }    

}
