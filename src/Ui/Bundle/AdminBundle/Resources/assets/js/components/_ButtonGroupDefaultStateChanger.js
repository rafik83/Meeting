'use strict';

function ButtonGroupDefaultStateChanger(element) {
    this.element = element;

    this.items = element.querySelectorAll('[data-btn-group-default-state-item]');

    this.element.addEventListener('click', function (event) {
        setTimeout(function(){
            this.items.forEach(function (item) {
                if (item.classList.contains('active')) {
                    item.classList.add(item.getAttribute('data-btn-group-default-state-item'));
                } else {
                    item.classList.remove('btn-success', 'btn-danger');
                }
            }.bind(this));
        }.bind(this), 1);
    }.bind(this))
}

module.exports = ButtonGroupDefaultStateChanger;
