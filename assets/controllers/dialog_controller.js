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

    showMessage(event) {
        this.prepareContent(event.params.message);
    }

    showPage({detail: {content}}) {
        this.prepareContent(content);
    }

    close() {
        this.dialogContent.innerHTML = '';
        document.querySelector('body').style.overflow = 'auto';
        if (this.hasRouteValue) {
            this.dialogConfirm.removeEventListener('click', () => this.execRoute())
        } else {
            this.dialogConfirm.classList.toggle('d-none');
        }
        this.dialogCancel.removeEventListener('click', () => this.close());
        this.dialog.style.display = 'none';
        this.dialog.close();
    }

    prepareContent(content) {
        this.dialogContent.innerHTML = content;
        document.querySelector('body').style.overflow = 'hidden';
        if (this.hasRouteValue) {
            this.dialogConfirm.addEventListener('click', () => this.execRoute());
        } else {
            this.dialogConfirm.classList.toggle('d-none');
        }
        this.dialogCancel.addEventListener('click', () => this.close());
        this.dialog.style.display = 'block';
        this.dialog.showModal();
    }

    execRoute() {
        window.location.replace(this.routeValue);
    }
}