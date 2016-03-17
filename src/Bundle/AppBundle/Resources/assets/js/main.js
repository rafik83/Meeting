var $        = require('jquery'),
    Confirm  = require('./components/_Confirm'),
    // CheckAll = require('./components/_CheckAll'),
    Update   = require('./components/_Update');

require('elao-form.js');

$(document).ready(function(){
    $('[data-collection]').collection();
    $('[data-confirm]').each(function (key, element) { new Confirm(element); });
    [].forEach.call(document.querySelectorAll('[data-update]'), function (element) { new Update(element); });
    // [].forEach.call(document.querySelectorAll('[data-check-all]'), function (element) { new CheckAll(element); });

    $('.selected-row').on('change', function() {
        $(this).parents('tr').toggleClass('warning');
        showOrHideBatchActions();
    });

    // select a row by clicking wherever on row
    $('table.selectable-row tr').on('click', function(e) {
        if (!$(e.target).is("td")) {
            return;
        }

        var selectedRow = $(this).find('.selected-row');
        if (selectedRow) {
            selectedRow.trigger('click');
        }
    });

    function CheckAll(element)
    {
        this.element  = $(element);
        this.selector = this.element.data('check-all');
        this.element.on('change', this.onChange.bind(this));
    }

    CheckAll.prototype.onChange = function ()
    {
        var checked = this.element.is(':checked');
        $(this.selector).prop('checked', checked);
        $(this.selector).parents('tr').toggleClass('warning', checked);

        showOrHideBatchActions();
    };

    function showOrHideBatchActions()
    {
        var rowSelectedCount = $('.selected-row:checked').length;

        if (rowSelectedCount) {
            $('.new-action').hide();
            $('.batch-actions').show();

            if (rowSelectedCount > 1) {
                var batch_action_label = $('[data-batch-action-plural-label]').val().replace('%count%', rowSelectedCount);
            } else {
                var batch_action_label = $('[data-batch-action-single-label]').val();
            }

            $('.batch-actions-label').text(batch_action_label);
        } else {
            $('.batch-actions').hide();
            $('.new-action').show();
        }
    }

    $('[data-check-all]').each(function (key, element) { new CheckAll(element); });

    $('.batch-actions').hide();
});
