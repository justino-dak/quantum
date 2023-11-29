<?php

declare(strict_types=1);

namespace App\Content\Multiple;

use App\Entity\Categorie;
use Sulu\Component\Content\SimpleContentType;
use App\Repository\Newsletter\CategorieRepository;
use Sulu\Component\Content\Compat\PropertyInterface;

class NewsletterCategorieSelectionContentType extends SimpleContentType
{
    /**
     * @var CategoriesRepository
     */
    private $categorieRepository;

    public function __construct(CategorieRepository $categorieRepository)
    {
        parent::__construct('newsletter_categorie_selection');

        $this->categorieRepository = $categorieRepository;
    }

    /**
     * @return Categorie[]
     */
    public function getContentData(PropertyInterface $property): array
    {
        $ids = $property->getValue();
        // $locale = $property->getStructure()->getLanguageCode();

        $categories = [];
        foreach ($ids ?: [] as $id) {
            $categorie = $this->categoriesRepository->find((int) $id);
            if ($categorie ) {
                $categories[] = $categorie;
            }
        }

        return $categories;
    }

    /**
     * @return mixed[]
     */
    public function getViewData(PropertyInterface $property)
    {
        return $property->getValue();
    }
}
