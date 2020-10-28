
export default class ParticipantListFilter {

    constructor(searchElement, participantList) {
        if (!searchElement) {
            return;
        }
        this.searchElement = searchElement;
        this.searchElement.addEventListener('keyup', this.onChange.bind(this));
        this.currentFilterText = this.searchElement.value;
        this.filter(participantList);
    }

    onChange(event) {
        clearTimeout(this.timeoutId);
        this.timeoutId = setTimeout(() => this.applyFilter(event.target.value), 500);
    }

    filter(participantList) {
        this.participantList = participantList;
        if (this.searchElement.value) {
            this.applyFilter(this.searchElement.value);
        }
    }

    applyFilter(filterText) {
        this.participantList.forEach((participantRow) => {
            if (participantRow.textContent.match(new RegExp(filterText, 'igm'))) {
                participantRow.classList.remove('hide');
            } else {
                participantRow.classList.add('hide');
            }
        });
    }
}
