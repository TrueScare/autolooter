import {Controller} from "@hotwired/stimulus";

export default class extends Controller {
    static targets = [
        'pagination',
        'content'
    ];

    static values = {
        route: String
    }

    reload({detail: {page, searchTerm, order, pageSize}}) {
        this.contentTarget.innerHTML = 'loading...'

        let route = this.routeValue;
        if(page || searchTerm || order ||pageSize){
            route += '?';
        }

        if(page){
            route += '&page='+page;
        }
        if(searchTerm){
            route += '&searchTerm='+searchTerm;
        }
        if(order){
            route += '&order='+order;
        }
        if(pageSize){
            route += '&pageSize='+pageSize;
        }

        let request = new XMLHttpRequest();
        request.open('POST', route)

        request.responseType = 'json';
        request.onreadystatechange = () => {
            if (request.readyState === 4 && request.status === 200) {
                this.contentTarget.innerHTML = request.response;
            }
        }

        const body = JSON.stringify({
            page: page,
            searchTerm: searchTerm,
            order: order,
            pageSize: pageSize
        });

        request.send(body);
    }
}