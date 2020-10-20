'use strict';

import $ from "jquery";

function Chat(element) {
    this.chatContainer = element.querySelector('[data-chat-container]');
    this.addChatForm = element.querySelector('[data-chat-form]');
    this.addChatFormContent = this.addChatForm.querySelector('input[name="content"]');
    this.addChatFormAction = this.addChatForm.getAttribute('action');
    this.addChatFormSubmit = this.addChatForm.querySelector('button[type="submit"]');
    this.addChatFormList = this.chatContainer.querySelector('.chat-list');

    this.chatLoaded = false;

    this.addChatForm.addEventListener('submit', this.submitChat.bind(this));

    this.chatVoteMessage = {
        'like' : element.getAttribute('data-chat-vote-like'),
        'acclaim' : element.getAttribute('data-chat-vote-acclaim'),
        'heart' : element.getAttribute('data-chat-vote-heart'),
        'instructive' : element.getAttribute('data-chat-vote-instructive'),
        'happy' : element.getAttribute('data-chat-vote-happy')
    };

    this.chatUnVoteMessage = element.getAttribute('data-chat-unvote-message');
    this.chatVoteDisabledMessage = element.getAttribute('data-chat-vote-disabled-message');

    this.chatListeners = [];
}

Chat.prototype.submitChat = function (event) {
    event.preventDefault();
    const chatContent = this.addChatFormContent.value;

    if ('' === chatContent) {
        window.setTimeout(() => this.addChatFormSubmit.disabled = false, 100);
        return;
    }

    this.addChatFormContent.value = '';

    $.post(this.addChatFormAction, JSON.stringify({content: chatContent}), (response) => {
        this.addChatFormSubmit.disabled = false;

        if (response.status === 'ok') {
            this.reload();
            return;
        }

        this.addChatFormContent.value = content;
        this.showError('Message creation failed');
    })

        .fail(() => {
            this.addChatFormSubmit.disabled = false;
            this.addChatFormContent.value = content;
            this.showError('Message creation failed');
        });
}

Chat.prototype.showError = function (error) {
    alert('There was an error: ' + (error.name ? error.name : error) + (error.message ? (', ' + error.message) : ''));
};

