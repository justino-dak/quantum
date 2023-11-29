<?php


declare(strict_types=1);

namespace App\Content\Single;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Sulu\Component\Content\SimpleContentType;
use Sulu\Component\Content\Compat\PropertyInterface;

class SingleArticleSelectionContentType extends SimpleContentType
{
    /**
     * @var ArticleRepository
     */
    private $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        parent::__construct('single_article_selection');

        $this->articleRepository = $articleRepository;
    }

    /**
     * @return Article[]
     */
    public function getContentData(PropertyInterface $property)
    {
        $id = $property->getValue();

        $article = $this->articleRepository->find((int) $id);

        return $article;
    }

    /**
     * 
     */
    public function getViewData(PropertyInterface $property)
    {
        return $property->getValue();
    }
}
