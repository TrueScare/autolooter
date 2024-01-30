import {Controller} from "@hotwired/stimulus";

export default class extends Controller {
    static values = {
        route: String
    }

    update(){
        let request = new XMLHttpRequest();
        request.onreadystatechange = () => {
            if (request.readyState === 4 && request.status === 200){
                this.element.innerHTML = JSON.parse(request.response);
            }
        }

        request.open('GET', this.routeValue);
        request.send();
    }
}