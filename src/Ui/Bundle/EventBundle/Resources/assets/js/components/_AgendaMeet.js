var $ = require('jquery');

function AgendaMeet(element)
{
    this.$element         = $(element);
    this.$headerElement   = this.$element.children('header');
    this.agendaSlotHeight = 60;
    this.borderSlot       = 2;

    var meet   = this.$element.data();
    var agenda = this.$element.parents('.agenda').data();

    this.agenda = {
      beginhour: this.getTime(agenda.beginhour),
      halftime: this.halfTime(agenda.beginhour, agenda.endhour),
      slotduration: this.getDuration(agenda.slotduration)
    };

    this.meet = {
      beginhour: this.getTime(meet.beginhour),
      duration: this.getDuration(meet.duration),
      isAfternoon: this.isAfternoon(meet.beginhour, agenda.beginhour)
    };

    this.setPosition();

    this.$headerElement.on('click', this.toggleDetails.bind(this));
};

AgendaMeet.prototype.toggleDetails = function(event)
{
    event.preventDefault();

    this.$element.toggleClass('open');
};

AgendaMeet.prototype.getTime = function(value)
{
    var data = value.split(':');

    return new Date(null, null, null, data[0], data[1], data[2]);
};

AgendaMeet.prototype.getDuration = function(value, start)
{
    var start = start || '0:0:0';

    return this.diff(this.getTime(start), this.getTime(value));
};

AgendaMeet.prototype.diff = function(from, to)
{
    return Math.round((to - from) / 1000);
};

AgendaMeet.prototype.halfTime = function(beginHour, endHour)
{
    return this.getDuration(endHour, beginHour) / 2;
};

AgendaMeet.prototype.setPosition = function()
{
    var diffHour = this.diff(this.agenda.beginhour, this.meet.beginhour);
    var topPosition = (diffHour / this.agenda.slotduration) * this.agendaSlotHeight + this.borderSlot;
    var slotsHeight = this.meet.duration / this.agenda.slotduration;
    var height = (slotsHeight * this.agendaSlotHeight) - (this.borderSlot * 2) + 1;

    if (this.meet.isAfternoon) {
        var halfTimeSlotsHeight = this.agenda.halftime / this.agenda.slotduration;
        var halfTimeHeight = halfTimeSlotsHeight * this.agendaSlotHeight;

        topPosition -= halfTimeHeight;
    }

    this.$element.css('top', topPosition);
    this.$headerElement.css('height', height);
};

AgendaMeet.prototype.isAfternoon = function(meetHour, agendaHour) {
    var hours = this.getDuration(meetHour, agendaHour);

    return hours >= this.agenda.halftime;
};

module.exports = AgendaMeet;
