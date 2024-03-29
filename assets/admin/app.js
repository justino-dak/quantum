// Add project specific javascript code and import of additional bundles here:

import {formToolbarActionRegistry,
    listItemActionRegistry,
    listToolbarActionRegistry} from 'sulu-admin-bundle/views';
import SendNewsletterToolbarAction from './components/formToolbarActions/SendNewsletterToolbarAction';
import DownloadBackupListItemAction from './components/listItemActions/DownloadBackupListItemAction';
import BackupToolbarAction from "./components/listToolbarActions/BackupToolbarAction";

formToolbarActionRegistry.add('app.newsletter_notify', SendNewsletterToolbarAction);
listItemActionRegistry.add('app.download_backup', DownloadBackupListItemAction);
listToolbarActionRegistry.add('app.generate_backup', BackupToolbarAction);

(function (d) {

    d.title = "Quantum - Admin"

    // inject the global navigation
    // const script = d.createElement('script');
    // const style = d.createElement('link');
    // const head = d.querySelector('head');

    // style.setAttribute('href', './js/admin.js');
    // style.setAttribute('rel', 'stylesheet');

    // head.append(style);

    // script.async=true;
    // script.src='./js/admin.js';

    // head.append(script);
    // d.getElementsByClassName('su-sulu-logo')[0].setAttributeNS('className', 'su-user');
    const logo = d.getElementsByClassName("su-sulu-logo");
    const logoUser = d.getElementsByClassName("su-sulu").length;


    console.log('logoUser =====> ',logoUser);
    // logo.attr('aria-label',"su-user");
    // logo[0].innerHTML= 'ADMIN';
    // const element = d.querySelectorAll('[aria-label="su-sulu-logo"]');
    // console.log('element ===>',element);
})(document)

