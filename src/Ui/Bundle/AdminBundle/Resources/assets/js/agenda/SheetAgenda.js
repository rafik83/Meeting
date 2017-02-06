var vue           = require('vue'),
    slotAgenda    = require('./SlotAgenda'),
    options       = require('../vueComponents/options');

module.exports = {
    template: '#sheet-agenda',
    delimiters: options.delimiters,
    props: ['sheet'],
    components: {
        'slot-agenda': slotAgenda
    },
    datas: {
        agendas: [] /** Opened sheets */
    },
    methods: {
        init: function () {
            this.$emit('load-sheets');
        },
        focus: function () {

        },
        close: function () {
            this.$emit('close-sheet-agenda', this.sheet);
        },
        findSheetAgenda: function (sheet) {
            return this.agendas.indexOf(sheet);
        },
        loadAgenda: function() {

        },
    }
};
