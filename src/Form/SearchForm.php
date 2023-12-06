<?php

namespace App\Form;

use App\Data\SearchData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('q', TextType::class,[
                    'required'=>false,
                    'label'=>false
                ]
            )
            // ->add('limit', NumberType::class,[
            //     'required'=>false,
            //     'label'=>false
            // ]
            // )
            // ->add('submit', SubmitType::class,[
            //     'label'=>"Rechercher"
            // ]
            // )


        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchData::class,
            'methode'=>'GET',
            'csrf_protection'=>false
        ]);
    }
    public function getBlockPrefix()
    {
        return '';
    }
}
