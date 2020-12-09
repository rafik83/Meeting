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

    getUnreadChatMessageStartingCount(element, badgeType) {
        const chatMessageCount = parseInt(element.getAttribute(badgeType), 10);

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

        destinationNodes.forEach((element, index) => {
            const clone = newMessageBadge.cloneNode(true);

            const userId = element.getAttribute("data-participant-user-id");

            const key = `${BADGE_TYPE.SINGLE_DISCUSSION_ITEM}-${userId}`;

            const startingCount = this.getUnreadChatMessageStartingCount(
                element,
                BADGE_TYPE.SINGLE_DISCUSSION_ITEM
            );

            clone.classList.add("alert-notification");

            clone.textContent = startingCount >= 99 ? "99+" : startingCount;

            if (startingCount === 0) {
                clone.classList.add("hide");
            }

            this.chatMessageCountBadges[key] = clone;

            element.appendChild(clone);
        });
    }

    createMenuBadgeCounters(destinationNodes, startingCount) {
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
        this.updateBadgeCounterValue(key, 1);
    }

    updateBadgeCounterValue(badgeType, value) {
        const element = this.chatMessageCountBadges[badgeType];

        if (!this.chatMessageCountBadges[badgeType]) {
            throw new Error(
                `Cannot update value of inexisting DOM node of type ${badgeType}`
            );
        }

        const currentPrivateChatMessageCount = parseInt(
            this.chatMessageCountBadges[badgeType].textContent,
            10
        );

        if (isNaN(currentPrivateChatMessageCount)) {
            return;
        }

        const newValue = currentPrivateChatMessageCount + value;

        if (newValue <= 0) {
            this.chatMessageCountBadges[badgeType].classList.add("hide");
        }

        if (newValue > 0) {
            this.chatMessageCountBadges[badgeType].classList.remove("hide");
        }

        this.chatMessageCountBadges[badgeType].textContent = newValue;
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
        this.getNetworkingMenuBadges().forEach((item, index) => {
            const key = `${BADGE_TYPE.NETWORKING_BUTTON}-${index}`;
            this.updateBadgeCounterValue(key, 1);
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
        this.getNetworkingMenuBadges().forEach((item, index) => {
            const key = `${BADGE_TYPE.NETWORKING_BUTTON}-${index}`;
            this.updateBadgeCounterValue(key, -Math.abs(valueToRemove));
        });
    }

    decreaseChatItemMessageCounter(authorId) {
        const key = `${BADGE_TYPE.SINGLE_DISCUSSION_ITEM}-${authorId}`;
        const valuetoRemove = this.getCurrentCounterValueForChatItem(authorId);
        this.updateBadgeCounterValue(key, -Math.abs(valuetoRemove));
    }
}
export default NetworkingBadgeManager;
