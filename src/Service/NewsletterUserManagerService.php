<?php
namespace App\Service;

use DateTime;
use App\Entity\Newsletters\Users;
use App\Repository\Newsletters\UsersRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Mailer\MailerInterface;


class NewsletterUserManagerService
{
    /**
     *  @var MailerInterface
     */
    private $mailer;

    /** 
     * @var UsersRepository  
     */
    private $usersRepository;

    public function __construct(
        MailerInterface $mailer,
        UsersRepository $usersRepository
        )
    {
        $this->mailer = $mailer;
        $this->usersRepository = $usersRepository;
    }

    /**
     * Verifie si l'utilisateur a confirmé son email sinon supprimer
     * @param Users | null $article
     */
    public function deleteUnchekedUser(Users $user):void
    {
        if ($user) {
            
            $this->usersRepository->remove($user->getId());

        }

    }

    /**
     *  @return  Collection|Users[] 
     */
    public function getUnchekedUsers()
    {
        $users=$this->usersRepository->findExpired();

        return $users;

    }    
}