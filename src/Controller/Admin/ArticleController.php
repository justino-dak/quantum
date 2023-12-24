<?php

namespace App\Controller\Admin;

use DateTime;
use Exception;
use App\Entity\Article;
use FOS\RestBundle\View\View;
use App\Admin\NavigationAdmin;
use App\Repository\DomainRepository;
use App\Repository\ArticleRepository;
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
use Sulu\Bundle\TagBundle\Tag\TagRepositoryInterface;
use Sulu\Component\Security\SecuredControllerInterface;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Sulu\Bundle\CategoryBundle\Entity\CategoryRepository;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sulu\Bundle\CategoryBundle\Entity\CategoryRepositoryInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;

/**
 * @RouteResource("article")
 */

class ArticleController extends AbstractRestController implements ClassResourceInterface,SecuredControllerInterface
{
    const LIST_KEY="articles";
    const RESOURCE_KEY="articles";
    const FORM_KEY="article_form";


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
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var ArticleRepository
     */
    private $repository;


    public function __construct(
        ViewHandlerInterface $viewHandler,
        FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        RestHelperInterface $restHelper,
        ArticleRepository $repository ,
        MediaRepositoryInterface $mediaRepository,
        MediaManagerInterface $mediaManager,
        TagRepositoryInterface $tagRepository,
        CategoryRepositoryInterface $categoryRepository,
        Security $security
    )
    {
        $this->viewHandler = $viewHandler;
        $this->fieldDescriptorFactory = $fieldDescriptorFactory;
        $this->doctrineListRepresentationFactory = $doctrineListRepresentationFactory;
        $this->restHelper = $restHelper;
        $this->repository = $repository;
        $this->mediaRepository = $mediaRepository;
        $this->mediaManager = $mediaManager;
        $this->tagRepository = $tagRepository;
        $this->security = $security;
        $this->categoryRepository = $categoryRepository;

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
        
        $this->save($entity);

        // return $this->handleView($this->view(null));
        return $this->viewHandler->handle(View::create(null));

        
    }

    /**
     * @Rest\Post("/articles/{id}")
     */
    public function postTriggerAction(int $id, Request $request): Response
    {
        $article = $this->repository->find($id);
        if (!$article) {
            throw new NotFoundHttpException();
        }

        $this->repository->save($article);

        // return $this->handleView($this->view($Article));
        return $this->viewHandler->handle(View::create($article));

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
    protected function mapDataToEntity(array $data, Article $entity): void
    {
        // dump($data);
        if ($titre = $data['titre'] ?? null) {
            $entity->setTitre($titre);
        }

        $description = $data['description'];
        $entity->setDescription($description);

        if ($contenu = $data['contenu'] ?? null) {
            $entity->setContenu($contenu);
        }

        if ($thumbnailId = ($data['thumbnail']['id'] ?? null)) {
            $thumbnail = $this->mediaRepository->findMediaById($thumbnailId);
            $entity->setThumbnail($thumbnail); 
        }

        $medias=$entity->getMedias();
        if ($mediaIds = ($data['medias']["ids"] ?? null)) {
            foreach ($mediaIds as $id){
                $media = $this->mediaRepository->findMediaById($id); 
                $entity->addMedia($media);
            }
            foreach ($medias as $media) {
                if (!in_array($media->getId(),$mediaIds)) {
                    $entity->removeMedia($media);
                }
            }
        }


        if ($tags = ($data['tags'] ?? null)) {
            foreach ($tags as $name){
                $tag = $this->tagRepository->findTagByName($name); 
                $entity->addTag($tag);
            }
            foreach ($entity->getTags() as $tag) {
                if (!in_array($tag->getName(),$tags)) {
                    $entity->removeTag($tag);
                }
            }            
        }

        if ($categoryIds = ($data['categories'] ?? null)) {
            foreach ($categoryIds as $id){
                $category = $this->categoryRepository->find($id); 
                $entity->addCategory($category);
            }
            foreach ($entity->getCategories() as $category) {
                if (!in_array($category->getId(),$categoryIds)) {
                    $entity->removeCategory($category);
                }
            }            
        }
        

    }

    protected function load(int $id): ?Article
    {
        return $this->repository->find($id);
    }

    protected function create(): Article
    {
        return $this->repository->create();
        
    }

    protected function save(Article $entity): void
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
    
    public function apiEntity(Article $entity){
        // dump($entity);
        $thumbnail=$entity->getThumbnail();
        $apiThumbnail=null;
        if($thumbnail){
            $apiThumbnail=$this->mediaManager->getById($thumbnail->getId(),'fr');
        }



        $medias=[];        
        if($ids=$entity->getMedias() ?? null){
            foreach ($ids as $id){
                array_push($medias,$id->getId());
            }
        }
        $tags=[];        
        if($ids=$entity->getTags() ?? null){
            foreach ($ids as $id){
                array_push($tags,$id->getName());
            }
        }  
        $categories=[];        
        if($ids=$entity->getCategories() ?? null){
            foreach ($ids as $id){
                array_push($categories,$id->getId());
            }
        }         
        return [
            'id' =>$entity->getId(),
            'titre'=>$entity->getTitre(),
            'description'=> $entity->getDescription(),
            'contenu'=> $entity->getContenu(),
            'thumbnail'=>($apiThumbnail)? [
                'id' => $apiThumbnail->getId(),
                'url'=>$apiThumbnail->getUrl(),
                'thumbnails'=>$apiThumbnail->getFormats()
            ]: null,
            'medias'=>['ids'=>$medias],
            // 'tags'=>[]
            'tags'=>$tags,
            'categories'=> $categories
        ];
    }

    public function getLocale(Request $request)
    {
        return $request->get('locale');
    }

    public function getSecurityContext()
    {
        return NavigationAdmin::ARTICLE_SECURITY_CONTEXT;
    }


}
