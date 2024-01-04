<?php

namespace App\Controller\Admin;

use DateTime;
use Exception;
use App\Entity\Team;
use FOS\RestBundle\View\View;
use App\Admin\NavigationAdmin;
use App\Repository\DomainRepository;
use App\Repository\TeamRepository;
use App\Repository\ActivityRepository;
use App\Repository\PostTypeRepository;
use Sulu\Component\Rest\RestHelperInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Sulu\Component\Rest\AbstractRestController;
use Symfony\Component\Routing\Annotation\Route;
use App\Common\DoctrineListRepresentationFactory;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sulu\Component\Security\SecuredControllerInterface;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sulu\Bundle\CategoryBundle\Entity\CategoryRepositoryInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;

/**
 * @RouteResource("team")
 */

class TeamController extends AbstractRestController implements ClassResourceInterface,SecuredControllerInterface
{
    const LIST_KEY="teams";
    const RESOURCE_KEY="teams";
    const FORM_KEY="team_form";


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
     * @var MediaRepositoryInterface
     */
    private $mediaRepository;

    /**
     * @var MediaManagerInterface
     * 
     */
    private $mediaManager;


    /**
     * @var TeamRepository
     */
    private $repository;


    public function __construct(
        ViewHandlerInterface $viewHandler,
        FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        RestHelperInterface $restHelper,
        TeamRepository $repository ,
        MediaRepositoryInterface $mediaRepository,
        MediaManagerInterface $mediaManager,
        Security $security
    )
    {
        $this->viewHandler = $viewHandler;
        $this->fieldDescriptorFactory = $fieldDescriptorFactory;
        $this->doctrineListRepresentationFactory = $doctrineListRepresentationFactory;
        $this->restHelper = $restHelper;
        $this->repository = $repository;
        $this->mediaRepository=$mediaRepository;
        $this->mediaManager=$mediaManager;
        $this->security=$security;

    }

    public function cgetAction(Request $request): Response
    {
        $locale = $request->query->get('locale');
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            static::RESOURCE_KEY,
            'thumbnail',
            [],
            ['locale' => $locale]
        );

        // return $this->handleView($this->view($listRepresentation));
        return $this->viewHandler->handle(View::create($listRepresentation));
    }

    public function getAction(int $id, Request $request): Response
    {
        $entity = $this->load($id, $request);
       
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

        $curentUser=$this->security->getUser();
        if ($createdBy = $curentUser ?? null) {
            $entity->setCreatedBy($createdBy);
        }
        // dump($data);
        $this->save($entity);

        // return $this->handleView($this->view(null));
        return $this->viewHandler->handle(View::create(null));

        
    }

    /**
     * @Rest\Post("/teams/{id}")
     */
    public function postTriggerAction(int $id, Request $request): Response
    {
        $team = $this->repository->find($id);
        if (! $team) {
            throw new NotFoundHttpException();
        }

        $this->repository->save( $team);

        // return $this->handleView($this->view($team));
        return $this->viewHandler->handle(View::create( $team));

    }

    public function putAction(int $id, Request $request): Response
    {
        $entity = $this->load($id);
        if (!$entity) {
            throw new Exception("error");
            throw new NotFoundHttpException();
        }

        $this->mapDataToEntity($request->request->all(), $entity);

        $entity->setUpdatedAt(new DateTime());

        $curentUser=$this->security->getUser();
        if ($updatedBy = $curentUser ?? null) {
            $entity->setUpdatedBy($updatedBy);
        }

        $this->save($entity);

        // return $this->handleView($this->view($entity));
        return $this->viewHandler->handle(View::create($entity));

    }

    /**
     * @param array<string, mixed> $data
     */
    protected function mapDataToEntity(array $data, Team $entity): void
    {
        // dump($data);
        if ($nom = $data['nom'] ?? null) {
            $entity->setNom($nom);
        }
        if ($prenoms = $data['prenoms'] ?? null) {
            $entity->setPrenoms($prenoms);
        }
        if ($poste = $data['poste'] ?? null) {
            $entity->setPoste($poste);
        }

        if ($whatsapp = $data['whatsapp'] ?? null) {
            $entity->setWhatsapp($whatsapp);
        }
        if ($facebook = $data['facebook'] ?? null) {
            $entity->setFacebook($facebook);
        }
        if ($youtube = $data['youtube'] ?? null) {
            $entity->setYoutube($youtube);
        }
        if ($instagram = $data['instagram'] ?? null) {
            $entity->setInstagram($instagram);
        }
        if ($linkedin = $data['linkedin'] ?? null) {
            $entity->setLinkedin($linkedin);
        }
        if ($twitter = $data['twitter'] ?? null) {
            $entity->setTwitter($twitter);
        }
        $description = $data['description'];
        $entity->setDescription($description);

        if ($thumbnailId = ($data['thumbnail']['id'])) {
            $thumbnail = $this->mediaRepository->findMediaById($thumbnailId);
            $entity->setThumbnail($thumbnail); 
        }


    }

    protected function load(int $id): ?Team
    {
        return $this->repository->find($id);
    }

    protected function create(): Team
    {
        return $this->repository->create();
        
    }

    protected function save(Team $entity): void
    {
       
        $this->repository->save($entity);
    }

    public function deleteAction(int $id): Response
    {
        $this->remove($id);

        // return $this->handleView($this->view());
        return $this->viewHandler->handle(View::create(null));
    }

    protected function remove(int $id): void
    {
        $this->repository->remove($id);
    }
    
    public function apiEntity(Team $entity){
        $thumbnail=$entity->getThumbnail();
        $apiThumbnail=null;
        if($thumbnail){
            $apiThumbnail=$this->mediaManager->getById($thumbnail->getId(),'fr');
        }

        return [
            'id' =>$entity->getId(),
            'nom'=>$entity->getNom(),
            'prenoms'=>$entity->getPrenoms(),
            'poste'=>$entity->getPoste(),
            'description'=> $entity->getDescription(),
            'whatsapp'=>$entity->getWhatsapp(),
            'facebook'=>$entity->getFacebook(),
            'youtube'=>$entity->getYoutube(),
            'instagram'=>$entity->getInstagram(),
            'linkedin'=>$entity->getLinkedin(),
            'twitter'=>$entity->getTwitter(),
            'thumbnail'=>($apiThumbnail)? [
                'id' => $apiThumbnail->getId(),
                'url'=>$apiThumbnail->getUrl(),
                'thumbnails'=>$apiThumbnail->getFormats()
            ]: null,
           ];
    }

    public function getLocale(Request $request)
    {
        return $request->get('locale');
    }

    public function getSecurityContext()
    {
        return NavigationAdmin::TEAM_SECURITY_CONTEXT;
    }


}
