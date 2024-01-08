import {Controller} from "@hotwired/stimulus";

export default class extends Controller {
    dialog;
    dialogContent;
    dialogConfirm;
    dialogCancel;

    static values = {
        route: String,
    }
    connect() {
        this.dialog = document.querySelector('#dialog');
        this.dialogContent = document.querySelector('#dialog_content');
        this.dialogConfirm = document.querySelector('#dialog_btn_confirm');
        this.dialogCancel = document.querySelector('#dialog_btn_cancel');
    }

    show(event) {
        this.dialogContent.innerHTML = event.params.message;
        document.querySelector('body').style.overflow = 'hidden';
        this.dialogConfirm.addEventListener('click', () => this.execRoute());
        this.dialogCancel.addEventListener('click', () => this.close());
        this.dialog.style.display = 'block';
        this.dialog.showModal();
    }
    close(){
        this.dialogContent.innerHTML = '';
        document.querySelector('body').style.overflow = 'auto';
        this.dialogConfirm.removeEventListener('click', () => this.execRoute())
        this.dialogCancel.removeEventListener('click', () => this.close());
        this.dialog.style.display = 'none';
        this.dialog.close();
    }

    execRoute() {
        window.location.replace(this.routeValue);
    }
}