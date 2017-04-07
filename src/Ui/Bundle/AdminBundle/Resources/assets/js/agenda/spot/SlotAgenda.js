var options = require('../../vueComponents/options');
var eventDispatcher = require('../../vueComponents/EventDispatcher');

module.exports = {
    template: '#spot-slot-agenda',
    delimiters: options.delimiters,
    props: {
        agendaSlot: {type: Object, required: true}
    },

    methods: {
        /**
         * @param {int} sheetId
         */
        showAgendaForSheetId: function (sheetId) {
            eventDispatcher.dispatch('show-agenda-for-sheet-id', sheetId);
            eventDispatcher.dispatch('toggleTab', 'sheetTab');
        }
    }
};
