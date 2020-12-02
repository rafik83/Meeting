class NetworkingNewMessageCounter {
    constructor() {
        this.privateChatMessageCountBadgeNode = undefined;
    }

    getUnreadPrivateChatMessageStartingCount(node) {
        const privateChatMessageCount = parseInt(
            node.dataset.unreadPrivateChatMessageCount
        );

        if (isNaN(privateChatMessageCount)) {
            return 0;
        }

        return privateChatMessageCount;
    }

    createPrivateChatMessageCountBadge(destinationNode) {
        const startingCount = this.getUnreadPrivateChatMessageStartingCount(
            destinationNode
        );

        this.privateChatMessageCountBadgeNode = document.createElement("span");

        this.privateChatMessageCountBadgeNode.textContent =
            startingCount >= 99 ? "99+" : startingCount;

        this.privateChatMessageCountBadgeNode.classList.add("alert-notification");

        destinationNode.appendChild(
            this.privateChatMessageCountBadgeNode
        );
    }

    incrementPrivateChatMessageCounter() {
        if (!this.privateChatMessageCountBadgeNode) {
            return;
        }
        const currentPrivateChatMessageCount = parseInt(
            this.privateChatMessageCountBadgeNode.textContent
        );

        if (isNaN(currentPrivateChatMessageCount)) {
            return;
        }

        this.privateChatMessageCountBadgeNode.textContent =
            currentPrivateChatMessageCount + 1;
    }

    countNewMessageFromPrivateChatIcons(dataSource) {
        if (dataSource.length > 0) {
            return Array.from(dataSource).reduce((prev, current) => {
                return prev + parseInt(current.dataset.newMessagesCount, 10);
            }, 0);
        }

        return 0;
    }

    appendNewMessageBadgeInHeaderSubmenu(datasource, destinationNodes) {
        const newMessageCount = this.countNewMessageFromPrivateChatIcons(
            datasource
        );
        if (newMessageCount === 0) {
            return;
        }

        const newMessageBadge = document.createElement("span");
        newMessageBadge.textContent =
            newMessageCount >= 99 ? "99+" : newMessageCount;
        newMessageBadge.classList.add("alert-notification");

        destinationNodes.forEach((node) => {
            const clone = newMessageBadge.cloneNode(true);
            node.appendChild(clone);
        });
    }
}
export default NetworkingNewMessageCounter;
