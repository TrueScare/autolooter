import {Controller} from "@hotwired/stimulus";

export default class extends Controller {
    static targets= [
        'paginationPrevious',
        'paginationNext',
        'paginationSearchTerm',
        'paginationPageSize',
        'paginationOrder'
    ];

    pagePrevious(){
        this.dispatchReload(this.paginationPreviousTarget.value);
    }
    pageNext(){
        this.dispatchReload(this.paginationNextTarget.value);
    }

    dispatchReload(page){
        this.dispatch('reload', {
            detail: {
                page: page,
                searchTerm:this.paginationSearchTermTarget.value,
                order:this.paginationOrderTarget.value,
                pageSize: this.paginationPageSizeTarget.value
            }
        });
    }
}