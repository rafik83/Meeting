class NetworkingNewMessageCounter {
    getUnreadPrivateChatMessageCount(node) {
        const privateChatMessageCount = parseInt(
            node.dataset.unreadPrivateChatMessageCount
        );

        if (isNaN(privateChatMessageCount)) {
            return 0;
        }

        return privateChatMessageCount;
    }

    appendNewMessageBadgeInPrivateChatButton(nodeList) {
        if (nodeList.length === 0) {
            return;
        }

        const destinationNode = nodeList[0];

        const startingCount = this.getUnreadPrivateChatMessageCount(destinationNode);

        const privateChatMessageCountBadge = document.createElement("span");
        privateChatMessageCountBadge.textContent =
            startingCount >= 99 ? "99+" : startingCount;

        privateChatMessageCountBadge.classList.add("alert-notification");

        destinationNode.appendChild(privateChatMessageCountBadge);
    }

    countNewMessageFromPrivateChatIcon(dataSource) {
        if (dataSource.length > 0) {
            return Array.from(dataSource).reduce((prev, current) => {
                return prev + parseInt(current.dataset.newMessagesCount, 10);
            }, 0);
        }

        return 0;
    }

    appendNewMessageBadgeInHeaderSubmenu(datasource, destinationNodes) {
        const newMessageCount = this.countNewMessageFromPrivateChatIcon(
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
