<?php

namespace App\EventSubscriber;

use App\Entity\Membre;
use App\Entity\Article;
use App\Entity\Bulletin;
use Doctrine\ORM\Events;
use App\Utils\Notifications;
use App\Service\MessengerService;
use App\Repository\MembreRepository;
use Doctrine\Common\EventSubscriber;
use App\Message\SendNewsletterMessage;
use App\Repository\BulletinRepository;
use App\Service\SendNewsletterService;
use App\Entity\Newsletters\Newsletters;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Website\BulletinController;
use Doctrine\Persistence\Event\LifecycleEventArgs;
// use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Repository\Newsletters\NewslettersRepository;
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

    /** @var NewslettersRepository */

    private $newslettersRepository;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        EntityManagerInterface $em,
        MessageBusInterface $messageBus,
        MessengerService $messengerService,
        SendNewsletterService $sendNewsletter,
        NewslettersRepository $newslettersRepository
        )
    {
        $this->urlGenerator = $urlGenerator;
        $this->em=$em;
        $this->messageBus = $messageBus;
        $this->messengerService = $messengerService;
        $this->sendNewsletter = $sendNewsletter;
        $this->newslettersRepository = $newslettersRepository;
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
                        $newsletter=($this->em->getRepository(Newsletters::class))->findOneBy(['name'=>'Newsletter']);
                        $users=$newsletter->getCategories()->getUsers();
    
                        // ici on envois un email de newsletter destiné aux abnnés dans la queue de Messenger 
                        if (count($users)> 0) {
                            foreach ($users as $user) {
                                if ($user->getIsValid()) {
                                    // $this->sendNewsletter->send($user,$newsletter,$entity);
                                    $this->messageBus->dispatch( new SendNewsletterMessage($user->getId(),$newsletter->getId(),$entity->getId()));
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
