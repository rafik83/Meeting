import $ from 'jquery';

function Slots(element)
{
  this.element = $(element);
  this.fromParticipants = $(this.element.data('from-participants'));
  this.toParticipants = $(this.element.data('to-participants'));
  this.url = this.element.data('url');

  this.fromParticipants.find('input').on('change', this.onChange.bind(this));
  this.toParticipants.find('input').on('change', this.onChange.bind(this));

  this.onChange();
}

Slots.prototype.onChange = function ()
{
  var data = this.getData();
  $.get(this.url, data, function (response) {
    this.element.find('input').each(function (key, element) {
      var $element = $(element);
      if ($.inArray($element.data('id'), response) >= 0) {
        $element.removeAttr('disabled');
      } else {
        $element.attr('disabled', 'disabled');
      }
    }.bind(this));

  }.bind(this));
};

Slots.prototype.getData = function ()
{
  var ids = [];
  this.fromParticipants.find('input:checked').each(function (key, element) { ids.push($(element).val()); });
  this.toParticipants.find('input:checked').each(function (key, element) { ids.push($(element).val()); });

  return { 'participants': ids };
};

export default Slots;
