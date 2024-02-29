import {action, observable} from 'mobx';
import React from 'react';
import { Dialog } from 'sulu-admin-bundle/components'; 
import {translate} from 'sulu-admin-bundle/utils';
import {AbstractListItemAction,List} from 'sulu-admin-bundle/views';

const baseUrl = window.location.origin;


export default class DownloadBackupListItemAction extends AbstractListItemAction {
    @observable visible= false;
    @observable open= false;
    @observable confirmLoading = false;
    @observable item;


    getNode(){
        return(
            <Dialog
                title={'Télécharger ?'}
                cancelText={'Annuler'}
                confirmText={'Oui'}
                onCancel={this.handlOnCancel}
                onConfirm={this.handleOnConfirm}
                children={'Cette opération télécharge la sauvegarde sélectionée. Voulez-vous vraiment continuer ?'}
                open={this.open}
                visible={this.visible}
            />
        );
    }
    
    
    getItemActionConfig(item) {
        const {disabled_ids: disabledIds = []} = this.options;

        return {
            icon: 'su-download',
            disabled: item ? disabledIds.includes(item['id']) : false,
            onClick: item ? () => this.handleClick(item) : undefined,
        };
    }

    @action handleOnConfirm=()=>{
        this.confirmLoading = true;
        try {
         
            window.location.assign(`${baseUrl}/admin/api/backups/${this.item.id}/download`);
                this.confirmLoading = false;
                this.open=false;  
                this.visible=false;                        
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

   @action handleClick = (item) => {
    this.item=item;
        this.visible=true;
        this.open=true;
    };
}