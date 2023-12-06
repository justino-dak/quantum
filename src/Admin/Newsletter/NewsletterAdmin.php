<?php
namespace App\Admin\Newsletter;

use App\Entity\Bulletin;
use App\Admin\NavigationAdmin;
use App\Entity\Newsletter\User;
use App\Entity\Newsletter\Newsletter;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;

class NewsletterAdmin extends Admin
{
    const NEWSLETTER_FORM_KEY = 'newsletter_details';
    const NEWSLETTER_LIST_VIEW = 'newsletter_list';
    const NEWSLETTER__ADD_FORM_VIEW = 'newsletter_add_form';
    const NEWSLETTER_EDIT_FORM_VIEW = 'newsletter_edit_form';

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

        if ($this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_SECURITY_CONTEXT,PermissionTypes::VIEW)) 
        {
            $listToolBarAction=[];
            $formToolBarAction=[];

            if ($this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_SECURITY_CONTEXT,PermissionTypes::ADD)) 
            {
                $listToolBarAction[]=new ToolbarAction('sulu_admin.add');

            }

            if ($this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_SECURITY_CONTEXT,PermissionTypes::DELETE)) 
            {
                $listToolBarAction[]=new ToolbarAction('sulu_admin.delete');
                $formToolBarAction[]=new ToolbarAction('sulu_admin.delete');
            }

            if ($this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_SECURITY_CONTEXT,PermissionTypes::VIEW)) 
            {
                $listToolBarAction[]=new ToolbarAction('sulu_admin.export');
            }
            
            if ($this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_SECURITY_CONTEXT,PermissionTypes::EDIT)) 
            {
                $formToolBarAction[]=new ToolbarAction('sulu_admin.save');
                $formToolBarAction[]=new ToolbarAction('app.newsletter_notify', ['allow_overwrite' => true]);
            }

            $listView=$this->viewBuilderFactory->createListViewBuilder(static::NEWSLETTER_LIST_VIEW,'/newsletters')
            ->setResourceKey(Newsletter::RESOURCE_KEY)
            ->setListKey('newsletters')
            ->setTitle('LES NEWSLETTERS')
            ->addListAdapters(['table'])
            ->setAddView(static::NEWSLETTER__ADD_FORM_VIEW)
            ->setEditView(static::NEWSLETTER_EDIT_FORM_VIEW)
            ->addToolbarActions($listToolBarAction);
             $viewCollection->add($listView);


            if (
                $this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_SECURITY_CONTEXT,PermissionTypes::ADD)
                ||
                $this->securityChecker->hasPermission(NavigationAdmin::NEWSLETTER_SECURITY_CONTEXT,PermissionTypes::EDIT)
                ) 
            {
                //add form 
                $addFormView=$this->viewBuilderFactory->createResourceTabViewBuilder(static::NEWSLETTER__ADD_FORM_VIEW,'/newsletters/add')
                    ->setResourceKey(Newsletter::RESOURCE_KEY)
                    ->setBackView(static::NEWSLETTER_LIST_VIEW);
                $viewCollection->add($addFormView);
                
                $addDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(static::NEWSLETTER__ADD_FORM_VIEW . '.details', '/details')
                    ->setResourceKey(Newsletter::RESOURCE_KEY)
                    ->setFormKey(static::NEWSLETTER_FORM_KEY)
                    ->setTabTitle('sulu_admin.details')
                    ->setEditView(static::NEWSLETTER_EDIT_FORM_VIEW)
                    ->addToolbarActions([new ToolbarAction('sulu_admin.save'), new ToolbarAction('sulu_admin.delete')])
                    ->setParent(static::NEWSLETTER__ADD_FORM_VIEW);

                $viewCollection->add($addDetailsFormView);   

                // Edit form
                $editFormView=$this->viewBuilderFactory->createResourceTabViewBuilder(static::NEWSLETTER_EDIT_FORM_VIEW,'/newsletters/:id')
                    ->setResourceKey(Newsletter::RESOURCE_KEY)
                    ->setBackView(static::NEWSLETTER_LIST_VIEW);
                $viewCollection->add($editFormView);

                $editDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(static::NEWSLETTER_EDIT_FORM_VIEW . '.details', '/details')
                    ->setResourceKey(Newsletter::RESOURCE_KEY)
                    ->setFormKey(static::NEWSLETTER_FORM_KEY)
                    ->setTabTitle('sulu_admin.details')
                    ->setEditView(static::NEWSLETTER_EDIT_FORM_VIEW)
                    ->addToolbarActions($formToolBarAction)
                    ->setParent(static::NEWSLETTER_EDIT_FORM_VIEW);

                $viewCollection->add($editDetailsFormView);   

            }
        }

    }

    public function getSecurityContexts()
    {
        return [
            Self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Newsletter' => [
                    NavigationAdmin::NEWSLETTER_SECURITY_CONTEXT=> [
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