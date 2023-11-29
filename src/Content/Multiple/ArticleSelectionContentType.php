<?php


declare(strict_types=1);

namespace App\Content\Multiple;


use App\Entity\Article;
use App\Repository\ArticleRepository;
use Sulu\Component\Content\SimpleContentType;
use Sulu\Component\Content\Compat\PropertyInterface;

class ArticleSelectionContentType extends SimpleContentType
{
    /**
     * @var ArticleRepository
     */
    private $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        parent::__construct('article_selection');

        $this->articleRepository = $articleRepository;
    }

    /**
     * @return Article[]
     */
    public function getContentData(PropertyInterface $property): array
    {
        $ids = $property->getValue();
        // $locale = $property->getStructure()->getLanguageCode();

        $articles = [];
        foreach ($ids ?: [] as $id) {
            $article = $this->articleRepository->find((int) $id);
            if ($article ) {
                $articles[] = $article;
            }
        }

        return $articles;
    }

    /**
     * @return mixed[]
     */
    public function getViewData(PropertyInterface $property)
    {
        return $property->getValue();
    }
}
