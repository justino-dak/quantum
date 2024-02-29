<?php
namespace App\Admin\Backup;

use App\Admin\NavigationAdmin;
use App\Controller\Admin\TeamController;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use App\Controller\Admin\BackupController;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ListItemAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;

class BackupAdmin extends Admin
{
    const BACKUP_LIST_VIEW = 'backup_list';

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

        if ($this->securityChecker->hasPermission(NavigationAdmin::BACKUP_SECURITY_CONTEXT,PermissionTypes::VIEW)) 
        {
            $listToolBarAction=[];


            if ($this->securityChecker->hasPermission(NavigationAdmin::BACKUP_SECURITY_CONTEXT,PermissionTypes::DELETE)) 
            {
                $listToolBarAction[]=new ToolbarAction('sulu_admin.delete');
                $listToolBarAction[]= new ToolbarAction('app.generate_backup');
            }

            if ($this->securityChecker->hasPermission(NavigationAdmin::BACKUP_SECURITY_CONTEXT,PermissionTypes::VIEW)) 
            {
                $listToolBarAction[]=new ToolbarAction('sulu_admin.export');
            }
            


            $listView=$this->viewBuilderFactory->createListViewBuilder(static::BACKUP_LIST_VIEW,'/backups')
            ->setResourceKey(BackupController::RESOURCE_KEY)
            ->setListKey(BackupController::LIST_KEY)
            ->setTitle('LISTE DES SAUVEGARDES')
            ->addListAdapters(['table'])
            ->addToolbarActions($listToolBarAction)
            ->addItemActions([
                new ListItemAction('app.download_backup'),
                ]);
            $viewCollection->add($listView);

        }


    }

    public function getSecurityContexts()
    {
        return [
            Self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Sauvegarde' => [
                    NavigationAdmin::BACKUP_SECURITY_CONTEXT => [
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