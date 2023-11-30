<?php

namespace App\Controller\Website;

use App\Repository\EspeceRepository;
use App\Repository\ArticleRepository;
use App\Repository\ServiceRepository;
use App\Repository\VarieteRepository;
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
     * @var ServiceRepository
    */
    private $serviceRepository;

    /**
     * @var ArticleRepository
    */
    private $articleRepository;

    /**
     * @var EspeceRepository
    */
    private $especeRepository;

    /**
     * @var VarieteRepository
    */
    private $varieteRepository;

    public function __construct(
        ServiceRepository $serviceRepository,
        ArticleRepository $articleRepository,
        EspeceRepository $especeRepository,
        VarieteRepository $varieteRepository
    ){
        $this->serviceRepository = $serviceRepository;
        $this->articleRepository = $articleRepository;
        $this->especeRepository = $especeRepository;
        $this->varieteRepository = $varieteRepository;

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
        $services=$this->serviceRepository->findAll();
        $articlesRecentes=$this->articleRepository->findBy([],['id'=>'DESC'],3);
        $aboutus=$this->articleRepository->findByTag('aboutus');
        $especesRecentes=$this->especeRepository->findBy([],['id'=>'DESC'],3);
        $varietesRecentes=$this->varieteRepository->findAllRand(3);


        return [
            'services'=>$services,
            'aboutus'=>(count($aboutus)> 0)?$aboutus[0]:null,
            'articlesRecentes'=>$articlesRecentes,
            'especesRecentes'=>$especesRecentes,
            'varietesRecentes'=>$varietesRecentes
        ];
    }

   
}