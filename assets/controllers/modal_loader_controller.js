import {Controller} from "@hotwired/stimulus";

export default class extends Controller {
    static values = {
        route: String
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
        request.open('GET', this.routeValue);
        request.send();
    }
}