import {Controller} from "@hotwired/stimulus";

export default class extends Controller {
    static values = {
        route: String,
        modalRoute: String,
        modalName: String
    }

    request() {
        let request = new XMLHttpRequest();
        request.responseType = 'json';
        request.onreadystatechange = () => {
            if (request.readyState === 4 && request.status === 200) {
                document.body.classList.toggle('loading');
                this.dispatch('loaded',
                    {
                        detail: {
                            content: request.response,
                        }
                    })
            }
        }
        document.body.classList.toggle('loading');
        request.open('POST', this.routeValue);
        request.send();
    }

    modalRequest(event) {
        event.preventDefault();
        let form = document.forms[this.modalNameValue];
        let request = new XMLHttpRequest();
        request.responseType = 'json';
        request.onreadystatechange = () => {
            if(request.readyState === 4 && request.status === 200) {
                this.dispatch('modal-request-loaded');
            }
        }
        request.open('POST', this.modalRouteValue);
        request.send(new FormData(form));
    }
}