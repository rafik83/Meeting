class NewMessageCounter {

    static getNewMessageCount() {
        return document.querySelectorAll("[data-new-messages-count]").reduce((prev, current) => {
            return prev + parseInt(current.dataset.newMessagCount);
        }, 0);
    }
}
export default NewMessageCounter;
