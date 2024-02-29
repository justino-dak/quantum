<?php

namespace App\Controller\Admin;

use DateTime;
use Exception;
use App\Entity\Team;
use App\Entity\Backup;
use FOS\RestBundle\View\View;
use App\Admin\NavigationAdmin;
use App\Repository\TeamRepository;
use App\Repository\BackupRepository;
use App\Repository\DomainRepository;
use App\Repository\ActivityRepository;
use App\Repository\PostTypeRepository;
use Sulu\Component\Rest\RestHelperInterface;
use Symfony\Component\Filesystem\Filesystem;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Sulu\Component\Rest\AbstractRestController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Routing\Annotation\Route;
use App\Common\DoctrineListRepresentationFactory;
use Symfony\Component\HttpKernel\KernelInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sulu\Bundle\CategoryBundle\Entity\CategoryRepositoryInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @RouteResource("backup")
 */

class BackupController extends AbstractRestController implements ClassResourceInterface,SecuredControllerInterface
{
    const LIST_KEY="backups";
    const RESOURCE_KEY="backups";


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
     * @var BackupRepository
     */
    private $repository;

    /**
     * @var KernelInterface
     */
    private $kernel;

    public function __construct(
        ViewHandlerInterface $viewHandler,
        FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        RestHelperInterface $restHelper,
        BackupRepository $repository ,
        MediaRepositoryInterface $mediaRepository,
        MediaManagerInterface $mediaManager,
        KernelInterface $kernel,
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
        $this->kernel=$kernel;

    }

    public function cgetAction(Request $request): Response
    {
        $locale = $request->query->get('locale');
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            static::RESOURCE_KEY,
            null,
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
        
        $entity->setCreatedAt(new DateTime());

        // dump($data);
        $this->save($entity);

        // return $this->handleView($this->view(null));
        return $this->viewHandler->handle(View::create(null));

        
    }
    /**
     * @Rest\Get("/backup/generate")
    */    
    public function backupAction(Request $request): Response
    {
        try {
            $application = new Application($this->kernel);
            $application->setAutoExit(false);
    
            $input = new ArrayInput([
                'command' => 'app:backup',
            ]);
            $output = new NullOutput();
    
            $application->run($input, $output);
                    
        } catch (\Throwable $th) {
            throw $th;
            return $this->viewHandler->handle(View::create(Response::HTTP_INTERNAL_SERVER_ERROR));
        }
        return $this->viewHandler->handle(View::create(Response::HTTP_OK));
        
    }

    /**
     * @Rest\Get("/backups/{id}/download")
    */    
    public function downloadAction(int $id,Request $request): Response
    {
        try {
            $entity = $this->load($id);
            $file=$entity->getLink();
    
            if (file_exists($file)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($file).'"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));

                while (ob_get_level()) {
                    ob_end_clean();
                }
                readfile($file);
                exit();
            }
            // $entity->setDownload($entity->getDownload() + 1);   
          
        } catch (\Throwable $th) {
            return $this->viewHandler->handle(View::create(Response::HTTP_INTERNAL_SERVER_ERROR));       
         }
        
    }


    /**
     * @Rest\Post("/backup/{id}")
     */
    public function postTriggerAction(int $id, Request $request): Response
    {
        $backup = $this->repository->find($id);
        if (! $backup) {
            throw new NotFoundHttpException();
        }

        $this->repository->save( $backup);

        // return $this->handleView($this->view($backup));
        return $this->viewHandler->handle(View::create( $backup));

    }

    public function putAction(int $id, Request $request): Response
    {
        $entity = $this->load($id);
        if (!$entity) {
            throw new Exception("error");
            throw new NotFoundHttpException();
        }

        $this->save($entity);

        // return $this->handleView($this->view($entity));
        return $this->viewHandler->handle(View::create($entity));

    }


    protected function load(int $id): ?Backup
    {
        return $this->repository->find($id);
    }

    protected function create(): Backup
    {
        return $this->repository->create();
        
    }

    protected function save(Backup $entity): void
    {
       
        $this->repository->save($entity);
    }

    public function deleteAction(int $id): Response
    {
        $backup=$this->load($id);
        $link=$backup->getLink();
        $fileSystem=new Filesystem();
        
        if ($fileSystem->exists($link)) {
            $fileSystem->remove($link);
        }
        $this->remove($id);


        // return $this->handleView($this->view());
        return $this->viewHandler->handle(View::create(null));
    }

    protected function remove(int $id): void
    {
        $this->repository->remove($id);
    }
    
    public function apiEntity(Backup $entity){

        return [
            'id' =>$entity->getId(),
           ];
    }

    public function getLocale(Request $request)
    {
        return $request->get('locale');
    }

    public function getSecurityContext()
    {
        return NavigationAdmin::BACKUP_SECURITY_CONTEXT;
    }


}
