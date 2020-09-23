import options from '../../vueComponents/options';
import eventDispatcher from '../../vueComponents/EventDispatcher';

export default {
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
