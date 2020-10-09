import {EventSourcePolyfill} from "event-source-polyfill";

class ParticipantList {
    constructor(element, notificationProviderUrl, topic, notificationSubscriberKey) {
        this.element = element;

        this.notificationProviderUrl = notificationProviderUrl;
        this.topic = topic;
        this.notificationSubscriberKey = notificationSubscriberKey
    }

    init() {
        const url = new URL(this.notificationProviderUrl);
        url.searchParams.append('topic', this.topic);

        const eventSource = new EventSourcePolyfill(url, {
            headers: {
                'Authorization': `Bearer ${this.notificationSubscriberKey}`
            }
        });
        eventSource.onmessage = (event) => {
            const payload = JSON.parse(event.data);

            if (payload.action === 'user_connection') {
                alert(payload.userName)
            }
        }
    }
}

export default ParticipantList;
