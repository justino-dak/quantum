<?php
namespace App\Controller\Admin\Newsletter;

use DateTime;
use Exception;
use FOS\RestBundle\View\View;
use App\Admin\NavigationAdmin;
use App\Service\MessengerService;
use App\Entity\Newsletter\Newsletter;
use App\Message\SendNewsletterMessage;
use App\Service\SendNewsletterService;
use Sulu\Component\Rest\RestHelperInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Sulu\Component\Rest\AbstractRestController;
use App\Common\DoctrineListRepresentationFactory;
use App\Repository\Newsletter\CategorieRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use App\Repository\Newsletter\NewsletterRepository;
use App\Repository\Newsletter\UserRepository;
use Symfony\Component\Messenger\MessageBusInterface;
use Sulu\Component\Security\SecuredControllerInterface;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;

/**
 * @RouteResource("newsletter")
 */

class NewsletterController extends AbstractRestController implements ClassResourceInterface,SecuredControllerInterface
{

    /**
     * @var Security
     */
    private $security;
    /**
     * @var ViewHandlerInterface
     * 
     */
    private $viewHandler;

    /**
     * @var FieldDescriptorFactoryInterface
     */
    private $fieldDescriptorFactory;

    /**
     * @var DoctrineListRepresentationFactory
     */
    private $doctrineListRepresentationFactory;

    /**
     * @var RestHelperInterface
     */
    private $restHelper;


    /**
     * @var NewsletterRepository
     */
    private $repository;

    /**
     * @var CategorieRepository
     */
    private $categoriesRepository;

    /** @var MessageBusInterface */
    private $messageBus;

    /** @var UserRepository */
    private $userRepository;


    public function __construct(
        ViewHandlerInterface $viewHandler,
        FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        RestHelperInterface $restHelper,
        NewsletterRepository $repository ,
        CategorieRepository $categoriesRepository,
        Security $security,
        MessageBusInterface $messageBus,
        UserRepository $userRepository

    )
    {
        $this->viewHandler = $viewHandler;
        $this->fieldDescriptorFactory = $fieldDescriptorFactory;
        $this->doctrineListRepresentationFactory = $doctrineListRepresentationFactory;
        $this->restHelper = $restHelper;
        $this->repository = $repository;
        $this->categoriesRepository = $categoriesRepository;
        $this->security=$security;
        $this->messageBus = $messageBus;
        $this->userRepository = $userRepository;

    }

    public function cgetAction(): Response
    {
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            Newsletter::RESOURCE_KEY,
            null,
            [],
            []
        );

        // return $this->handleView($this->view($listRepresentation));
        return $this->viewHandler->handle(View::create($listRepresentation));
    }

    public function getAction(int $id, Request $request): Response
    {
        $entity = $this->load($id);
       
        if (!$entity) {
            throw new NotFoundHttpException();
        }

        $entity=$this->apiEntity($entity);
        // return $this->handleView($this->view($entity));
        return $this->viewHandler->handle(View::create($entity));

    }


    public function postAction(Request $request): Response
    {
        $entity = $this->create($request);

        $this->mapDataToEntity($request->request->all(), $entity);

        $entity->setCreatedAt(new DateTime());

        $this->save($entity);

        // return $this->handleView($this->view(null));
        return $this->viewHandler->handle(View::create(null));

        
    }

    /**
     * @Rest\Post("/newsletters/{id}")
     */
    public function postTriggerAction(int $id, Request $request): Response
    {
        $postType = $this->repository->find($id);
        if (!$postType) {
            throw new NotFoundHttpException();
        }

        $this->repository->save($postType);

        // return $this->handleView($this->view($Article));
        return $this->viewHandler->handle(View::create($postType));

    }

    public function putAction(int $id, Request $request): Response
    {
        $entity = $this->load($id);
        if (!$entity) {
            throw new Exception("error");
            throw new NotFoundHttpException();
        }

        $this->mapDataToEntity($request->request->all(), $entity);

        $this->save($entity);

        // return $this->handleView($this->view($entity));
        return $this->viewHandler->handle(View::create($entity));

    }


    public function deleteAction(int $id): Response
    {
        $this->remove($id);

        // return $this->handleView($this->view());
        return $this->viewHandler->handle(View::create(null));
    }

    /**
     * @Rest\Patch("/newsletters/{id}/notify")
    */
    public function notifyAction(int $id, Request $request): Response
    {
        $entity = $this->load($id);
        if (!$entity) {
            throw new Exception("error");
            throw new NotFoundHttpException();
        }
        $request=Request::createFromGlobals();
        $homepage=$request->getSchemeAndHttpHost();
        $newsletter=$entity;
        // $users=$this->userRepository->findBy(['is_valid'=>true]);
        $users=$this->userRepository->findAll();


        // ici on envois un email de newsletter destiné aux abnnés dans la queue de Messenger 
        if (count($users)> 0) {
            foreach ($users as $user) {
                if ($user->getIsValid()) {
                    // $this->sendNewsletter->send($user,$newsletter,$entity);
                    $this->messageBus->dispatch( new SendNewsletterMessage($user->getId(),$newsletter->getId(),null,$homepage));
                }
            }
        }

        $entity=$this->apiEntity($entity);

        // return $this->handleView($this->view($entity));
        return $this->viewHandler->handle(View::create($entity));

    }    
  

    /**
     * @param array<string, mixed> $data
     */
    protected function mapDataToEntity(array $data, Newsletter $entity): void
    {
        if ($name = $data['name'] ?? null) {
            $entity->setName( $name);
        }
        if ($content = $data['content'] ?? null) {
            $entity->setContent($content);
        }
        if ($category = ($data['category'] ?? null)) {
                // $tag = $this->categoriesRepository->find($id); 
                $entity->setCategorie($this->categoriesRepository->find($category));         
        }

       
    }

    protected function load(int $id): ?Newsletter
    {
        return $this->repository->find($id);
    }

    protected function create(): Newsletter
    {
        return $this->repository->create();
        
    }

    protected function save(Newsletter $entity): void
    {
       
        $this->repository->save($entity);
    }

    protected function remove(int $id): void
    {
        $this->repository->remove($id);
    }

    public function apiEntity(Newsletter $entity){

        return [
            'id' =>$entity->getId(),
            'name'=>$entity->getName(),
            'content'=> $entity->getContent(),
            'category'=>$entity->getCategorie()->getId()
        ];
    }


    public function getLocale(Request $request)
    {
        return $request->get('locale');
    }

    public function getSecurityContext()
    {
        return NavigationAdmin::NEWSLETTER_SECURITY_CONTEXT;
    }

    

}
