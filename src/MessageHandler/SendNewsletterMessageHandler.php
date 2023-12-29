<?php

namespace App\MessageHandler;

use App\Entity\Article;
use App\Entity\Newsletter\User;
use App\Entity\Newsletter\Newsletter;
use App\Message\SendNewsletterMessage;
use App\Service\SendNewsletterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SendNewsletterMessageHandler implements MessageHandlerInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var SendNewsletterService */
    private $newsService;
    public function __construct(EntityManagerInterface $em, SendNewsletterService $newsService)
    {
        $this->em = $em;
        $this->newsService = $newsService;
    }

    public function __invoke(SendNewsletterMessage $message)
    {
        // do something with your message
        $user= $this->em->find(User::class,$message->getUserId());
        $newsletter=$this->em->find(Newsletter::class,$message->getNewsId());
        $article=null;
        if ($message->getArticleId()) {
            $article= $this->em->find(Article::class,$message->getArticleId());
        }

        $this->newsService->send($user,$newsletter,$article,$message->getLink());
    }
}
