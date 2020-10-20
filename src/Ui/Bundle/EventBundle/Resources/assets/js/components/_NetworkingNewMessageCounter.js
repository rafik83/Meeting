class NetworkingNewMessageCounter {

    constructor(newMessageCountElements) {
        this.newMessageCountElements = newMessageCountElements
    }

    getNewMessageCount() {
        if (this.newMessageCountElements.length > 0) {
            return Array.from(this.newMessageCountElements).reduce((prev, current) => {
                return prev + parseInt(current.dataset.newMessagesCount, 10);
            }, 0);
        }

        return 0;
    }

    appendNewMessageBadge(networkingPageDataElements) {
        // We assume if one of the dataset is set to true, We are in the networking page
        const isOnNetworkingPage = networkingPageDataElements.length > 0 &&
            (networkingPageDataElements[0].dataset.isNetworkingPageActive == true);
        const newMessageCount = this.getNewMessageCount();
        if (!isOnNetworkingPage || newMessageCount == 0) {
            return;
        }

        const newMessageBadge = document.createElement("span");
        newMessageBadge.textContent = newMessageCount >= 99 ? "99+" : newMessageCount;
        newMessageBadge.classList.add('alert-notification');

        [].forEach.call(networkingPageDataElements, function (item) {
            const clone = newMessageBadge.cloneNode(true);
            item.appendChild(clone);
        });
    }
}
export default NetworkingNewMessageCounter;
