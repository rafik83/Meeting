/**
 * Program
 *
 * @param element
 * @constructor
 */
function Program(element) {
    this.element    = element;
    this.link       = this.element.querySelector('.title');
    this.open       = false;
    this.toggleOpen = this.toggleOpen.bind(this);

    if (this.element.classList.contains('has-details')) {
        this.link.addEventListener('click', this.toggleOpen);
    }
}

/**
 * Toggle element details
 *
 * @param {Event} event
 */
Program.prototype.toggleOpen = function(event) {
    event.preventDefault();
    this.element.classList.toggle('open');
};

module.exports = Program;
