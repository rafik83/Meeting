export const BADGE_TYPE = {
    GENERALCHAT: "data-unread-general-chat-message-count",
    PRIVATECHAT: "data-unread-private-chat-message-count",
    NETWORKING_BUTTON: "data-submenu='networking'",
    SINGLE_DISCUSSION_ITEM: "data-discussion-item-messages-count",
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

    createDiscussionItemCounterBadge(destinationNodes) {
        const newMessageBadge = document.createElement("span");

        destinationNodes.forEach((node, index) => {
            const clone = newMessageBadge.cloneNode(true);

            const userId = node.getAttribute("data-participant-user-id");

            const key = `${BADGE_TYPE.SINGLE_DISCUSSION_ITEM}-${userId}`;

            const startingCount = this.getUnreadeChatMessageStartingCount(
                node,
                BADGE_TYPE.SINGLE_DISCUSSION_ITEM
            );

            clone.classList.add("alert-notification");

            clone.textContent = startingCount >= 99 ? "99+" : startingCount;

            if (startingCount === 0) {
                clone.classList.add("hide");
            }

            this.chatMessageCountBadges[key] = clone;

            node.appendChild(clone);
        });
    }

    creatMenuBadgeCounters(destinationNodes, startingCount) {
        const newMessageBadge = document.createElement("span");

        newMessageBadge.textContent =
            startingCount >= 99 ? "99+" : startingCount;

        newMessageBadge.classList.add("alert-notification");

        if (startingCount === 0) {
            newMessageBadge.classList.add("hide");
        }

        destinationNodes.forEach((node, index) => {
            const clone = newMessageBadge.cloneNode(true);
            this.chatMessageCountBadges[
                `${BADGE_TYPE.NETWORKING_BUTTON}-${index}`
            ] = clone;
            node.appendChild(clone);
        });
    }

    incrementChatItemBadgeCounter(authorId) {
        const key = `${BADGE_TYPE.SINGLE_DISCUSSION_ITEM}-${authorId}`;
        this.incrementCounterFromBadgeType(key);
    }

    incrementCounterFromBadgeType(badgeType) {
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
    }

    getNetworkingMenuBadges() {
        return Object.keys(this.chatMessageCountBadges)
            .map((key) => {
                if (key.startsWith(BADGE_TYPE.NETWORKING_BUTTON)) {
                    return this.chatMessageCountBadges[key];
                }
            })
            .filter((item) => item);
    }

    incrementMenuBadgesCounter() {
        const incrementChatMessageCounterFromDomNode = (domNode) => {
            if (!domNode) {
                throw new Error(
                    `Cannot increment inexisting DOM node of type ${badgeType}`
                );
            }
            const currentPrivateChatMessageCount = parseInt(
                domNode.textContent
            );

            if (isNaN(currentPrivateChatMessageCount)) {
                return;
            }

            if (currentPrivateChatMessageCount === 0) {
                domNode.classList.remove("hide");
            }

            domNode.textContent = currentPrivateChatMessageCount + 1;
        };

        this.getNetworkingMenuBadges().forEach((item) => {
            incrementChatMessageCounterFromDomNode(item);
        });
    }

    getCurrentCounterValueForChatItem(authorId) {
        const key = `${BADGE_TYPE.SINGLE_DISCUSSION_ITEM}-${authorId}`;

        const node = this.chatMessageCountBadges[key];

        if (!node) {
            return 0;
        }

        const value = parseInt(node.textContent, 10);

        if (isNaN(value)) {
            return 0;
        }

        return value;
    }

    decreaseMenuBadgesCounter(valueToRemove) {
        const decrementValue = (domNode) => {
            if (!domNode) {
                throw new Error(
                    `Cannot increment inexisting DOM node of type ${badgeType}`
                );
            }
            const currentPrivateChatMessageCount = parseInt(
                domNode.textContent,
                10
            );

            if (isNaN(currentPrivateChatMessageCount)) {
                return;
            }

            const newValue = currentPrivateChatMessageCount - valueToRemove;

            if (newValue <= 0) {
                domNode.classList.add("hide");
            }

            domNode.textContent = newValue;
        };

        this.getNetworkingMenuBadges().forEach((item) => {
            decrementValue(item);
        });
    }

    decreaseChatMessageCounter(value, badgeType) {
        const node = this.chatMessageCountBadges[badgeType];

        if (!node) {
            return;
        }
        const currentChatMessageCount = parseInt(node.textContent, 10);

        if (isNaN(currentChatMessageCount)) {
            return;
        }

        const newValue = currentChatMessageCount - value;

        if (newValue === 0) {
            this.chatMessageCountBadges[badgeType].classList.add("hide");
        }

        this.chatMessageCountBadges[badgeType].textContent = newValue;
    }

    decreaseChatItemMessageCounter(authorId) {
        const key = `${BADGE_TYPE.SINGLE_DISCUSSION_ITEM}-${authorId}`;

        const valuetoRemove = this.getCurrentCounterValueForChatItem(authorId);

        this.decreaseChatMessageCounter(valuetoRemove, key);
    }
}
export default NetworkingBadgeManager;
