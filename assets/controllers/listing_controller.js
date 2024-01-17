import {Controller} from "@hotwired/stimulus";

export default class extends Controller {
    static targets = [
        'pagination',
        'content',
        'loader'
    ];

    static values = {
        route: String
    }

    reload({detail: {page, searchTerm, order, pageSize}}) {
        this.element.classList.toggle('loading');

        let route = this.handleRoute(this.routeValue, page, searchTerm, order, pageSize);

        let request = new XMLHttpRequest();
        request.open('POST', route)

        request.responseType = 'json';
        request.timeout = 5000;
        request.ontimeout = () => {
            alert('rip');
            this.element.classList.toggle('loading');
        };
        request.onreadystatechange = () => {
            if (request.readyState === 4 && request.status === 200) {
                this.contentTarget.innerHTML = request.response;
                this.element.classList.toggle('loading')
            }
        };

        this.updateUrl(page, searchTerm, order, pageSize);

        request.send();
    }

    handleRoute(route, page, searchTerm, order, pageSize) {
        if (page || searchTerm || order || pageSize) {
            route += '?';
        }

        if (page) {
            route += '&page=' + page;
        }
        if (searchTerm) {
            route += '&searchTerm=' + searchTerm;
        }
        if (order) {
            route += '&order=' + order;
        }
        if (pageSize) {
            route += '&pageSize=' + pageSize;
        }
        return route;
    }

    updateUrl(page, searchTerm, order, pageSize){
        let route = this.getUrlWithoutParameters();
        if (page || searchTerm || order || pageSize) {
            route += '?';
        }

        let params = "";

        if (page) {
            params += '&page=' + page;
        }
        if (searchTerm) {
            params += '&searchTerm=' + searchTerm;
        }
        if (order) {
            params += '&order=' + order;
        }
        if (pageSize) {
            params += '&pageSize=' + pageSize;
        }

        params = params.replace(/^&/,'');

        window.history.replaceState(window.history.state,document.title, route + params);
    }

    getUrlWithoutParameters(){
        return location.protocol + '//' + location.host + location.pathname;
    }
}