var options = require('../../vueComponents/options');

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
            this.$emit('show-agenda-for-sheet-id', sheetId)
        }
    }
};
