class NetworkingNewMessageCounter {

    constructor(dataSource) {
        this.dataSource = dataSource
    }


    countNewMessageFromPrivateChatIcon() {
        if (this.dataSource.length > 0) {
            return Array.from(this.dataSource).reduce((prev, current) => {
                return prev + parseInt(current.dataset.newMessagesCount, 10);
            }, 0);
        }

        return 0;
    }

    appendNewMessageBadgeInHeaderSubmenu(destinationNodes) {
        const newMessageCount = this.countNewMessageFromPrivateChatIcon();
        if (newMessageCount === 0) {
            return;
        }

        const newMessageBadge = document.createElement("span");
        newMessageBadge.textContent = newMessageCount >= 99 ? "99+" : newMessageCount;
        newMessageBadge.classList.add('alert-notification');

        destinationNodes.forEach((node) => {
            const clone = newMessageBadge.cloneNode(true);
            node.appendChild(clone);
        });
    }
}
export default NetworkingNewMessageCounter;
