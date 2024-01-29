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
        request.open('PUT', this.routeValue);
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
        request.open('PUT', this.modalRouteValue);
        request.setRequestHeader("Content-Type", "application/json");
        request.send(this.convertFormToObject(form.formData));
        console.log(request);
    }

    convertFormToObject(formData){
        let object = {}
        formData.forEach(function(key, value){
            object[key] = value;
        });
        return JSON.stringify(object);
    }
}