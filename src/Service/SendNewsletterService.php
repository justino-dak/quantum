<?php
namespace App\Service;

use App\Entity\Article;
use App\Entity\Newsletter\User;
use App\Entity\Newsletter\Newsletter;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;


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
    public function send(User $user, Newsletter $newsletter, Article $article=null):void
    {
        // sleep(5);
        // // throw new \Exception('Message non envoyé');
        try {
            if($user){
                $_locale='fr';
                $email=(new TemplatedEmail())
                ->from('no-reply@quantum.com')
                ->to( trim($user->getEmail()))
                ->subject('QUANTUM : NEWSLETTER')
                ->htmlTemplate('emails/newsletter.html.twig')
                ->context(compact('newsletter', 'user', 'article'))
            ;
            $this->mailer->send($email);  
            $newsletter->setIsSent(true);                       
            }else {
                throw new Exception("User can not be null", 1);
            }

        } catch (\Throwable $th) {
            throw $th;
        }

    }
}