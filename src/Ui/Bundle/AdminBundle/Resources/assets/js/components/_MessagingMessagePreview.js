/**
 * Messaging message preview: refresh the content of the target iframe when the user selects a message.
 *
 * Example:
 *
 * <input
 *    type="radio"
 *    id="select_message_message_2"
 *    name="select_message[message]"
 *    data-message-preview="1"
 *    data-preview-url="/admin/fr/event/2/messaging/message/preview/2"
 *    value="2"
 * >
 */

/**
 * @param {Element} element              The element that triggers the preview refresh
 * @param {Element} targetIFrame         The iframe containing the refreshed HTML
 * @param {Element} defaultTextContainer The container that contains default text displayed when no message has been selected yet
 *
 * @constructor
 */
function MessagingMessagePreview (element, targetIFrame, defaultTextContainer)
{
    this.element              = element;
    this.targetIFrame         = targetIFrame;
    this.defaultTextContainer = defaultTextContainer;

    this.element.addEventListener('click', this.updatePreview.bind(this));
}

MessagingMessagePreview.prototype.updatePreview = function()
{

    this.defaultTextContainer.style.display = 'none';
    var url = this.element.getAttribute('data-preview-url');
    this.targetIFrame.setAttribute("src", url);
    this.targetIFrame.style.display = 'block';
};

export default MessagingMessagePreview;
