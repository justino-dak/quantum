<?php

declare(strict_types=1);

namespace App\Common;

use Sulu\Component\Rest\RestHelperInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactory;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;

class DoctrineListRepresentationFactory
{
    /**
     * @var RestHelperInterface
     */
    private $restHelper;

    /**
     * @var MediaManagerInterface
     * 
     */
    private $mediaManager;

    /**
     * @var DoctrineListBuilderFactoryInterface
     */
    private $listBuilderFactory;

    /**
     * @var FieldDescriptorFactoryInterface
     */
    private $fieldDescriptorFactory;

    public function __construct(
        RestHelperInterface $restHelper,
        DoctrineListBuilderFactoryInterface $listBuilderFactory,
        FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        MediaManagerInterface $mediaManager
    ) {
        $this->restHelper = $restHelper;
        $this->listBuilderFactory = $listBuilderFactory;
        $this->fieldDescriptorFactory = $fieldDescriptorFactory;
        $this->mediaManager= $mediaManager;
    }

    /**
     * @param mixed[] $filters
     * @param mixed[] $parameters
     */
    public function createDoctrineListRepresentation(
        string $resourceKey,
        string $mediaJoin=null,
        array $filters = [],
        array $parameters = []
    ): PaginatedRepresentation {
        /** @var DoctrineFieldDescriptor[] $fieldDescriptors */
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors($resourceKey);

        $listBuilder = $this->listBuilderFactory->create($fieldDescriptors['id']->getEntityName());
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        // foreach ($parameters as $key => $value) {
        //     $listBuilder->setParameter($key, $value);
        // }

        foreach ($filters as $key => $value) {
            $listBuilder->where($fieldDescriptors[$key], $value);
        }

        $list = $listBuilder->execute();
        if($mediaJoin){
            $list=$this->mapThumnails($list,$mediaJoin);
        }

        return new PaginatedRepresentation(
            $list,
            $resourceKey,
            (int) $listBuilder->getCurrentPage(),
            (int) $listBuilder->getLimit(),
            (int) $listBuilder->count()
        );
    }


    /**
     * Takes an array of entity and resets the thumbnail containing the media id with
     * the actual urls to the avatars thumbnail.
     *
     * @param array $dataList
     * @param string $fieldName
     *
     * @return array
     */
    
    private function mapThumnails($dataList,$fieldName)
    {
        $ids = \array_filter(\array_column($dataList, $fieldName));
        $thumbnails = $this->mediaManager->getFormatUrls($ids,null);
        foreach ($dataList as $key => $data) {
            if (\array_key_exists($fieldName, $data)
                && $data[$fieldName]
                && \array_key_exists($data[$fieldName], $thumbnails)
            ) {
                $dataList[$key][$fieldName] = $thumbnails[$data[$fieldName]];
            }
        }

        return $dataList;
    }
}
