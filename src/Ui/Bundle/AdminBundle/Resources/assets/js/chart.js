import Bar from './chart/_bar.js';

function init(target) {
    [].forEach.call(target.querySelectorAll('[data-chart-bar]'), function (element) {
        new Bar(element)
    });
}

init(document);
