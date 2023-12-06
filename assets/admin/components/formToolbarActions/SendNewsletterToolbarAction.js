import {translate} from 'sulu-admin-bundle/utils';
import {AbstractFormToolbarAction} from 'sulu-admin-bundle/views';


export default class SendNewsletterToolbarAction extends AbstractFormToolbarAction {
    getToolbarItemConfig() {
        const {allow_overwrite: allowOverwrite = false} = this.options;

        return {
            type: 'button',
            label: translate('app.newsletter_notify'),
            icon: 'fa-paper-plane',
            disabled: !allowOverwrite && nameAlreadySet,
            onClick: this.handleClick,
        };
    }

    handleClick = () => {
        console.log('ResourceStore', this.ResourceFormStore);
        if (confirm(`Voullez-vous notifier ce newsletter aux abonnés ?`)) {
            // var acknowledge  = new Promise((resolve, reject) => {
            const notify  = new Promise((resolve, reject) => {
                    const requestOptions = {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({id:1})
                    };
                    fetch(`${baseUrl}/admin/api/newsletters/${1}/notify`, requestOptions)
                        .then(async response => {
                            const data = await response.json();  
                            // check for error response
                            if (!response.ok) {
                                // get error message from body or default to response status
                                const error = (data && data.message) || response.status;
                                return Promise.reject(error);
                            }
                
                        })
                        .catch(error => {
                            setErrorMessage(error);
                            console.error('Une erreur est survenue', error);
                        });
                    this.listStore.reload();

               
            });
            
        } 

    };
}
