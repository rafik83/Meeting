var vue = require('vue'),
    slotAgenda = require('./SlotAgenda'),
    options = require('../vueComponents/options');

module.exports = {
    template: '#sheet-agenda',
    delimiters: options.delimiters,
    props: ['sheet', 'availableSlots'],
    components: {
        'slot-agenda': slotAgenda
    },
    methods: {
        init: function () {
            this.$emit('load-sheets');
        },
        focus: function () {
            this.$emit('focus-sheet', this.sheet);
        },
        close: function () {
            this.$emit('close-sheet-agenda', this.sheet);
        },
        /**
         * @param {Object} meetingToUpdate
         */
        showMeetingUpdateModal: function (meetingToUpdate) {
            this.$emit('show-meeting-update-modal', meetingToUpdate);
        },
        /**
         * @param {Object} sheet
         */
        loadAgenda: function (sheet) {
            this.$emit('load-agenda', sheet)
        },
        /**
         *
         * Emit event to show agenda of given sheet id
         *
         * @param {int} sheetMetId
         */
        showAgendaForSheetId: function (sheetMetId) {
            this.$emit('show-agenda-for-sheet-id', sheetMetId);
        }
    }
};
