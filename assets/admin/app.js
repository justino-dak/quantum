// Add project specific javascript code and import of additional bundles here:

import {formToolbarActionRegistry} from 'sulu-admin-bundle/views';
import SendNewsletterToolbarAction from './components/formToolbarActions/SendNewsletterToolbarAction';

formToolbarActionRegistry.add('app.newsletter_notify', SendNewsletterToolbarAction);
