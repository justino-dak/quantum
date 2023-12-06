import {translate} from 'sulu-admin-bundle/utils';
import {AbstractFormToolbarAction} from 'sulu-admin-bundle/views';


const baseUrl = window.location.origin;
const newsletterAlreadySent = false;


export default class SendNewsletterToolbarAction extends AbstractFormToolbarAction {

    getToolbarItemConfig() {
        const {allow_overwrite: allowOverwrite = false} = this.options;

        return {
            type: 'button',
            label: translate('app.newsletter_notify'),
            icon: 'fa-paper-plane',
            // loading: true,
            // disabled: !allowOverwrite && newsletterAlreadySent,
            onClick: this.handleClick,
        };
    }

    handleClick = () => {
        console.log('this', this);
        const data=this.resourceFormStore.data
        if (confirm(`Voullez-vous notifier ce newsletter aux abonnés ?`)) {

            // this.loading;
            // var acknowledge  = new Promise((resolve, reject) => {
            const requestOptions = {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({id:data.id})
            };

            fetch(`${baseUrl}/admin/api/newsletters/${data.id}/notify`, requestOptions)
                .then(async response => {
                    const data = await response.json();  
                    // check for error response
                    if (!response.ok) {
                        // get error message from body or default to response status
                        const error = (data && data.message) || response.status;
                        return Promise.reject(error);
                    }

                    alert('La notification est envoyés avec succès');
        
                })
                .catch(error => {
                    console.error('Une erreur est survenue', error);
                    return Promise.reject(error);
                });
            this.resourceFormStore.loading;

               
            
        } 

    };
}
