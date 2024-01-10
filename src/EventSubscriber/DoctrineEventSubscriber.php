<?php

namespace App\EventSubscriber;

use App\Entity\Article;
use Doctrine\ORM\Events;
use App\Service\MessengerService;
use Doctrine\Common\EventSubscriber;
use App\Entity\Newsletter\Newsletter;
use App\Message\SendNewsletterMessage;
use App\Service\SendNewsletterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\Repository\Newsletter\NewsletterRepository;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DoctrineEventSubscriber implements EventSubscriber
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;



    /** @var EntityManagerInterface */
    private $em;

    /** @var MessageBusInterface */
    private $messageBus;

    /** @var MessengerService */
    private $messengerService;

    /** @var SendNewsletterService*/
    private $sendNewsletter;

    /** @var NewsletterRepository */

    private $newsletterRepository;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        EntityManagerInterface $em,
        MessageBusInterface $messageBus,
        MessengerService $messengerService,
        SendNewsletterService $sendNewsletter,
        NewsletterRepository $newsletterRepository
        )
    {
        $this->urlGenerator = $urlGenerator;
        $this->em=$em;
        $this->messageBus = $messageBus;
        $this->messengerService = $messengerService;
        $this->sendNewsletter = $sendNewsletter;
        $this->newsletterRepository = $newsletterRepository;
    }

    public  function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::postPersist,
            Events::postUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->index("prePersist", $args);

    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->index("postPersist", $args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->index("postUpdate", $args);
    }


    public function index(string $action,LifecycleEventArgs $args)
    {
        $entity=$args->getObject();
       
        switch ($action) {
            case 'postUpdate':
                break;

            case 'prePersist':
                break;

            case 'postPersist':

                /// Article
                if ($entity instanceof Article) {

                        // $newsletter=$this->newslettersRepository->findOneByCategories ('Newsletter');
                        $newsletter=($this->em->getRepository(Newsletter::class))->findOneBy(['name'=>'Newsletter']);
                        $users=$newsletter->getCategorie()->getUsers();
    
                        // ici on envois un email de newsletter destiné aux abnnés dans la queue de Messenger 
                        if (count($users)> 0) {
                            foreach ($users as $user) {
                                if ($user->getIsValid()) {
                                    // $this->sendNewsletter->send($user,$newsletter,$entity);
                                    $this->messageBus->dispatch( new SendNewsletterMessage($user->getId(),$newsletter->getId(),$entity->getId(),(Request::createFromGlobals())->getSchemeAndHttpHost()));
                                }
                            }
                        }

                }
                
                break;
            
            default:
                # code...
                break;
        }
    }
  

}
