var $ = require('jquery');

/**
 * @param element
 */
function HeaderMobile(element)
{
    this.element    = element;
    this.subElement = element.querySelector('.catalog-mobile-button');
    this.menu       = element.querySelector('#' + element.dataset.anchor);

    this.element.addEventListener('click',  this.toggleMenu.bind(this));
    $('.catalog-close').on('click', this.toggleMenu.bind(this));
}

/**
 * Open and close header mobile
 */
HeaderMobile.prototype.toggleMenu = function () {
    if (!this.subElement.classList.contains('disabled')) {
        document.body.classList.toggle('menu-mobile-opened');
        this.element.classList.toggle('open');
        this.menu.classList.toggle('open');
    } else {
        document.body.classList.remove('menu-mobile-opened');
        this.element.classList.add('open');
        this.menu.classList.remove('open');
    }
};

module.exports = HeaderMobile;
