
export const CHATKIND = {
    GENERALCHAT : "generalChat",
    PRIVATECHAT : "privateChat"
}

export class NetworkingBadgeManager {
    constructor() {
        this.chatMessageCountBadges = [];
    }

    getUnreadPrivateChatMessageStartingCount(node, chatKind) {
        const datasetName =
            chatKind === CHATKIND.PRIVATECHAT
                ? "unreadPrivateChatMessageCount"
                : "unreadGenralChatMessageCount";

        const chatMessageCount = parseInt(
            node.dataset[datasetName],
            10
        );

        if (isNaN(chatMessageCount)) {
            return 0;
        }

        return chatMessageCount;
    }

    createChatMessageCountBadge(destinationNode, chatKind) {
        const startingCount = this.getUnreadPrivateChatMessageStartingCount(
            destinationNode,
            chatKind
        );

        this.chatMessageCountBadges[chatKind] = document.createElement("span");

        this.chatMessageCountBadges[chatKind].textContent =
            startingCount >= 99 ? "99+" : startingCount;

        this.chatMessageCountBadges[chatKind].classList.add(
            "alert-notification"
        );

        if (startingCount === 0) {
            this.chatMessageCountBadges[chatKind].classList.add("hide");
        }

        destinationNode.appendChild(this.chatMessageCountBadges[chatKind]);
    }

    incrementChatMessageCounter(chatKind) {
        if (!this.chatMessageCountBadges[chatKind]) {
            return;
        }
        const currentPrivateChatMessageCount = parseInt(
            this.chatMessageCountBadges[chatKind].textContent
        );

        if (isNaN(currentPrivateChatMessageCount)) {
            return;
        }

        if (currentPrivateChatMessageCount === 0) {
            this.chatMessageCountBadges[chatKind].classList.remove("hide");
        }

        this.chatMessageCountBadges[chatKind].textContent =
            currentPrivateChatMessageCount + 1;
    }

    decreaseChatMessageCounter(value, chatKind) {
        const currentChatMessageCount = parseInt(
            this.chatMessageCountBadges[chatKind].textContent,
            10
        );

        if (isNaN(currentChatMessageCount)) {
            return;
        }

        if (currentChatMessageCount === 0) {
            this.chatMessageCountBadges[chatKind].classList.add("hide");
        }

        this.chatMessageCountBadges[chatKind].textContent =
            currentChatMessageCount - value;
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
export default NetworkingBadgeManager;
