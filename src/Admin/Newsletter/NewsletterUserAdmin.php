<?php
namespace App\Admin\Newsletter;

use App\Entity\Bulletin;
use App\Admin\NavigationAdmin;
use App\Entity\Newsletter\User;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;

class NewsletterUserAdmin extends Admin
{
    const NEWSLETTER_USER_FORM_KEY = 'newsletterUser_details';
    const NEWSLETTER_USER_LIST_VIEW = 'newsletterUser_list';
    const NEWSLETTER_USER__ADD_FORM_VIEW = 'newsletterUser_add_form';
    const NEWSLETTER_USER_EDIT_FORM_VIEW = 'newsletterUser_edit_form';

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

        if ($this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_USER_SECURITY_CONTEXT,PermissionTypes::VIEW)) 
        {
            $listToolBarAction=[];
            $formToolBarAction=[];

            if ($this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_USER_SECURITY_CONTEXT,PermissionTypes::ADD)) 
            {
                // $listToolBarAction[]=new ToolbarAction('sulu_admin.add');

            }

            if ($this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_USER_SECURITY_CONTEXT,PermissionTypes::DELETE)) 
            {
                $listToolBarAction[]=new ToolbarAction('sulu_admin.delete');
                $formToolBarAction[]=new ToolbarAction('sulu_admin.delete');
            }

            if ($this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_USER_SECURITY_CONTEXT,PermissionTypes::VIEW)) 
            {
                $listToolBarAction[]=new ToolbarAction('sulu_admin.export');
            }
            
            if ($this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_USER_SECURITY_CONTEXT,PermissionTypes::EDIT)) 
            {
                $formToolBarAction[]=new ToolbarAction('sulu_admin.save');
            }

            $listView=$this->viewBuilderFactory->createListViewBuilder(static::NEWSLETTER_USER_LIST_VIEW,'/newsletter/users')
            ->setResourceKey(User::RESOURCE_KEY)
            ->setListKey('newsletterusers')
            ->setTitle('LES ABONNÉS À LA NEWSLETTER')
            ->addListAdapters(['table'])
            ->setAddView(static::NEWSLETTER_USER__ADD_FORM_VIEW)
            ->setEditView(static::NEWSLETTER_USER_EDIT_FORM_VIEW)
            ->addToolbarActions($listToolBarAction);
             $viewCollection->add($listView);


            // if (
            //     $this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_USER_SECURITY_CONTEXT,PermissionTypes::ADD)
            //     ||
            //     $this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_USER_SECURITY_CONTEXT,PermissionTypes::EDIT)
            //     ) 
            // {
            //     //add form 
            //     $addFormView=$this->viewBuilderFactory->createResourceTabViewBuilder(static::NEWSLETTER_USER__ADD_FORM_VIEW,'/newsletter/users/add')
            //         ->setResourceKey(User::RESOURCE_KEY)
            //         ->setBackView(static::NEWSLETTER_USER_LIST_VIEW);
            //     $viewCollection->add($addFormView);
                
            //     $addDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(static::NEWSLETTER_USER__ADD_FORM_VIEW . '.details', '/details')
            //         ->setResourceKey(User::RESOURCE_KEY)
            //         ->setFormKey(static::NEWSLETTER_USER_FORM_KEY)
            //         ->setTabTitle('sulu_admin.details')
            //         ->setEditView(static::NEWSLETTER_USER_EDIT_FORM_VIEW)
            //         ->addToolbarActions([new ToolbarAction('sulu_admin.save'), new ToolbarAction('sulu_admin.delete')])
            //         ->setParent(static::NEWSLETTER_USER__ADD_FORM_VIEW);

            //     $viewCollection->add($addDetailsFormView);   

            //     // Edit form
            //     $editFormView=$this->viewBuilderFactory->createResourceTabViewBuilder(static::NEWSLETTER_USER_EDIT_FORM_VIEW,'/newsletter/users/:id')
            //         ->setResourceKey(User::RESOURCE_KEY)
            //         ->setBackView(static::NEWSLETTER_USER_LIST_VIEW);
            //     $viewCollection->add($editFormView);

            //     $editDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(static::NEWSLETTER_USER_EDIT_FORM_VIEW . '.details', '/details')
            //         ->setResourceKey(User::RESOURCE_KEY)
            //         ->setFormKey(static::NEWSLETTER_USER_FORM_KEY)
            //         ->setTabTitle('sulu_admin.details')
            //         ->setEditView(static::NEWSLETTER_USER_EDIT_FORM_VIEW)
            //         ->addToolbarActions($formToolBarAction)
            //         ->setParent(static::NEWSLETTER_USER_EDIT_FORM_VIEW);

            //     $viewCollection->add($editDetailsFormView);   

            // }
        }

    }

    public function getSecurityContexts()
    {
        return [
            Self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Newsletter' => [
                    NavigationAdmin::NEWSLETTER_USER_SECURITY_CONTEXT=> [
                        PermissionTypes::VIEW,
                        // PermissionTypes::ADD,
                        // PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                    ],
                ],
            ],
        ];
    }


}