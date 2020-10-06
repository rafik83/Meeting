
export default class ParticipantListFilter {

    constructor(searchElement, participantList) {
        this.searchElement = searchElement;
        this.searchElement.addEventListener('keyup', this.onChange.bind(this));
        this.currentFilterText = this.searchElement.value;
        this.participantList = participantList;
        if (this.searchElement.value) {
            this.applyFilter(this.searchElement.value);
        }
    }

    onChange(event) {
        clearTimeout(this.timeoutId);
        this.timeoutId = setTimeout(() => this.applyFilter(event.target.value), 500);
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
