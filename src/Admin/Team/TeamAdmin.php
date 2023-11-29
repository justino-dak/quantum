<?php
namespace App\Admin\Team;

use App\Admin\NavigationAdmin;
use App\Controller\Admin\TeamController;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;

class TeamAdmin extends Admin
{
    const TEAM_FORM_KEY = 'team_details';
    const TEAM_LIST_VIEW = 'team_list';
    const TEAM_ADD_FORM_VIEW = 'team_add_form';
    const TEAM_EDIT_FORM_VIEW = 'team_edit_form';

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

        if ($this->securityChecker->hasPermission(NavigationAdmin::TEAM_SECURITY_CONTEXT,PermissionTypes::VIEW)) 
        {
            $listToolBarAction=[];
            $formToolBarAction=[];

            if ($this->securityChecker->hasPermission(NavigationAdmin::TEAM_SECURITY_CONTEXT,PermissionTypes::ADD)) 
            {
                $listToolBarAction[]=new ToolbarAction('sulu_admin.add');

            }

            if ($this->securityChecker->hasPermission(NavigationAdmin::TEAM_SECURITY_CONTEXT,PermissionTypes::DELETE)) 
            {
                $listToolBarAction[]=new ToolbarAction('sulu_admin.delete');
                $formToolBarAction[]=new ToolbarAction('sulu_admin.delete');
            }

            if ($this->securityChecker->hasPermission(NavigationAdmin::TEAM_SECURITY_CONTEXT,PermissionTypes::VIEW)) 
            {
                $listToolBarAction[]=new ToolbarAction('sulu_admin.export');
            }
            
            if ($this->securityChecker->hasPermission(NavigationAdmin::TEAM_SECURITY_CONTEXT,PermissionTypes::EDIT)) 
            {
                $formToolBarAction[]=new ToolbarAction('sulu_admin.save');
            }

            $listView=$this->viewBuilderFactory->createListViewBuilder(static::TEAM_LIST_VIEW,'/team')
            ->setResourceKey(TeamController::RESOURCE_KEY)
            ->setListKey(TeamController::LIST_KEY)
            ->setTitle('NOTRE EQUIPE')
            ->addListAdapters(['table'])
            ->setAddView(static::TEAM_ADD_FORM_VIEW)
            ->setEditView(static::TEAM_EDIT_FORM_VIEW)
            ->addToolbarActions($listToolBarAction);
            $viewCollection->add($listView);

            if (
                $this->securityChecker->hasPermission(NavigationAdmin::TEAM_SECURITY_CONTEXT,PermissionTypes::ADD)
                ||
                $this->securityChecker->hasPermission(NavigationAdmin::TEAM_SECURITY_CONTEXT,PermissionTypes::EDIT)
                ) 
            {
                //add form 
                $addFormView=$this->viewBuilderFactory->createResourceTabViewBuilder(static::TEAM_ADD_FORM_VIEW,'/team/add')
                    ->setResourceKey(TeamController::RESOURCE_KEY)
                    ->setBackView(static::TEAM_LIST_VIEW);
                $viewCollection->add($addFormView);
                
                $addDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(static::TEAM_ADD_FORM_VIEW . '.details', '/details')
                    ->setResourceKey(TeamController::RESOURCE_KEY)
                    ->setFormKey(static::TEAM_FORM_KEY)
                    ->setTabTitle('sulu_admin.details')
                    ->setEditView(static::TEAM_EDIT_FORM_VIEW)
                    ->addToolbarActions([new ToolbarAction('sulu_admin.save'), new ToolbarAction('sulu_admin.delete')])
                    ->setParent(static::TEAM_ADD_FORM_VIEW);

                $viewCollection->add($addDetailsFormView);   

                // Edit form
                $editFormView=$this->viewBuilderFactory->createResourceTabViewBuilder(static::TEAM_EDIT_FORM_VIEW,'/team/:id')
                    ->setResourceKey(TeamController::RESOURCE_KEY)
                    ->setBackView(static::TEAM_LIST_VIEW);
                $viewCollection->add($editFormView);

                $editDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(static::TEAM_EDIT_FORM_VIEW . '.details', '/details')
                    ->setResourceKey(TeamController::RESOURCE_KEY)
                    ->setFormKey(static::TEAM_FORM_KEY)
                    ->setTabTitle('sulu_admin.details')
                    ->setEditView(static::TEAM_EDIT_FORM_VIEW)
                    ->addToolbarActions($formToolBarAction)
                    ->setParent(static::TEAM_EDIT_FORM_VIEW);

                $viewCollection->add($editDetailsFormView);   

            }
        }


    }

    public function getSecurityContexts()
    {
        return [
            Self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Equipe' => [
                    NavigationAdmin::TEAM_SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                    ],
                ],
            ],
        ];
    }


}