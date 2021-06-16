import {EventSourcePolyfill} from "event-source-polyfill";

export default class Subscriber {

    constructor(notificationProviderUrl) {
        this.notificationProviderUrl = notificationProviderUrl;
        this.subscribers = {};
    }

    addSubscriber(topic, subscriberKey, callback) {
        if (this.subscribers.hasOwnProperty(topic)) {
            return;
        }

        const url = new URL(this.notificationProviderUrl);
        url.searchParams.append('topic', topic);

        const eventSource = new EventSourcePolyfill(url, {
            headers: {
                'Authorization': `Bearer ${subscriberKey}`
            },
            heartbeatTimeout: 600000,
        });
        eventSource.onmessage = callback;
        this.subscribers[topic] = eventSource;
    }

    removeSubscriber(topic) {
        if (!this.subscribers.hasOwnProperty(topic)) {
            return;
        }
        this.subscribers[topic].close();
        delete this.subscribers[topic];
    }
}
