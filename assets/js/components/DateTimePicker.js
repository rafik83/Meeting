require('eonasdan-bootstrap-datetimepicker');
var $      = require('jquery'),
    moment = require('moment');

function DateTimePicker(element, customConfig)
{
    var customConfig = customConfig || null;
    this.element     = element;
    this.parentZone  = null;

    this.standardConfig = {
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
        format: 'DD/MM/YYYY HH:mm'
    };

    if (customConfig !== null) {
        this.standardConfig = Object.assign(this.standardConfig, customConfig);
    }

    var allowDates = this.element.getAttribute('data-allow-dates');
    var allowHours = this.element.getAttribute('data-allow-hours');

    // In case of allowDates or allowHours not set, change the format of the picker
    if ((allowDates === null && allowHours !== null)
        || (allowHours === null && allowDates !== null)
    ) {
        this.standardConfig = Object.assign(
            this.standardConfig,
            {
                format: allowHours === null ? 'DD/MM/YYYY' : 'HH:mm'
            }
        );
    }

    $(element).datetimepicker(this.standardConfig);

    if ($(this.element).hasClass("datetimepicker-range-element")) {
        this.bindRangeElement();
    }
}

DateTimePicker.prototype.bindRangeElement = function ()
{
    // Find the parent zone
    this.parentZone = $(this.element).closest('.datetimepicker-range');

    if (this.parentZone !== null) {
        this.rangeElement = $(this.parentZone).find(".datetimepicker-range-element");

        [].forEach.call(this.rangeElement, function (rangeElement) {
            if (!$(rangeElement).is(this.element)) {
                $(rangeElement).on("dp.change", function (event) {
                    if (typeof $(rangeElement).data("DateTimePicker") != 'undefined') {
                        var elementDate  = $(rangeElement).data("DateTimePicker").date();
                        this.currentDate = $(this.element).data("DateTimePicker").date();
                        var newDate = moment().year(elementDate.year()).month(elementDate.month()).date(elementDate.date()).minute(0).hour(0);

                        if (this.currentDate !== null) {
                            if (elementDate.year() != this.currentDate.year()
                                || elementDate.month() != this.currentDate.month()
                                || elementDate.date() != this.currentDate.date()
                            ) {
                                newDate.hour(this.currentDate.hour());
                                newDate.minute(this.currentDate.minute());

                                $(this.element).data("DateTimePicker").date(
                                    newDate
                                );
                            }
                        } else {
                            $(this.element).data("DateTimePicker").date(
                                newDate
                            );
                        }
                    }
                }.bind(this));
            }
        }.bind(this));
    }
};

module.exports = DateTimePicker;
