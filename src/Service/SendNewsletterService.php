<?php
namespace App\Service;

use App\Entity\Article;
use App\Entity\Newsletter\User;
use App\Entity\Newsletter\Newsletter;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use App\Controller\Website\NewslettersController;
use Symfony\Component\Config\Definition\Exception\Exception;


class SendNewsletterService
{
    /** @var MailerInterface */
    private $mailer;
    
    /** @var Request */
    private $request;

    public function __construct(MailerInterface $mailer,Request $request)
    {
        $this->mailer = $mailer;
        $this->request = $request;
    }

    /**
     * @param Article | null $article
     */
    public function send(User $user, Newsletter $newsletter, Article $article=null,$homeLink=null):void
    {
        // sleep(5);
        // // throw new \Exception('Message non envoyé');
        // $request=Request::createFromGlobals();
        // $request=NewslettersController::getRequest();
        try {
            if($user){
                $_locale='fr';
                $email=(new TemplatedEmail())
                ->from('no-reply@quantum-togo.com')
                ->to( trim($user->getEmail()))
                ->subject('QUANTUM : NEWSLETTER')
                ->htmlTemplate('emails/newsletter.html.twig')
                ->context(compact('newsletter', 'user', 'article','homeLink'))
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