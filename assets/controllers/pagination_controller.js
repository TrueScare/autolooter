import {Controller} from "@hotwired/stimulus";

export default class extends Controller {
    static targets = [
        'paginationCurrent',
        'paginationPrevious',
        'paginationNext',
        'paginationSearchTerm',
        'paginationPageSize',
        'paginationOrder'
    ];

    pagePrevious(Event) {
        Event.preventDefault();
        this.dispatchReload(this.paginationPreviousTarget.value);
    }

    pageNext(Event) {
        Event.preventDefault();
        this.dispatchReload(this.paginationNextTarget.value);
    }

    update(Event) {
        Event.preventDefault();
        this.dispatchReload(1,'');
    }

    dispatchReload(page = null, searchTerm = null, order = null, pageSize = null) {
        console.log(searchTerm ?? this.paginationSearchTermTarget.value);
        this.dispatch('reload', {
            detail: {
                page: page ?? this.paginationCurrentTarget.value,
                searchTerm: (searchTerm.length > 0) ? searchTerm: this.paginationSearchTermTarget.value,
                order: order ?? this.paginationOrderTarget.value,
                pageSize: pageSize ?? this.paginationPageSizeTarget.value
            }
        });
    }
}