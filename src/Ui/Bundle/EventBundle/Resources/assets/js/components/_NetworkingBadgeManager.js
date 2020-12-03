
export const BADGE_TYPE = {
    GENERALCHAT : "general-chat",
    PRIVATECHAT : "private-chat",
    NETWORKING_BUTTON : "networking"
}

export class NetworkingBadgeManager {
    constructor() {
        this.chatMessageCountBadges = [];
    }

    getUnreadPrivateChatMessageStartingCount(node, badgeType) {
        const datasetName =
            badgeType === BADGE_TYPE.PRIVATECHAT
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

    createChatMessageCountBadge(destinationNode, badgeType) {
        const startingCount = this.getUnreadPrivateChatMessageStartingCount(
            destinationNode,
            badgeType
        );

        this.chatMessageCountBadges[badgeType] = document.createElement("span");

        this.chatMessageCountBadges[badgeType].textContent =
            startingCount >= 99 ? "99+" : startingCount;

        this.chatMessageCountBadges[badgeType].classList.add(
            "alert-notification"
        );

        if (startingCount === 0) {
            this.chatMessageCountBadges[badgeType].classList.add("hide");
        }

        destinationNode.appendChild(this.chatMessageCountBadges[badgeType]);
    }

    incrementChatMessageCounter(badgeType) {
        if (!this.chatMessageCountBadges[badgeType]) {
            return;
        }
        const currentPrivateChatMessageCount = parseInt(
            this.chatMessageCountBadges[badgeType].textContent
        );

        if (isNaN(currentPrivateChatMessageCount)) {
            return;
        }

        if (currentPrivateChatMessageCount === 0) {
            this.chatMessageCountBadges[badgeType].classList.remove("hide");
        }

        this.chatMessageCountBadges[badgeType].textContent =
            currentPrivateChatMessageCount + 1;
    }

    decreaseChatMessageCounter(value, badgeType) {
        const currentChatMessageCount = parseInt(
            this.chatMessageCountBadges[badgeType].textContent,
            10
        );

        if (isNaN(currentChatMessageCount)) {
            return;
        }

        if (currentChatMessageCount === 0) {
            this.chatMessageCountBadges[badgeType].classList.add("hide");
        }

        this.chatMessageCountBadges[badgeType].textContent =
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
