import { EventSourcePolyfill } from "event-source-polyfill";
class UserConnectionRegister {

    constructor() { }

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

        const { topic, subscriberKey, providerUrl } = registrationsParameters

        const url = new URL(providerUrl);
        url.searchParams.append('topic', topic);

        new EventSourcePolyfill(url, {
            headers: {
                'Authorization': `Bearer ${subscriberKey}`
            }
        });
    }
}
export default UserConnectionRegister;
