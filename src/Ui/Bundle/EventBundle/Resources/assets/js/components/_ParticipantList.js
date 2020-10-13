class ParticipantList {
    constructor(target, userCurrentId) {
        this.target = target;
        this.userCurrentId = userCurrentId;
    }

    addNewuser(payload, callback) {
        if (parseInt(this.userCurrentId) === payload.userId) {
            return;
        }

        const matchedUserTR = this.target.querySelectorAll('[data-participant-user-id="'+ payload.userId +'"]');
        if (matchedUserTR.length > 0) {
            return;
        }

        const userCompany = payload.userCompany || ' ';
        const tbody = this.target.querySelector('tbody');
        const tr = document.createElement('tr');
        tr.setAttribute('data-participant-user-id', payload.userId);
        const td = document.createElement('td');
        const img = document.createElement('img');
        const user = document.createElement('p');
        const position = document.createElement('em');

        const url = new URL(this.target.getAttribute('data-private-chat-url'), document.URL);
        url.searchParams.append('toUser', payload.userId);

        tr.setAttribute('data-private-chat-url', url);

        tbody.insertBefore(tr, tbody.firstChild);
        tr.appendChild(td);

        img.setAttribute('src', payload.userAvatar);
        user.textContent = payload.userFirstName+' '+ payload.userLastName+' - '+ userCompany + ' - ';
        position.textContent = payload.userPosition;
        user.appendChild(position);
        td.appendChild(img);
        td.appendChild(user);

        callback(tr)
    }
}

export default ParticipantList;
