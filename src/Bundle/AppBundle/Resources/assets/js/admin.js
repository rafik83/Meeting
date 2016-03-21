var $               = require('jquery'),
    Confirm         = require('./components/_Confirm'),
    CheckAll        = require('./components/_CheckAll'),
    TemplateBuilder = require('./components/_TemplateBuilder'),
    Batch           = require('./components/_Batch'),
    Update          = require('./components/_Update');

require('elao-form.js');

$(document).ready(function(){
    $('[data-collection]').collection();
    [].forEach.call(document.querySelectorAll('[data-confirm]'), function (element) { new Confirm(element); });
    [].forEach.call(document.querySelectorAll('[data-update]'), function (element) { new Update(element); });
    [].forEach.call(document.querySelectorAll('[data-check-all]'), function (element) { new CheckAll(element, element.getAttribute('data-check-all')); });
    [].forEach.call(document.querySelectorAll('[data-template-builder]'), function (element) { new TemplateBuilder(element) });
    [].forEach.call(document.querySelectorAll('[data-batch]'), function (element) { new Batch(element) });

    /// ----------


    /*
    $('tbody input[type="checkbox"]').on('change', function() {
        $(this).parents('tr').toggleClass('warning');
        showOrHideBatchActions();
    });

    // select a row by clicking wherever on row
    $('table.selectable-row tr').on('click', function(e) {
        if (!$(e.target).is("td") && !$(e.target).is("small")) {
            return;
        }

        var selectedRow = $(this).find('input[type="checkbox"]');
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
        var rowSelectedCount = $('tbody input[type="checkbox"]:checked').length;

        if (rowSelectedCount) {
            $('.new-action').hide();
            $('.batch-actions').show();

            if (rowSelectedCount > 1) {
                var batch_action_label = $('[data-batch-action-plural-label]').data('batchActionPluralLabel').replace('%count%', rowSelectedCount);
            } else {
                var batch_action_label = $('[data-batch-action-single-label]').data('batchActionSingleLabel');
            }

            $('.batch-actions-label').text(batch_action_label);
        } else {
            $('.batch-actions').hide();
            $('.new-action').show();
        }
    }

    $('[data-check-all]').each(function (key, element) { new CheckAll(element); });

    $('.batch-actions').hide();



    $('[data-filter-bind]').each(function() {
        var filterName = $(this).data('filterBind');

        var filterField = $('#filters [name="'+filterName+'"]');

        var options = $('#filters [name="'+filterName+'"] option');

        var optionsHtml = '';
        options.each(function() {
            var active = $(this).attr('selected') == 'selected' ? 'class="active"' : '';
            var label = $(this).text() != '' ? $(this).text() : 'All';
            optionsHtml += '<li '+active+'><a href="#" data-filter-value="'+$(this).val()+'">'+label+'</a></li>';
        });

        var columnHtml = '<div class="btn-group">';
        columnHtml += '<a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'
        columnHtml += $(this).text() + ' <span class="caret"></span>';
        columnHtml += '</a>';
        columnHtml += '<ul class="dropdown-menu" data-filter-name="' + filterName + '">';
        columnHtml += optionsHtml;
        columnHtml += '</ul></div>';

        $(this).html(columnHtml);

        $(filterField).parents('.form-group').hide();
    });

    $('.filterable').on('click', '[data-filter-value]', function() {
        var filterValue = $(this).data('filterValue');
        var filterName = $(this).parents('[data-filter-name]').data('filterName');

        var field = $('#filters [name="'+filterName+'"]');
        field.val(filterValue);
        field.parents('form').submit();
    });
    */

});
