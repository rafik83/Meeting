import NotificationSubscriber from './_Subscriber';

class UserConnectionRegister {

    constructor() {
        this.listeners = [];
    }

    getRegistrationParameters() {
        const rawData = document.getElementById("user-context");
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
        console.log(registrationsParameters);

        const { topic, subscriberKey, providerUrl } = registrationsParameters;

        const subscriber = new NotificationSubscriber(providerUrl);
        subscriber.addSubscriber(topic, subscriberKey, (event) => {
            this.listeners.forEach((callback) => callback(event));
        });
    }

    addListener(callback) {
        this.listeners.push(callback);
    }
}
export default UserConnectionRegister;
