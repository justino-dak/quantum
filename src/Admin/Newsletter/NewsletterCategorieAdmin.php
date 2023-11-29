<?php
namespace App\Admin\Newsletter;

use App\Entity\Bulletin;
use App\Admin\NavigationAdmin;
use App\Entity\Newsletter\User;
use App\Entity\Newsletter\Categorie;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;

class NewsletterCategorieAdmin extends Admin
{
    const NEWSLETTER_CATEGORIE_FORM_KEY = 'newsletterCategorie_details';
    const NEWSLETTER_CATEGORIE_LIST_VIEW = 'newsletterCategorie_list';
    const NEWSLETTER_CATEGORIE__ADD_FORM_VIEW = 'newsletterCategorie_add_form';
    const NEWSLETTER_CATEGORIE_EDIT_FORM_VIEW = 'newsletterCategorie_edit_form';

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

    public function configureViews(ViewCollection $viewCollection): void
    {
        $locales=$this->webspaceManager->getAllLocales();

        if ($this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_CATEGORIE_SECURITY_CONTEXT,PermissionTypes::VIEW)) 
        {
            $listToolBarAction=[];
            $formToolBarAction=[];

            if ($this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_CATEGORIE_SECURITY_CONTEXT,PermissionTypes::ADD)) 
            {
                $listToolBarAction[]=new ToolbarAction('sulu_admin.add');

            }

            if ($this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_CATEGORIE_SECURITY_CONTEXT,PermissionTypes::DELETE)) 
            {
                $listToolBarAction[]=new ToolbarAction('sulu_admin.delete');
                $formToolBarAction[]=new ToolbarAction('sulu_admin.delete');
            }

            if ($this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_CATEGORIE_SECURITY_CONTEXT,PermissionTypes::VIEW)) 
            {
                $listToolBarAction[]=new ToolbarAction('sulu_admin.export');
            }
            
            if ($this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_CATEGORIE_SECURITY_CONTEXT,PermissionTypes::EDIT)) 
            {
                $formToolBarAction[]=new ToolbarAction('sulu_admin.save');
            }

            $listView=$this->viewBuilderFactory->createListViewBuilder(static::NEWSLETTER_CATEGORIE_LIST_VIEW,'/newsletter/categories')
            ->setResourceKey(Categorie::RESOURCE_KEY)
            ->setListKey('newslettercategories')
            ->setTitle(' NEWSLETTER - CATEGORIES')
            ->addListAdapters(['table'])
            ->setAddView(static::NEWSLETTER_CATEGORIE__ADD_FORM_VIEW)
            ->setEditView(static::NEWSLETTER_CATEGORIE_EDIT_FORM_VIEW)
            ->addToolbarActions($listToolBarAction);
             $viewCollection->add($listView);


            if (
                $this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_CATEGORIE_SECURITY_CONTEXT,PermissionTypes::ADD)
                ||
                $this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_CATEGORIE_SECURITY_CONTEXT,PermissionTypes::EDIT)
                ) 
            {
                //add form 
                $addFormView=$this->viewBuilderFactory->createResourceTabViewBuilder(static::NEWSLETTER_CATEGORIE__ADD_FORM_VIEW,'/newsletter/categories/add')
                    ->setResourceKey(Categorie::RESOURCE_KEY)
                    ->setBackView(static::NEWSLETTER_CATEGORIE_LIST_VIEW);
                $viewCollection->add($addFormView);
                
                $addDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(static::NEWSLETTER_CATEGORIE__ADD_FORM_VIEW . '.details', '/details')
                    ->setResourceKey(Categorie::RESOURCE_KEY)
                    ->setFormKey(static::NEWSLETTER_CATEGORIE_FORM_KEY)
                    ->setTabTitle('sulu_admin.details')
                    ->setEditView(static::NEWSLETTER_CATEGORIE_EDIT_FORM_VIEW)
                    ->addToolbarActions([new ToolbarAction('sulu_admin.save'), new ToolbarAction('sulu_admin.delete')])
                    ->setParent(static::NEWSLETTER_CATEGORIE__ADD_FORM_VIEW);

                $viewCollection->add($addDetailsFormView);   

                // Edit form
                $editFormView=$this->viewBuilderFactory->createResourceTabViewBuilder(static::NEWSLETTER_CATEGORIE_EDIT_FORM_VIEW,'/newsletter/categories/:id')
                    ->setResourceKey(Categorie::RESOURCE_KEY)
                    ->setBackView(static::NEWSLETTER_CATEGORIE_LIST_VIEW);
                $viewCollection->add($editFormView);

                $editDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(static::NEWSLETTER_CATEGORIE_EDIT_FORM_VIEW . '.details', '/details')
                    ->setResourceKey(Categorie::RESOURCE_KEY)
                    ->setFormKey(static::NEWSLETTER_CATEGORIE_FORM_KEY)
                    ->setTabTitle('sulu_admin.details')
                    ->setEditView(static::NEWSLETTER_CATEGORIE_EDIT_FORM_VIEW)
                    ->addToolbarActions($formToolBarAction)
                    ->setParent(static::NEWSLETTER_CATEGORIE_EDIT_FORM_VIEW);

                $viewCollection->add($editDetailsFormView);   

            }
        }

    }

    public function getSecurityContexts()
    {
        return [
            Self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Newsletter' => [
                    NavigationAdmin::NEWSLETTER_CATEGORIE_SECURITY_CONTEXT=> [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        // PermissionTypes::DELETE,
                    ],
                ],
            ],
        ];
    }


}