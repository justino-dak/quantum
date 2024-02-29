import {action, observable} from 'mobx';
import React from 'react';
import { Dialog } from 'sulu-admin-bundle/components'; 
import {translate} from 'sulu-admin-bundle/utils';
import {AbstractListToolbarAction} from 'sulu-admin-bundle/views';

const baseUrl = window.location.origin;


export default class BackupToolbarAction extends AbstractListToolbarAction {
    @observable visible= false;
    @observable open= false;
    @observable confirmLoading = false;
    @observable item;


    getNode(){
        return(
            <Dialog
                title={'Backup  ?'}
                cancelText={'Annuler'}
                confirmText={'Oui'}
                onCancel={this.handlOnCancel}
                onConfirm={this.handleOnConfirm}
                children={'Cette opération génere une sauvegarde de la base de données et fichiers importés sur le serveur. Voulez-vous vraiment continuer ?'}
                open={this.open}
                visible={this.visible}
                confirmLoading={this.confirmLoading}
            />
        );
    }
    

    getToolbarItemConfig() {

        return {
            type: 'button',
            label: translate('Générer un backup'),
            icon: 'fa-file',
            onClick: this.handleClick,
        };
    }

  @action  handleOnConfirm=()=>{
        this.confirmLoading = true;
        // console.log("this",this);
        try {
            const requestOptions = {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' },
            };

            fetch(`${baseUrl}/admin/api/backup/generate`, requestOptions)
                .then( action(async response => {
                        // console.log(response);
                    if (!response.ok) {
                        // get error message from body or default to response status
                        const error = (data && data.message) || response.status;
                        return Promise.reject(error);
                    }

                    this.open=false;  
                    this.visible=false;
                    this.confirmLoading = false;

                    const data = await response.json();  

                    this.list.reload();
        
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