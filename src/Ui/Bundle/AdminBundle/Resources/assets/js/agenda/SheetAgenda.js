var vue = require('vue');
var slot = require('./Slot');

module.exports = {
    template: '#sheet-agenda',
    props: ['sheet'],
    components: {
        'slot': slot
    },
    methods: {
        focus: function () {

        },
        close: function () {
            this.$emit('close-sheet-agenda', this.sheet);
        }
    }
};
