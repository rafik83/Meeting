import NotificationSubscriber from './_Subscriber';

class UserConnectionRegister {

    constructor() {
        this.listeners = [];
    }

    getRegistrationParameters() {
        const rawData = document.getElementById('user-context');
        if (!rawData) {
            return null
        }

        return JSON.parse(rawData.innerHTML)
    }

    connect() {
        const registrationsParameters = this.getRegistrationParameters();

        if (!registrationsParameters) {
            return null
        }

        const { userTopic, subscriberKey, providerUrl } = registrationsParameters;

        const subscriber = new NotificationSubscriber(providerUrl);
        subscriber.addSubscriber(userTopic, subscriberKey, (notification) => {
            this.listeners.forEach((callback) => callback(notification));
        });
    }

    addListener(callback) {
        this.listeners.push(callback);
    }
}
export default UserConnectionRegister;
