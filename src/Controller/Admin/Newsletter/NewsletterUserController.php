<?php
namespace App\Controller\Admin\Newsletter;

use App\Admin\NavigationAdmin;
use DateTime;
use Exception;
use FOS\RestBundle\View\View;
use App\Entity\Newsletter\User;
use Sulu\Component\Rest\RestHelperInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\Newsletter\UserRepository;
use Sulu\Component\Rest\AbstractRestController;
use App\Common\DoctrineListRepresentationFactory;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sulu\Component\Security\SecuredControllerInterface;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;

/**
 * @RouteResource("newsletteruser")
 */

class NewsletterUserController extends AbstractRestController implements ClassResourceInterface,SecuredControllerInterface
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
     * @var UserRepository
     */
    private $repository;


    public function __construct(
        ViewHandlerInterface $viewHandler,
        FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        RestHelperInterface $restHelper,
        UserRepository $repository ,
        Security $security
    )
    {
        $this->viewHandler = $viewHandler;
        $this->fieldDescriptorFactory = $fieldDescriptorFactory;
        $this->doctrineListRepresentationFactory = $doctrineListRepresentationFactory;
        $this->restHelper = $restHelper;
        $this->repository = $repository;
        $this->security=$security;
    }

    public function cgetAction(): Response
    {
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            User::RESOURCE_KEY,
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

        // $entity=$this->apiEntity($entity);
        // return $this->handleView($this->view($entity));
        return $this->viewHandler->handle(View::create($entity));

    }


    public function postAction(Request $request): Response
    {
        $entity = $this->create($request);
        
        $this->mapDataToEntity($request->request->all(), $entity);

        $this->save($entity);

        // return $this->handleView($this->view(null));
        return $this->viewHandler->handle(View::create(null));

        
    }

    /**
     * @Rest\Post("/newsletterusers/{id}")
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
     * @param array<string, mixed> $data
     */
    protected function mapDataToEntity(array $data, User $entity): void
    {
        if ($email = $data['email'] ?? null) {
            $entity->setEmail( strtolower(trim($email)));
        }
       
    }

    protected function load(int $id): ?User
    {
        return $this->repository->find($id);
    }

    protected function create(): User
    {
        return $this->repository->create();
        
    }

    protected function save(User $entity): void
    {
       
        $this->repository->save($entity);
    }

    protected function remove(int $id): void
    {
        $this->repository->remove($id);
    }


    public function getLocale(Request $request)
    {
        return $request->get('locale');
    }

    public function getSecurityContext()
    {
        return NavigationAdmin::NEWSLETTER_USER_SECURITY_CONTEXT;
    }

    

}
