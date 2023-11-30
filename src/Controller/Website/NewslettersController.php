<?php

namespace App\Controller\Website;

use App\Entity\Newsletter\User;
use App\Service\MessengerService;
use App\Entity\Newsletter\Categories;
use App\Message\SendNewsletterMessage;
use App\Service\SendNewsletterService;
use App\Entity\Newsletter\Newsletter;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\Newsletter\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\Newsletter\CategorieRepository;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Repository\Newsletter\NewsletterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NewslettersController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private  $usersRepository;

    /**
     * @var CategoriesRepository
     */
    private  $categoriesRepository;

    /**
     * @var NewslettersRepository
     */
    private  $newslettersRepository;

    /**
     * @var SendNewsletterService
     */
    private  $sendNewsletterService;

    public function __construct(
        UserRepository $usersRepository,
        CategorieRepository $categoriesRepository,
        NewsletterRepository $newslettersRepository,
        SendNewsletterService $sendNewsletterService
        )
    {
        $this->usersRepository = $usersRepository;
        $this->categoriesRepository = $categoriesRepository;
        $this->newslettersRepository = $newslettersRepository;
        $this->sendNewsletterService = $sendNewsletterService;
    }

    /**
     * @Route("/newsletters/inscription", name="web_newsletters_inscription")
     */
    public function addAction(Request $request, MailerInterface $mailer): Response
    {
        $referer = $request->headers->get('referer');
        $user=$this->usersRepository->create();
        
        /** @var array */
        $data =$request->request->all();

        if ($data['email'] ?? null) {
            $user->setEmail(strtolower(trim($data['email'])));
        }else {
            return $this->redirect($referer);
        }

        $token=hash('sha256', uniqid()); // creation ddu token
        $user->setValidationToken($token);
        $categories = $this->categoriesRepository->findOneBy(['name'=>'Newsletter']);
        if($categories){
            $user->addCategory($categories);
        }      

        //Ici on cree le message
        $email=(new TemplatedEmail())
                ->from('no-reply@universaquatic.com')
                ->to($user->getEmail())
                ->subject('CONFIRMATION D\'ADRESSE E-MAIL')
                ->htmlTemplate('emails/inscription.html.twig')
                // ->context(compact('user', 'token'))
                ;

        // ici nous verifions si utilisateur eiste deja
        $duplicatedUsers=$this->usersRepository->findOneBy(['email'=>$user->getEmail()]);

        if ( $duplicatedUsers) {
            if ($duplicatedUsers->getIsValid()) {
                $this->addFlash('error', "Echec: cet email a été déjà enregistré. Veuillez reésayer avec un autre .");
            }else{
                try {
                    $user=$duplicatedUsers;
                    $token=$duplicatedUsers->getValidationToken();
                    $email->context(compact('user', 'token'));
                    $mailer->send($email);// on envois le message
                    $this->addFlash('message','Un email a été envoyé à votre adresse. Veuillez le chercher parmis les méssages non désirables pour confirmer votre inscription .');
                } catch (\Throwable $th) {
                    $this->addFlash('error', $th->getMessage());

                    // $this->addFlash('error', 'Echec: une erreur inconue s\'est produite. Veuillez reéssayer.');
                }
            }
            return $this->render('newsletters/confirm.html.twig' );

        } else {
            try {
                $this->usersRepository->save($user); // on sauvegarge l'adresse
                $email->context(compact('user', 'token'));
                $mailer->send($email);// on envois le message

                $this->addFlash('message','Un email a été envoyé à votre adresse. Veuillez le chercher parmis les méssages non désirables pour confirmer votre inscription .');
                
            } catch (\Throwable $th) {

                $this->addFlash('error', 'Echec: une erreur inconue s\'est produite. Veuillez reéssayer.'.$th->getMessage());
            }
            return $this->render('newsletters/confirm.html.twig' );  
        }
        
        return $this->redirect($referer);
    }

    /**
     * @Route("/newsletter/{id}/{token}",name="web_newsletters_confirm")
     */
    public function confirm ( $id, $token) 
    {
        $user=$this->usersRepository->find($id);
        if ($user->getValidationToken() != $token) {
            throw $this->createNotFoundException('Page non trouvé');
        }
        $user->setIsValid(true);
        $this->usersRepository->save($user);

        $this->addFlash('message','Votre inscription a été acceptée');
        return $this->render('newsletters/confirm.html.twig' );
    }

    /**
     * @Route("/newsletters/{id}/send" , name="web_newsletter_send")
     */
    public function send( $id, MessageBusInterface $messageBus)
    {
        $newsletter=$this->newslettersRepository->find($id);
        $users= $newsletter->getCategories()->getUsers();

        //Ici on envoie le nnewsleter à chque inscrit
        foreach($users as $user){
            if($user->getIsValid()){
                // $this->sendNewsletterService->send($user,$newsletter);
                $messageBus->dispatch(new SendNewsletterMessage($user->getId(),$id,1));
            }
        }
        return  new Response(null);
    }

    /**
     * @Route("/newsletter/unsubcribe/{id}/{newsletter}/{token}" , name="web_newsletter_unsubcribe")
     */
    public function unsubcrib($id, $newsletter, $token)
    {
        $user=$this->usersRepository->find($id);
        $newsletter=$this->newslettersRepository->find($newsletter);

        if(!$user){
            throw $this->createNotFoundException('Page non trouvée');
        }

        if($user->getValidationToken() != $token){
            throw $this->createNotFoundException('Page non trouvée');
        }


        if(count($user->getCategories()) > 1){
            $user->removeCategory($newsletter->getCategories());
            $this->usersRepository->save($user);
        }else {

            $this->usersRepository->remove($user->getId());
        }

        $this->addFlash('message','Vous  vous ête désabonné(e) à notre newsletter avec succès');
        return $this->render('newsletters/confirm.html.twig' );

    }

    /**
     * @Route("/messenger/async",name="web_messenger_async")
     */
    public function async ( MessengerService $messenger) 
    {
        // $messenger->async();
        return new Response($messenger->async());
    }

    /**
     * @Route("/messenger/stop",name="web_messenger_stop")
     */
    public function stop ( MessengerService $messenger) 
    {
        // $messenger->stop();
        return new Response($messenger->stop());
    }

    /**
     * @Route("/messenger/failed",name="web_messenger_failed")
     */
    public function failed ( MessengerService $messenger) 
    {
        // $messenger->failed();
        return new Response($messenger->failed());
    }

}
