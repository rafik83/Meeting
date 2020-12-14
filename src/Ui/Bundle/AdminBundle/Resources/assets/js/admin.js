import '../../../../../../../assets/js/components/Polyfills';
import $ from 'jquery';
import Confirm from './components/_Confirm';
import CheckAllCheckbox from './components/_CheckAllCheckbox';
import LoadingButton from './components/_LoadingButton';
import TemplateBuilder from './components/template/_TemplateBuilder';
import FormTemplateBuilder from './components/template/_FormTemplateBuilder';
import PrintTemplateBuilder from './components/template/_PrintTemplateBuilder';
import Batch from './components/_Batch';
import Slots from './components/_Slots';
import SharedChoicesCollection from './components/_SharedChoicesCollection';
import SortableCollection from './components/_SortableCollection';
import Update from './components/_Update';
import UpdateAndReconcile from './components/_UpdateAndReconcile';
import PreventMultipleSubmit from './components/_PreventMultipleSubmit';
import AnchorFocuser from './components/_AnchorFocuser';
import DateTimePicker from '../../../../../../../assets/js/components/DateTimePicker';
import MessagingMessagePreview from './components/_MessagingMessagePreview';
import ParticipantVisio from './components/_ParticipantVisio';
import TipPreview from './components/_TipPreview';
import ToggleVisibility from './components/_ToggleVisibility';
import CommercialStatusSelect from './components/_CommercialStatusSelect';
import AttributableProductToggleHappening from './components/_AttributableProductToggleHappening';
import DuplicationSheetsModal from './components/_DuplicationSheetsModal';
import SelectPreviousNextMover from './components/_SelectPreviousNextMover';
import RadioGroupAjax from './components/_RadioGroupAjax';
import FilterBuilder from './components/_FilterBuilder';
import ButtonGroupDefaultStateChanger from './components/_ButtonGroupDefaultStateChanger';
import ParticipantPresence from './components/_ParticipantPresence';
import ShowModal from './components/_ShowModal';
import AssignAccommodationStay from './components/_AssignAccommodationStay';

import 'bootstrap';
import 'elao-form.js';
import 'select2';
import 'moment/locale/fr';
import 'moment/locale/en-gb';
import './vendor/bootstrap-duallistbox/_jquery.bootstrap-duallistbox';
import './zendesk/zendesk';

// Init function

