class NetworkingNewMessageCounter {

    static getNewMessageCount() {

        const newMessageItems = document.querySelectorAll("[data-new-messages-count");

        if (newMessageItems.length > 0) {
            return Array.from(newMessageItems).reduce((prev, current) => {
                return prev + parseInt(current.dataset.newMessagesCount);
            }, 0);
        }

        return 0;
    }

    static appendNewMessageBadge() {

        const networkingPageDataElements = document.querySelectorAll("[data-is-networking-page-active]");
        // I assume if one of the dataset is set to true, we are in the networking page
        const isOnNetworkingPage = networkingPageDataElements.length > 0 &&
            (networkingPageDataElements[0].dataset.isNetworkingPageActive == true);
        const newMessageCount = this.getNewMessageCount();

        if (!isOnNetworkingPage || newMessageCount == 0) {
            return;
        }

        const classAttribute = document.createAttribute("class");
        classAttribute.value = "alert-notification";
        const newMessageBadge = document.createElement("span");
        newMessageBadge.innerHTML = newMessageCount;
        newMessageBadge.setAttributeNode(classAttribute);


        networkingPageDataElements.forEach(item => {
            const clone = newMessageBadge.cloneNode(true);
            item.appendChild(clone);
        });
    }
}
export default NetworkingNewMessageCounter;
