<?php

namespace App\MessageHandler;

use App\Entity\Newsletter\User;
use App\Message\NewsletterUserMessage;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\NewsletterUserManagerService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class NewsletterUserMessageHandler implements MessageHandlerInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var NewsletterUserManagerService */
    private $userManager;
    public function __construct(EntityManagerInterface $em, NewsletterUserManagerService $userManager)
    {
        $this->em = $em;
        $this->userManager = $userManager;
    }

    public function __invoke(NewsletterUserMessage $message)
    {
        // do something with your message
        $user= $this->em->find(User::class,$message->getUserId());
        if ($user) {
            $this->userManager->deleteUnchekedUser($user);
        }

    }
}
