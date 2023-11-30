<?php

namespace App\Controller\Website;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends AbstractController
{
   /**
     * @Route("/teams", name="web_teams")
     */
    public function index(): Response
    {
        return $this->render('website/team/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }

    /**
     * @Route("/teams/{id}", name="web_team")
     */
    public function detail(): Response
    {
        return $this->render('website/team/detail.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }
}
