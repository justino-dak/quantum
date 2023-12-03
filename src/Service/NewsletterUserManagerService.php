<?php
namespace App\Service;

use DateTime;
use App\Entity\Newsletter\User;
use Doctrine\Common\Collections\Collection;
use App\Repository\Newsletter\UserRepository;
use Symfony\Component\Mailer\MailerInterface;


class NewsletterUserManagerService
{
    /**
     *  @var MailerInterface
     */
    private $mailer;

    /** 
     * @var UserRepository  
     */
    private $userRepository;

    public function __construct(
        MailerInterface $mailer,
        UserRepository $userRepository
        )
    {
        $this->mailer = $mailer;
        $this->userRepository = $userRepository;
    }

    /**
     * Verifie si l'utilisateur a confirmé son email sinon supprimer
     * @param User | null $user
     */
    public function deleteUnchekedUser(User $user):void
    {
        if ($user) {
            
            $this->userRepository->remove($user->getId());

        }

    }

    /**
     *  @return  Collection|User[] 
     */
    public function getUnchekedUsers()
    {
        $users=$this->userRepository->findExpired();

        return $users;

    }    
}