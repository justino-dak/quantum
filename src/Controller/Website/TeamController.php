<?php

namespace App\Controller\Website;

use App\Repository\TeamRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TeamController extends AbstractController
{


    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(
        TeamRepository $teamRepository,
        PaginatorInterface $paginator
        )
    {
        $this->teamRepository = $teamRepository;
        $this->paginator = $paginator;
    }

   /**
     * @Route("/equipe", name="web_teams")
     */
    public function index(): Response
    {
        $teams=$this->teamRepository->findAll();
        return $this->render('website/team/index.html.twig', [
            'teams' => $teams,
        ]);
    }

    /**
     * @Route("/equipe/{id}", name="web_team")
     */
    public function detail(): Response
    {
        return $this->render('website/team/detail.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }
}
