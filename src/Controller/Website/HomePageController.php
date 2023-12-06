<?php

namespace App\Controller\Website;

use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;
use Sulu\Component\Content\Compat\StructureInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sulu\Bundle\WebsiteBundle\Controller\DefaultController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class HomePageController extends DefaultController
{
    /**
     * @var ArticleRepository
    */
    private $articleRepository;

    public function __construct(
        ArticleRepository $articleRepository,
    ){
        $this->articleRepository = $articleRepository;

    }

    protected function getAttributes($attributes, StructureInterface $structure = null, $preview = false)
    {
        $attributes = parent::getAttributes($attributes, $structure, $preview);
        $customAttributes =$this->getCustomAttributes();

            foreach ($customAttributes as $key => $value) {
                $attributes['content'][$key]=$value;
            }
           
        return $attributes;
    }

    protected function getCustomAttributes()
    {
        $articlesRecentes=$this->articleRepository->findBy([],['id'=>'DESC'],3);
        $aboutus=$this->articleRepository->findByTag('aboutus');


        return [
            'aboutus'=>(count($aboutus)> 0)?$aboutus[0]:null,
            'articlesRecentes'=>$articlesRecentes,
        ];
    }

   
}