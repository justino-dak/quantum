import {action, observable} from 'mobx';
import React from 'react';
import { Dialog  } from 'sulu-admin-bundle/components';
import Snackbar from 'sulu-admin-bundle/components/Snackbar'; 
import {translate} from 'sulu-admin-bundle/utils';
import {AbstractFormToolbarAction} from 'sulu-admin-bundle/views';


const baseUrl = window.location.origin;
const newsletterAlreadySent = false;


export default class SendNewsletterToolbarAction extends AbstractFormToolbarAction {

    @observable visible= false;
    @observable snackbarVisibilty= true;
    @observable open= false;
    @observable confirmLoading = false;
    @observable item;


    getNode(){
        return(
            <>
                <Dialog
                    title={'Newsletter ?'}
                    cancelText={'Annuler'}
                    confirmText={'Oui'}
                    onCancel={this.handlOnCancel}
                    onConfirm={this.handleOnConfirm}
                    children={'Cette opération envoie une notification à tous les abonnés du newletter . Voulez-vous vraiment continuer ?'}
                    open={this.open}
                    visible={this.visible}
                    confirmLoading={this.confirmLoading}
                />

            </>

        );
    }    


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

    @action handleOnConfirm=()=>{
        this.confirmLoading = true;
        try {

            const data=this.resourceFormStore.data
            const requestOptions = {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({id:data.id})
            };

            fetch(`${baseUrl}/admin/api/newsletters/${data.id}/notify`, requestOptions)
                .then( action(async response => {
                    // check for error response
                    if (!response.ok) {
                        // get error message from body or default to response status
                        const error = (data && data.message) || response.status;
                        return Promise.reject(error)
                    }

                    alert('La notification est envoyés avec succès');
                    this.open=false;  
                    this.visible=false;
                    this.confirmLoading = false;

                    // this.resourceFormStore.loading;           
                        
    
                    // alert('La notification est envoyés avec succès');
        
                }))
                .catch(error => {
                    console.error('Une erreur est survenue', error);
                    return Promise.reject(error);
                });
        } catch (error) {
            console.log(error);
            throw new Error(error);
        }
    }
    
    @action handlOnCancel=()=>{
        this.open=false;  
        this.visible=false;  
        this.confirmLoading = false;

    }

   @action handleClick = () => {
        this.visible=true;
        this.open=true;
    };

}
