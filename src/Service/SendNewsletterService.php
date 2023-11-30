<?php
namespace App\Service;

use App\Entity\Article;
use App\Entity\Newsletters\Users;
use App\Entity\Newsletters\Newsletters;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;


class SendNewsletterService
{
    /** @var MailerInterface */
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param Article | null $article
     */
    public function send(Users $user, Newsletters $newsletter, Article $article=null):void
    {
        // sleep(5);
        // // throw new \Exception('Message non envoyé');
        try {
            $_locale='fr';
            $email=(new TemplatedEmail())
            ->from('no-reply@universaquatic.com')
            ->to( trim($user->getEmail()))
            ->subject('UNIVERS AQUATIC : NEWSLETTER')
            ->htmlTemplate('emails/newsletter.html.twig')
            ->context(compact('newsletter', 'user', 'article'))
        ;
        $this->mailer->send($email);  
        $newsletter->setIsSent(true);            

        } catch (\Throwable $th) {
            throw $th;
        }

    }
}