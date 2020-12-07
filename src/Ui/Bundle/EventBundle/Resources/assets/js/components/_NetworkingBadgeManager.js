export const BADGE_TYPE = {
    GENERALCHAT: "data-unread-general-chat-message-count",
    PRIVATECHAT: "data-unread-private-chat-message-count",
    NETWORKING_BUTTON: "data-submenu='networking'",
    SINGLE_DISCUSSION_ITEM: "data-unread-discussion-message-count",
};

export class NetworkingBadgeManager {
    constructor() {
        this.chatMessageCountBadges = [];
    }

    getUnreadeChatMessageStartingCount(DOMnode, badgeType) {
        const chatMessageCount = parseInt(DOMnode.getAttribute(badgeType), 10);

        if (isNaN(chatMessageCount)) {
            return 0;
        }

        return chatMessageCount;
    }

    createChatMessageCountBadge(destinationNode, badgeType, startingCount) {
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

    createMultipleMessageCountBadge(
        destinationNodes,
        badgeType,
        startingCount
    ) {
        const newMessageBadge = document.createElement("span");

        newMessageBadge.textContent =
            startingCount >= 99 ? "99+" : startingCount;

        newMessageBadge.classList.add("alert-notification");

        if (startingCount === 0) {
            newMessageBadge.classList.add("hide");
        }

        destinationNodes.forEach((node, index) => {
            const clone = newMessageBadge.cloneNode(true);
            this.chatMessageCountBadges[`${badgeType}-${index}`] = clone;
            node.appendChild(clone);
        });
    }

    createUnreadDiscussionCountBadge(destinationNode, startingCount) {
        this.chatMessageCountBadges[
            BADGE_TYPE.SINGLE_DISCUSSION_ITEM
        ] = destinationNode;

        destinationNode.textContent = startingCount;
    }

    incrementChatMessageCounter(badgeType) {
        if (!this.chatMessageCountBadges[badgeType]) {
            throw new Error(
                `Cannot increment inexisting DOM node of type ${badgeType}`
            );
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

            // console.log(this.chatMessageCountBadges)
    }

    incrementChatMessageCounterDOMNODE(domNode) {
        if (!domNode) {
            throw new Error(
                `Cannot increment inexisting DOM node of type ${badgeType}`
            );
        }
        const currentPrivateChatMessageCount = parseInt(domNode.textContent);

        if (isNaN(currentPrivateChatMessageCount)) {
            return;
        }

        if (currentPrivateChatMessageCount === 0) {
            domNode.classList.remove("hide");
        }

        domNode.textContent = currentPrivateChatMessageCount + 1;

       
    }

    incrementMultipleChatMessaeCounter(badgeType) {

        const nodeToIncrement = Object.keys(this.chatMessageCountBadges)
            .map((key) => {
                if (key.startsWith(badgeType)) {
                    return this.chatMessageCountBadges[key];
                }
            }).filter(item => item);

     

        nodeToIncrement.forEach((item) => {
            this.incrementChatMessageCounterDOMNODE(item);
        });
    }

    decreaseChatMessageCounter(value, badgeType) {
        const currentChatMessageCount = parseInt(
            this.chatMessageCountBadges[badgeType].textContent,
            10
        );

        if (isNaN(currentChatMessageCount)) {
            return;
        }

        const newValue = currentChatMessageCount - value;

        if (newValue === 0) {
            this.chatMessageCountBadges[badgeType].classList.add("hide");
        }

        this.chatMessageCountBadges[badgeType].textContent = newValue;
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
