require('eonasdan-bootstrap-datetimepicker');
var $ = require('jquery');

function DateTimePicker(element)
{
    this.element        = element;

    this.displayHours = this.element.getAttribute('data-allow-hours') ? 'HH:mm' : '';
    this.displayDates = this.element.getAttribute('data-allow-dates') ? 'DD/MM/YYYY ' : '';
    this.format       = this.displayDates + this.displayHours;

    this.standardConfig = {
        locale: 'fr',
        sideBySide: true,
        allowInputToggle: true,
        icons: {
            time: 'glyphicon glyphicon-time',
            date: 'glyphicon glyphicon-calendar',
            up: 'glyphicon glyphicon-chevron-up',
            down: 'glyphicon glyphicon-chevron-down',
            previous: 'glyphicon glyphicon-chevron-left',
            next: 'glyphicon glyphicon-chevron-right',
            today: 'glyphicon glyphicon-screenshot',
            clear: 'glyphicon glyphicon-trash',
            close: 'glyphicon glyphicon-remove'
        },
        format: this.format
    };

    $(element).datetimepicker(this.standardConfig);
}

module.exports = DateTimePicker;
