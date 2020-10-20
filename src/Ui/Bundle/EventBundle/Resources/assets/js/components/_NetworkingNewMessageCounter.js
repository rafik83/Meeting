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
        // I assume if one of the dataset is set to true, I am in the networking page
        const isOnNetworkingPage = networkingPageDataElements.length > 0 &&
            (networkingPageDataElements[0].dataset.isNetworkingPageActive == true);
        const newMessageCount = this.getNewMessageCount();
        if (!isOnNetworkingPage || newMessageCount == 0) {
            return;
        }

        const classAttribute = document.createAttribute("class");
        classAttribute.value = "alert-notification";
        const newMessageBadge = document.createElement("span");
        newMessageBadge.textContent = newMessageCount;
        newMessageBadge.setAttributeNode(classAttribute);

        [].forEach.call(networkingPageDataElements, function (item) {
            const clone = newMessageBadge.cloneNode(true);
            item.appendChild(clone);
        });
    }
}
export default NetworkingNewMessageCounter;
