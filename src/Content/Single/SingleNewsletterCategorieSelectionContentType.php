<?php

declare(strict_types=1);

namespace App\Content\Single;

use App\Entity\Newsletters\Categories;
use Sulu\Component\Content\SimpleContentType;
use App\Repository\Newsletter\CategorieRepository;
use Sulu\Component\Content\Compat\PropertyInterface;

class SingleNewsletterCategorieSelectionContentType extends SimpleContentType
{
    /**
     * @var CategorieRepository
     */
    private $categorieRepository;

    public function __construct(CategorieRepository $categorieRepository)
    {
        parent::__construct('single_newsletter_categorie_selection');

        $this->categorieRepository = $categorieRepository;
    }

    /**
     * @return Categories[]
     */
    public function getContentData(PropertyInterface $property)
    {
        $id = $property->getValue();

        $categorie = $this->categorieRepository->find((int) $id);

        return $categorie;
    }

    /**
     * 
     */
    public function getViewData(PropertyInterface $property)
    {
        return $property->getValue();
    }
}
