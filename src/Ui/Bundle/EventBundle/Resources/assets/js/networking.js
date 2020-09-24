import Chat from './components/_Chat';

function init(target) {

    [].forEach.call(target.querySelectorAll('[data-chat-networking]'), function (element) {
        const topicUrl =  element.getAttribute('data-topic');
        const chat = new Chat(element, topicUrl);
        chat.initChat();
    });
}

init(document);
