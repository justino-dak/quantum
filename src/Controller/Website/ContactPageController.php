<?php

namespace App\Controller\Website;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Length;
use Sulu\Component\Content\Compat\StructureInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sulu\Bundle\WebsiteBundle\Controller\DefaultController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactPageController extends DefaultController
{
    protected function getAttributes($attributes, StructureInterface $structure = null, $preview = false)
    {
        $attributes = parent::getAttributes($attributes, $structure, $preview);
        return $attributes;
    }

   
}