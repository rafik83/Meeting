/**
 * @param element
 *
 * @constructor
 *
 * This component is used to send an ajax request when a radio button of a radio group is clicked
 */
function RadioGroupAjax(element) {
    this.element = element;
    this.endpoint = element.getAttribute('data-radio-group-ajax');

    this.radios = element.querySelectorAll('input[type="radio"]');

    this.radios.forEach(function (radio) {
        radio.parentNode.addEventListener('click', function (event) {
            var xhr = new XMLHttpRequest();

            xhr.open('POST', this.endpoint);
            xhr.send(JSON.stringify({
                value: radio.value
            }));

        }.bind(this));
    }.bind(this));
}

export default RadioGroupAjax;
