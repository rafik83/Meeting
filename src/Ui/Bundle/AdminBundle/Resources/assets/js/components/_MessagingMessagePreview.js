/**
 * Messaging message preview: refresh the content of previewContainer when the user selects a message.
 *
 * Example:
 *
 * <input
 *    type="radio"
 *    id="select_message_message_2"
 *    name="select_message[message]"
 *    data-subject="My message subject"
 *    data-content="<p>My wonderful message content</p>"
 *    data-message-preview="1"
 *    value="2"
 * >
 *
 * @param {Element} element          The element that triggers the preview refresh
 * @param {Element} template         The template containing the refreshed HTML
 * @param {Element} previewContainer The container that must be refreshed
 *
 * @constructor
 */

function MessagingMessagePreview (element, template, previewContainer)
{
    this.element          = element;
    this.template         = template;
    this.previewContainer = previewContainer;

    this.element.addEventListener('click', this.updatePreview.bind(this));
}

MessagingMessagePreview.prototype.updatePreview = function() {
    var subject = this.element.getAttribute('data-subject');
    var content = this.element.getAttribute('data-content');

    previewHtml = this.template.innerHTML
        .replace('#subject#', subject)
        .replace('#content#', content)
    ;

    this.previewContainer.innerHTML = previewHtml;
};

module.exports = MessagingMessagePreview;
