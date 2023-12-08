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
use Sulu\Bundle\CategoryBundle\Entity\CategoryRepositoryInterface;

class HomePageController extends DefaultController
{
    /**
     * @var ArticleRepository
    */
    private $articleRepository;

    /**
     * @var CategoryRepositoryInterface
    */
    private $categoryRepository;

    public function __construct(
        ArticleRepository $articleRepository,
        CategoryRepositoryInterface $categoryRepository
    ){
        $this->articleRepository = $articleRepository;
        $this->categoryRepository = $categoryRepository;
    }

    protected function getAttributes($attributes, StructureInterface $structure = null, $preview = false)
    {
        $attributes = parent::getAttributes($attributes, $structure, $preview);
        $customAttributes =$this->getCustomAttributes();

            foreach ($customAttributes as $key => $value) {
                // $attributes['content'][$key]=$value;
                $attributes[$key]=$value;
            }
           
        return $attributes;
    }

    protected function getCustomAttributes()
    {
        $articles=$this->articleRepository->findBy([],['id'=>'DESC'],3);
        $aboutus=$this->articleRepository->findByTag('presentation');
        $temoignages=$this->articleRepository->findByTag('temoignage');
        $valeurs=$this->articleRepository->findByTag('valeur');
        $clients=$this->articleRepository->findByTag('client');
        $projets=$this->articleRepository->findByTag('presentation',6);
        $specialites=$this->articleRepository->findByTag('specialite');
        $categories=$this->categoryRepository->findAll();




        return [
            'presentation'=>(count($aboutus)> 0)?$aboutus[0]:null,
            'articles'=>$articles,
            'temoignages'=>$temoignages,
            'valeurs'=>$valeurs,
            'clients'=>$clients,
            'projets'=>$projets,
            'specialites'=>$specialites,
            'categories'=> $categories
        ];
    }

   
}