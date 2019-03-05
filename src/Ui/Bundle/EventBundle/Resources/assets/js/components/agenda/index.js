var Availability = require('./availability/_Availability'),
    Agenda = require('./_Agenda'),
    AvailabilityStorage = require('./availability/_Storage'),
    Toggle = require('./../_Toggle');

var agendaAvailabilities = [];
var availabilityStorage = null;
var agendaContainer = null;

function init(target) {
    agendaContainer = target.querySelectorAll('.agenda-container')[0];

    initMain(target);
    initUI(target);
    initStorage(target);
    initTooltip();
}

function initTooltip() {
    var availableMessage = agendaContainer.getAttribute('data-slot-available');
    var unavailableMessage = agendaContainer.getAttribute('data-slot-unavailable');
    var imperativePresenceMessage = agendaContainer.getAttribute('data-slot-imperative-presence');

    Toggle.defaultLabels = {disabled: imperativePresenceMessage, yes: availableMessage, no: unavailableMessage};
}

function initMain(target) {
    agendaAvailabilities = [];

    [].forEach.call(target.querySelectorAll('.agenda'), function (element) {
        var agenda = new Agenda(element);
        var meetsLayoutNode = element.querySelectorAll('.meets')[0];
        var agendaHeadNode = element.querySelectorAll('.agenda-head')[0];
        var agendaAvailability = new Availability(agenda, meetsLayoutNode, agendaHeadNode);
        agendaAvailabilities.push(agendaAvailability);
    });
}

function initUI(target) {
    var enabled = false;
    var saveButtons = [];
    var manageButtons = [];
    var enableButtons = [];
    var disableButtons = [];

    function init() {
        manageButtons = target.querySelectorAll('.manageAvailabilityButton');
        enableButtons = target.querySelectorAll('.manageAvailabilityButton--enable');
        disableButtons = target.querySelectorAll('.manageAvailabilityButton--disable');

        saveButtons = target.querySelectorAll('.saveAvailabilityButton');

        [].forEach.call(manageButtons, function (element) {
            element.onclick = toggle;
        });

        [].forEach.call(saveButtons, function (element) {
            element.onclick = saveData;
        });

        hideSaveButton();
        hideDisableButton();
    }

    function saveData() {
        availabilityStorage.save();
    }

    function disable() {
        for (var agendaAvailability of agendaAvailabilities) {
            agendaAvailability.disable();
        }
        hideSaveButton();
        showEnableButton();
        hideDisableButton();
    }

    function enable() {
        for (var agendaAvailability of agendaAvailabilities) {
            agendaAvailability.enable();
        }
        showSaveButton();
        hideEnableButton();
        showDisableButton();
    }

    function toggle() {
        enabled = !enabled;
        if (enabled) {
            enable();
        } else {
            disable();
        }
    }

    function showDisableButton() {
        [].forEach.call(disableButtons, function (element) {
            element.style.display = null;
        });
    }

    function hideDisableButton() {
        [].forEach.call(disableButtons, function (element) {
            element.style.display = 'none';
        });
    }

    function showEnableButton() {
        [].forEach.call(enableButtons, function (element) {
            element.style.display = null;
        });
    }

    function hideEnableButton() {
        [].forEach.call(enableButtons, function (element) {
            element.style.display = 'none';
        });
    }

    function showSaveButton() {
        [].forEach.call(saveButtons, function (element) {
            element.style.display = null;
        });
    }

    function hideSaveButton() {
        [].forEach.call(saveButtons, function (element) {
            element.style.display = 'none';
        });
    }

    init();
}

function initStorage() {
    var eventUnavailabilitiesCreateUrl = agendaContainer.getAttribute('data-event-unavailabilities-create-url');
    var koOnSaveMessage = agendaContainer.getAttribute('data-event-availabilities-ko-message');
    var failOnSaveMessage = agendaContainer.getAttribute('data-event-availabilities-fail-message');
    var onOkMessage = agendaContainer.getAttribute('data-event-availabilities-ok-message');
    var messages = {'onKo': koOnSaveMessage, 'onFail': failOnSaveMessage, 'onOk': onOkMessage};
    availabilityStorage = new AvailabilityStorage(eventUnavailabilitiesCreateUrl, messages);
    [].forEach.call(agendaAvailabilities, function (agendaAvailability) {
        availabilityStorage.addAvailability(agendaAvailability)
    });
}

module.exports = {init: init};
