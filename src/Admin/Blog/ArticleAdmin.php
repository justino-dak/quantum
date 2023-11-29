<?php
namespace App\Admin\Blog;

use App\Entity\Article;
use App\Admin\NavigationAdmin;
use App\Controller\Admin\ArticleController;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;

class ArticleAdmin extends Admin
{
    const ARTICLE_FORM_KEY = 'article_details';
    const ARTICLE_LIST_VIEW = 'article_list';
    const ARTICLE_ADD_FORM_VIEW = 'article_add_form';
    const ARTICLE_EDIT_FORM_VIEW = 'article_edit_form';

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

        if ($this->securityChecker->hasPermission(NavigationAdmin::ARTICLE_SECURITY_CONTEXT,PermissionTypes::VIEW)) 
        {
            $listToolBarAction=[];
            $formToolBarAction=[];

            if ($this->securityChecker->hasPermission(NavigationAdmin::ARTICLE_SECURITY_CONTEXT,PermissionTypes::ADD)) 
            {
                $listToolBarAction[]=new ToolbarAction('sulu_admin.add');

            }

            if ($this->securityChecker->hasPermission(NavigationAdmin::ARTICLE_SECURITY_CONTEXT,PermissionTypes::DELETE)) 
            {
                $listToolBarAction[]=new ToolbarAction('sulu_admin.delete');
                $formToolBarAction[]=new ToolbarAction('sulu_admin.delete');
            }

            if ($this->securityChecker->hasPermission(NavigationAdmin::ARTICLE_SECURITY_CONTEXT,PermissionTypes::VIEW)) 
            {
                $listToolBarAction[]=new ToolbarAction('sulu_admin.export');
            }
            
            if ($this->securityChecker->hasPermission(NavigationAdmin::ARTICLE_SECURITY_CONTEXT,PermissionTypes::EDIT)) 
            {
                $formToolBarAction[]=new ToolbarAction('sulu_admin.save');
            }

            $listView=$this->viewBuilderFactory->createListViewBuilder(static::ARTICLE_LIST_VIEW,'/articles')
            ->setResourceKey(ArticleController::RESOURCE_KEY)
            ->setListKey(ArticleController::LIST_KEY)
            ->setTitle('ARTICLES')
            ->addListAdapters(['table'])
            ->setAddView(static::ARTICLE_ADD_FORM_VIEW)
            ->setEditView(static::ARTICLE_EDIT_FORM_VIEW)
            ->addToolbarActions($listToolBarAction);
            $viewCollection->add($listView);

            if (
                $this->securityChecker->hasPermission(NavigationAdmin::ARTICLE_SECURITY_CONTEXT,PermissionTypes::ADD)
                ||
                $this->securityChecker->hasPermission(NavigationAdmin::ARTICLE_SECURITY_CONTEXT,PermissionTypes::EDIT)
                ) 
            {
                //add form 
                $addFormView=$this->viewBuilderFactory->createResourceTabViewBuilder(static::ARTICLE_ADD_FORM_VIEW,'/articles/add')
                    ->setResourceKey(ArticleController::RESOURCE_KEY)
                    ->setBackView(static::ARTICLE_LIST_VIEW);
                $viewCollection->add($addFormView);
                
                $addDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(static::ARTICLE_ADD_FORM_VIEW . '.details', '/details')
                    ->setResourceKey(ArticleController::RESOURCE_KEY)
                    ->setFormKey(static::ARTICLE_FORM_KEY)
                    ->setTabTitle('sulu_admin.details')
                    ->setEditView(static::ARTICLE_EDIT_FORM_VIEW)
                    ->addToolbarActions([new ToolbarAction('sulu_admin.save'), new ToolbarAction('sulu_admin.delete')])
                    ->setParent(static::ARTICLE_ADD_FORM_VIEW);

                $viewCollection->add($addDetailsFormView);   

                // Edit form
                $editFormView=$this->viewBuilderFactory->createResourceTabViewBuilder(static::ARTICLE_EDIT_FORM_VIEW,'/articles/:id')
                    ->setResourceKey(ArticleController::RESOURCE_KEY)
                    ->setBackView(static::ARTICLE_LIST_VIEW);
                $viewCollection->add($editFormView);

                $editDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(static::ARTICLE_EDIT_FORM_VIEW . '.details', '/details')
                    ->setResourceKey(ArticleController::RESOURCE_KEY)
                    ->setFormKey(static::ARTICLE_FORM_KEY)
                    ->setTabTitle('sulu_admin.details')
                    ->setEditView(static::ARTICLE_EDIT_FORM_VIEW)
                    ->addToolbarActions($formToolBarAction)
                    ->setParent(static::ARTICLE_EDIT_FORM_VIEW);

                $viewCollection->add($editDetailsFormView);   

            }
        }


    }

    public function getSecurityContexts()
    {
        return [
            Self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Blog' => [
                    NavigationAdmin::ARTICLE_SECURITY_CONTEXT => [
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