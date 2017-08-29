var $ = require('jquery');

/**
 * @param element
 */
function HeaderMobile(element)
{
    this.element = element;
    this.menu    = element.querySelector('#' + element.dataset.anchor);

    this.element.addEventListener('click',  this.toggleMenu.bind(this));
    $('.header-close').on('click', this.toggleMenu.bind(this));
}

/**
 * Open and close header mobile
 */
HeaderMobile.prototype.toggleMenu = function () {
    document.body.classList.toggle('menu-mobile-opened');
    this.element.classList.toggle('open');
    this.menu.classList.toggle('open');
};

module.exports = HeaderMobile;
