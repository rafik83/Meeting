import { createModalManager } from "./ChatModalManager";
import { createNotificationHandler } from "./ChatNotificationHandler";

export default class MeetOnlineButtonHandler {
    constructor (container, userConnection) {
        const meetOnlineButtons = container.querySelectorAll('.user__meet[data-private-chat-url]');

        if (meetOnlineButtons) {
            const notificationHandler = createNotificationHandler(container);

            const modalChatManager = createModalManager(container, userConnection, notificationHandler);
            meetOnlineButtons.forEach(
                meetButtonNode => meetButtonNode.addEventListener('click', () => modalChatManager.open(meetButtonNode))
            );
        }
    }
}
