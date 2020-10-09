import {EventSourcePolyfill} from "event-source-polyfill";

class ParticipantList {
    constructor(target, notificationProviderUrl, topic, notificationSubscriberKey, userCurrentId) {
        this.target = target;
        this.notificationProviderUrl = notificationProviderUrl;
        this.topic = topic;
        this.notificationSubscriberKey = notificationSubscriberKey;
        this.userCurrentId = userCurrentId;
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

                if (parseInt(this.userCurrentId) === payload.userId) {
                    return;
                }

                const matchedUserTR = this.target.querySelectorAll('[data-participant-user-id="'+ payload.userId +'"]');
                if (matchedUserTR.length > 0) {
                    return;
                }

                const userCompany = payload.userCompany || ' ';
                const tbody = document.getElementById('ParticipantList');
                const tr = document.createElement('tr');
                tr.setAttribute('data-participant-user-id', payload.userId);
                const td = document.createElement('td');
                const img = document.createElement('img');
                const p = document.createElement('p');

                tbody.insertBefore(tr, tbody.firstChild);
                tr.appendChild(td);
                td.appendChild(img);
                td.appendChild(p);

                img.setAttribute('src', payload.userAvatar);
                p.innerHTML = payload.userFirstName+' '+ payload.userLastName+' - '+ userCompany + ' - <em>'+ payload.userPosition+'</em>';

            }
        }
    }
}

export default ParticipantList;