function init(target, firstInit) {
    if (!firstInit && $('.tinymce', target).length) {
        tinymceInit();
    }

    $('[data-collection]', target).collection()
        .on('collection:added', function (event, item) { init(item.element.get(0)); })
        .on('collection:deleted', function (event, item) {
            // Refresh shared choices collection in sub collections
            $('[data-shared-choices-collection]').each(function (key, element) {
                var o = $(element).data('shared-choices-collection-object');
                if (o !== undefined) {
                    o.refresh();
                }
            });
        });
    $('[data-toggle="tooltip"]', target).tooltip();
    $('[data-toggle="popover"]', target).popover();

    $('.clear-on-hidden-modal', target)
        .on('show.bs.modal', function (e) {
            $(e.target).removeData('bs.modal').find('.modal-content').html($(e.target).data('placeholder'));
        })
        .on('hidden.bs.modal', function (e) {
            $(e.target).removeData('bs.modal').find('.modal-content').empty();
        })
    ;

    [].forEach.call(target.querySelectorAll('.select2'), function (element) {
        $(element).select2({
            language: {
                noResults: function () {
                    return $(element).data('no-results-label');
                }
            },
            allowClear: element.getAttribute('data-placeholder') !== null
        });
    });

    [].forEach.call(target.querySelectorAll('.template-builder-body .select2-builder'), function (element) {
        $(element).select2({
            language: {
                noResults: function () {
                    return $(element).data('no-results-label');
                }
            },
            allowClear: element.getAttribute('data-placeholder') !== null
        });
    });

    [].forEach.call($('[data-datatimepicker]'), function (element) {
        new DateTimePicker(element);
    });

    [].forEach.call(target.querySelectorAll('[data-confirm]'), function (element) { new Confirm(element); });
    [].forEach.call(target.querySelectorAll('[data-update]'), function (element) { new Update(element); });
    [].forEach.call(target.querySelectorAll('[data-update-and-reconcile]'), function (element) { new UpdateAndReconcile(element); });
    [].forEach.call(target.querySelectorAll('[data-check-all-checkbox]'), function (element) { new CheckAllCheckbox(element, element.getAttribute('data-check-all-checkbox')); });
    [].forEach.call(target.querySelectorAll('[data-template-builder]'), function (element) { new TemplateBuilder(element, 'sheet') });

    [].forEach.call(target.querySelectorAll('[data-registration-template-builder]'), function (element) {
        new FormTemplateBuilder(element, 'registration')
    });

    [].forEach.call(target.querySelectorAll('[data-form-template-builder]'), function (element) {
        new FormTemplateBuilder(element, 'form')
    });

    [].forEach.call(target.querySelectorAll('[data-print-template-builder]'), function (element) { new PrintTemplateBuilder(element) });
    [].forEach.call(target.querySelectorAll('[data-batch]'), function (element) { new Batch(element) });
    [].forEach.call(target.querySelectorAll('[data-slot]'), function (element) { new Slots(element) });
    [].forEach.call(target.querySelectorAll('[data-sortable-collection]'), function (element) {
        new SortableCollection(element, element.getAttribute('data-sortable-collection'));
    });

    [].forEach.call(target.querySelectorAll('[data-loading-link]'), function (element) {
        var loadingButton = new LoadingButton(element, element.getAttribute('data-loading-link'));
        element.addEventListener('click', function () { loadingButton.start(); });
    });

    // Disable click on <a href="#"></a>
    [].forEach.call(target.querySelectorAll('a[href="#"'), function (element) {
        element.addEventListener('click', function (event) { event.preventDefault(); });
    });

    // Disable click on active button
    [].forEach.call(target.querySelectorAll('button.active'), function  (element) {
        element.addEventListener('click', function (event) { event.preventDefault(); });
    });

    [].forEach.call(target.querySelectorAll('[data-shared-choices-collection]'), function (element) {
        $(element).data('shared-choices-collection-object', new SharedChoicesCollection(element, element.getAttribute('data-shared-choices-collection')));
    });

    // Prevent multiple submit on input type submit
    [].forEach.call(target.querySelectorAll('form'), function (element) { new PreventMultipleSubmit(element); });


    [].forEach.call(target.querySelectorAll('[data-dual-list-box]'), function (element) {
        var selectedListLabel = element.getAttribute('data-dual-list-box-selectedListLabel');
        var nonSelectedListLabel = element.getAttribute('data-dual-list-box-nonSelectedListLabel');

        $(element).bootstrapDualListbox({
            infoText: false,
            selectorMinimalHeight: 300,
            selectedListLabel: selectedListLabel,
            nonSelectedListLabel: nonSelectedListLabel
        });
    });

    [].forEach.call(target.querySelectorAll('select.commercial_status_selector'), function(element) {
        new CommercialStatusSelect(element);
    });

    [].forEach.call(target.querySelectorAll('[data-switch-to-tab]'), function (element) {
        new AnchorFocuser(element, location);
    });

    [].forEach.call(target.querySelectorAll('[data-message-preview]'), function (element) {
        new MessagingMessagePreview(element, target.querySelector('#message_preview_iframe'), target.querySelector('#no_preview_text'));
    });

    [].forEach.call(target.querySelectorAll('.form-participant-visio'), function (element) {
        new ParticipantVisio(element);
    });

    [].forEach.call(target.querySelectorAll('[data-preview-tip]'), function (element) {
        new TipPreview(element, target.querySelector('#tip_preview'), target.querySelector('#tip_pages'));
    });

    [].forEach.call(target.querySelectorAll('[data-toggle-visibility]'), function (element) {
        new ToggleVisibility(element, target);
    });

    [].forEach.call(target.querySelectorAll('[data-attributable-product-toggle-happening]'), function (element) {
        new AttributableProductToggleHappening(element, target);
    });

    [].forEach.call(target.querySelectorAll('.duplication-sheet'), function (element) {
        new DuplicationSheetsModal(element, target.querySelector('#duplication-sheet'));
    });

    [].forEach.call(target.querySelectorAll('[data-select-mover-form]'), function (element) {
        new SelectPreviousNextMover(element, element.querySelector('[data-select-mover]'));
    });

    [].forEach.call(target.querySelectorAll('[data-radio-group-ajax]'), function (element) {
        new RadioGroupAjax(element);
    });

    [].forEach.call(target.querySelectorAll('.btn-group[data-btn-group-default-state]'), function (element) {
        new ButtonGroupDefaultStateChanger(element);
    });

    [].forEach.call(target.querySelectorAll('.filter-form'), function () {
        new FilterBuilder(
            target.querySelector('#rules'),
            target.querySelector('#builder'),
            target.querySelector('#submit-rules')
        );
    });

    [].forEach.call(target.querySelectorAll('[data-participant-presence-endpoint]'), function (element) {
        new ParticipantPresence(element);
    });

    [].forEach.call(target.querySelectorAll('[data-show-modal'), function (element) {
        new ShowModal(element);
    });

    [].forEach.call(target.querySelectorAll('form[name="admin_assign_accommodation_type"]'), function (element) {
        new AssignAccommodationStay(element);
    });
}

// Call init function when element is added to DOM

document.addEventListener('dom.element.added', function (event) {
    init(event.detail.element);
});

// Init

init(document, true);
