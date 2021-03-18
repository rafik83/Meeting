'use strict';
import axios from 'axios';
import Modal from "../Modal";

export default class MuteStream {
    constructor(streamElement, userId, muteUrl) {
        this.streamElement = streamElement;
        this.userId = userId;
        this.muteUrl = muteUrl;
        this.isMute = false;
    }

    init() {
        this.muteButton = document.createElement('div')
        this.muteButton.classList.add('visio-user-rollover');
        this.muteButton.innerHTML = '<button class="visio-user-muteSpeaker btn"><i class="icon-Conference icon-center"></i></button>';
        this.streamElement.appendChild(this.muteButton);
        this.pictoAudio =  this.muteButton.querySelector('i');
        this.muteSpeaker = this.muteButton.querySelector('.visio-user-muteSpeaker');
        this.nameSpeaker = this.streamElement.querySelector('.visio-user-name');

        this.muteStreamConfirmModalElement = document.querySelector('[data-modal-mute-stream]');
        this.muteStreamButtonConfirm = this.muteStreamConfirmModalElement.querySelector('[data-modal-confirm]')
        this.muteStreamConfirmModal = new Modal();
        this.muteStreamConfirmModal.init(this.muteStreamConfirmModalElement);

        this.accessDeniedErrorMessage = this.streamElement.getAttribute(
            'data-user-denied-media-access'
        );

        this.muteButton.addEventListener('click', this.modal.bind(this));
    }

    disableButton() {
        this.muteSpeaker.classList.add('btn-off');
        this.streamElement.classList.add('muted');
        this.isMute = true;
        this.pictoAudio.classList.add('icon-Conference-off');
        this.nameSpeaker.classList.add('padding-left');
    }

    enableButton() {
        this.muteSpeaker.classList.remove('btn-off');
        this.streamElement.classList.remove('muted');
        this.isMute = false;
        this.pictoAudio.classList.remove('icon-Conference-off');
        this.nameSpeaker.classList.remove('padding-left');
    }

    modal() {
        if (this.isMute) {
            return;
        }

        this.muteStreamConfirmModal.show();

        const clonedButton = this.muteStreamButtonConfirm.cloneNode(true);
        this.muteStreamButtonConfirm.parentNode.replaceChild(clonedButton, this.muteStreamButtonConfirm);
        this.muteStreamButtonConfirm = clonedButton;

        this.muteStreamButtonConfirm.addEventListener('click', ()=> {
            axios.post(this.muteUrl, {'userIdToMute': this.userId})
                .then((response) => {
                    if (response.data.status !== 'ok') {
                        this.showError('Mute failed');
                    }
                })
                .catch((response) => {
                    this.showError(response.responseJSON ? response.responseJSON.message : response.status);
                });
            this.muteStreamConfirmModal.hide();
        });
    }

    showError(error) {
        switch (error.name) {
            case 'OT_USER_MEDIA_ACCESS_DENIED':
                alert(this.accessDeniedErrorMessage);
                break;
            default:
                alert('There was an error: ' + (error.name ? error.name : error) + (error.message ? (', ' + error.message) : ''));
                break;
        }
    };

}
