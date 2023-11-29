<?php
namespace App\Admin;

use App\Admin\Team\TeamAdmin;
use App\Admin\Blog\ArticleAdmin;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use App\Admin\Newsletter\NewsletterAdmin;
use App\Admin\Newsletter\NewsletterUserAdmin;
use App\Admin\Newsletter\NewsletterCategorieAdmin;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;

class NavigationAdmin extends Admin
{



    const ARTICLE_SECURITY_CONTEXT='sulu.Blog.article';
    const SERVICE_SECURITY_CONTEXT='sulu.Services.service';

    const TEAM_SECURITY_CONTEXT='sulu.Equipe.equipe';


    const NEWSLETTER_SECURITY_CONTEXT='sulu.newsletter.Newsletter';
    const NEWSLETTER_USER_SECURITY_CONTEXT='sulu.newsletter.Abonné';
    const NEWSLETTER_CATEGORIE_SECURITY_CONTEXT='sulu.newsletter.Catégorie de Newsletter';

    
    /**
     * @var ViewBuilderFactoryInterface
     */
    private $viewBuilderFactory;

        /**
     * @var WebspaceManagerInterface
     */
    private $webspaceManager;

    /**
     * @var SecurityCheckerInterface
     */
    private $securityChecker;


    public function __construct(
        ViewBuilderFactoryInterface $viewBuilderFactory,
        WebspaceManagerInterface $webspaceManager,
        SecurityCheckerInterface $securityChecker
        )
    {
        $this->viewBuilderFactory = $viewBuilderFactory;
        $this->webspaceManager=$webspaceManager;
        $this->securityChecker = $securityChecker;
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {

        //-------------------------------------------------------------------------------
        //       MENU BLOG
        //--------------------------------------------------------------------------------
        $menuBlog= new NavigationItem('BLOG');
        $menuBlog->setIcon('su-newspaper');
        $menuBlog->setPosition(40);

            //les sous Memu

            if ($this->securityChecker->hasPermission(Static::ARTICLE_SECURITY_CONTEXT,PermissionTypes::VIEW))
            {
                $menuArtciles= new NavigationItem('Articles');
                $menuArtciles->setView(ArticleAdmin::ARTICLE_LIST_VIEW);
                $menuArtciles->setPosition(10);
                $menuBlog->addChild($menuArtciles);
            }           
        

        // Ajouter le menu "BLOG" à la navigation
        $navigationItemCollection->add($menuBlog);


        
        //-------------------------------------------------------------------------------
        //       MENU EQUIPE
        //--------------------------------------------------------------------------------
        $menuTeam= new NavigationItem('NOTRE EQUIPE');
        $menuTeam->setIcon('su-newspaper');
        $menuTeam->setPosition(40);

            //les sous Memu

            if ($this->securityChecker->hasPermission(Static::TEAM_SECURITY_CONTEXT,PermissionTypes::VIEW))
            {
                $menuEquipe= new NavigationItem('Equipe');
                $menuEquipe->setView(TeamAdmin::TEAM_LIST_VIEW);
                $menuEquipe->setPosition(10);
                $menuTeam->addChild($menuEquipe);
            }           
        

        // Ajouter le menu "EQUIPE" à la navigation
        $navigationItemCollection->add($menuTeam);       
        
        
        //-------------------------------------------------------------------------------
        //       Memu NEWSLETTER
        //--------------------------------------------------------------------------------
        
        $menuNewsletter= new NavigationItem('NEWSLETTER');
        $menuNewsletter->setIcon('su-newspaper');
        $menuNewsletter->setPosition(40);

            //les sous Memu
            if ($this->securityChecker->hasPermission(Static::NEWSLETTER_USER_SECURITY_CONTEXT,PermissionTypes::VIEW))
            {
                $menuUsers= new NavigationItem('Abonnés');
                $menuUsers->setView(NewsletterUserAdmin::NEWSLETTER_USER_LIST_VIEW);
                $menuUsers->setPosition(10);
                $menuNewsletter->addChild($menuUsers);
            }

            if ($this->securityChecker->hasPermission(Static::NEWSLETTER_SECURITY_CONTEXT,PermissionTypes::VIEW))
            {
                $menuNewsletters= new NavigationItem('Newsletters');
                $menuNewsletters->setView(NewsletterAdmin::NEWSLETTER_LIST_VIEW);
                $menuNewsletters->setPosition(10);
                $menuNewsletter->addChild($menuNewsletters);
            }

            if ($this->securityChecker->hasPermission(Static::NEWSLETTER_CATEGORIE_SECURITY_CONTEXT,PermissionTypes::VIEW))
            {
                $menuCategories= new NavigationItem('Categories');
                $menuCategories->setView(NewsletterCategorieAdmin::NEWSLETTER_CATEGORIE_LIST_VIEW);
                $menuCategories->setPosition(10);
                $menuNewsletter->addChild($menuCategories);
            }

        // Ajouter le menu "NEWSLETTER" à la navigation
        $navigationItemCollection->add($menuNewsletter);
        


    }

  
}