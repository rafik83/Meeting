/* use strict */

let indicatorElement;

export function setExternalIndicatorElement(element) {
    indicatorElement = element;
};

export function updateExternalIndicatorElement(value) {
    if (value > 0) {
        indicatorElement.textContent = value;
        indicatorElement.classList.add('alert-notification');
    } else {
        indicatorElement.textContent = '';
        indicatorElement.classList.remove('alert-notification');
    }
};