Chat.prototype.initChat = function () {
    if (this.chatLoaded) {
        return;
    }

    const href = this.chatContainer.getAttribute('data-href');
    const voteChatHref = this.chatContainer.getAttribute('data-vote-chat-href');

    const $addChatFormList = $(this.addChatFormList);

    $.get(href, function (response) {
        this.removeChatListeners();
        $addChatFormList.empty();
        response.forEach((item) => {
            const rowEl = document.createElement('div');
            rowEl.id = `chat-message-${item.id}`;
            rowEl.classList.add('chat-row');

            const contentEl = rowEl.appendChild(document.createElement('div'));
            contentEl.classList.add('chat-content');

            const chatAside = document.createElement('small');
            chatAside.classList.add('pull-right', 'chat-aside');

            const emoticonBlock = document.createElement('div');

            const element = {
                '&#x1F44D;': 'like',
                '&#128079;': 'acclaim',
                '&#x2764;&#xFE0F': 'heart',
                '&#128161;': 'instructive',
                '&#128522;': 'happy'
            };

            const onLikedClicked = function (event) {
                const messageId = event.currentTarget.getAttribute('data-message-id');
                const messageType = event.currentTarget.getAttribute('data-message-type');
                const payload = { messageId, messageType };
                $.post(voteChatHref, JSON.stringify(payload), (response) => {
                    if (response.status !== 'ok') {
                        this.showError('Message vote failed');
                    }
                }, 'json');

                const chatMessageRow = document.getElementById(`chat-message-${payload.messageId}`);
                const voteCounts = chatMessageRow.querySelectorAll(`[data-message-type]`);

                if (event.currentTarget.classList.contains('btn-primary')) {
                    event.currentTarget.classList.remove('btn-primary', 'disabled');
                    event.currentTarget.classList.add('btn-gray');
                    const voteType = event.currentTarget.getAttribute('data-message-type');
                    event.currentTarget.title = this.chatVoteMessage[voteType];
                }  else {
                    voteCounts.forEach((voteCount) => {
                        voteCount.classList.add('btn-gray');
                        voteCount.classList.remove('btn-primary', 'disabled');
                        const voteType = voteCount.getAttribute('data-message-type');
                        voteCount.title = this.chatVoteMessage[voteType];
                    });
                    event.currentTarget.classList.add('btn-primary', 'disabled');
                    event.currentTarget.classList.remove('btn-gray');
                    event.currentTarget.title = this.chatUnVoteMessage;
                }

            }.bind(this);

            const chatCreatedAt = document.createElement('div');
            chatCreatedAt.textContent = item.formattedCreatedAt;
            chatAside.appendChild(chatCreatedAt);

            contentEl.appendChild(chatAside);
            contentEl.appendChild(document.createTextNode(item.content));

            const emoticonEl = rowEl.appendChild(document.createElement('div'));
            emoticonEl.classList.add('chat-emoticon');

            const authorEl = rowEl.appendChild(document.createElement('div'));
            authorEl.classList.add('chat-author');
            const authorNameEl = authorEl.appendChild(document.createElement('span'));
            authorNameEl.classList.add('chat-author-name');
            const authorNameTextEl = authorNameEl.appendChild(document.createElement('span'));
            authorNameTextEl.textContent = item.authorName;

            const avatarEl = authorEl.appendChild(document.createElement('span'));
            avatarEl.classList.add('chat-author-avatar');
            const imgEl = avatarEl.appendChild(document.createElement('img'));
            imgEl.setAttribute('src', item.avatar);


            for (let smileyCode in element) {
                const emoticonBtn = document.createElement('i');
                emoticonBtn.classList.add('glyphicon', 'btn', 'btn-xs');
                emoticonBtn.innerHTML = smileyCode;
                emoticonBtn.setAttribute('data-message-id', item.id);
                emoticonBtn.setAttribute('data-message-type', element[smileyCode]);

                const voteChat = document.createElement('span');
                voteChat.classList.add('chat-vote-count');

                if (item.votes[element[smileyCode]]) {
                    voteChat.textContent = item.votes[element[smileyCode]];
                }
                emoticonBtn.append(voteChat);

                if (!item.isAuthor) {
                    emoticonBtn.addEventListener('click', onLikedClicked);
                    this.chatListeners.push([emoticonBtn, onLikedClicked]);

                    if (item.selfVote === element[smileyCode]) {
                        emoticonBtn.classList.add('btn-primary', 'disabled');
                        emoticonBtn.title = this.chatUnVoteMessage;
                    } else {
                        emoticonBtn.classList.add('btn-gray');
                        emoticonBtn.title = this.chatVoteMessage[element[smileyCode]];
                    }

                } else {
                    emoticonBtn.classList.add('btn-gray', 'disabled');
                    emoticonBtn.title = this.chatVoteDisabledMessage;

                    rowEl.classList.add('chat-row-on');
                    contentEl.classList.add('chat-content-on');
                }
                emoticonBlock.appendChild(emoticonBtn);
                emoticonEl.appendChild(emoticonBlock);
            }

            if (item.sheetTitle) {
                const authorTitleEl = authorNameEl.appendChild(document.createElement('small'));
                authorTitleEl.textContent = [item.sheetTitle].filter((item) => !!item).join(', ');
                authorTitleEl.classList.add('chat-author-title');

                if (item.isAuthor) {
                    authorTitleEl.classList.add('chat-author-title-on');
                }
            }

            $addChatFormList[0].appendChild(rowEl);
        });

        this.addChatFormList.scrollTop = this.addChatFormList.scrollHeight;
        this.chatLoaded = true;
    }.bind(this))
        .fail(function (error) {
            console.error('Failed to load chat', error);
        }.bind(this));
};

Chat.prototype.updateVotes = function (messageId, votes) {
    const chatMessageRow = document.getElementById(`chat-message-${messageId}`);
    const voteCounts = chatMessageRow.querySelectorAll(`[data-message-type]`);
    voteCounts.forEach((voteCount) => {
        const voteType = voteCount.getAttribute('data-message-type');
        voteCount.querySelector('.chat-vote-count').textContent = votes[voteType] ? votes[voteType] : '';
    });
}
Chat.prototype.reload = function () {
    this.chatLoaded = false;
    this.initChat();
}

Chat.prototype.removeChatListeners = function () {
    this.chatListeners.forEach((item) => item[0].removeEventListener('click', item[1]));
    this.chatListeners = [];
}

export default Chat;
