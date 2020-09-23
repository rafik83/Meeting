'use strict';

function ButtonGroupDefaultStateChanger(element) {
    this.element = element;

    this.items = element.querySelectorAll('[data-btn-group-default-state-item]');
    this.itemsClasses = Array.prototype.map.call(this.items, function (item) {
        return item.getAttribute('data-btn-group-default-state-item');
    });

    this.element.addEventListener('click', function (event) {
        setTimeout(function(){
            this.items.forEach(function (item) {
                if (item.classList.contains('active')) {
                    item.classList.add(item.getAttribute('data-btn-group-default-state-item'));
                } else {
                    for (var itemClass = 0, length = this.itemsClasses.length; itemClass < length; itemClass++) {
                        item.classList.remove(this.itemsClasses[itemClass]);
                    }
                }
            }.bind(this));
        }.bind(this), 1);
    }.bind(this))
}

export default ButtonGroupDefaultStateChanger;
