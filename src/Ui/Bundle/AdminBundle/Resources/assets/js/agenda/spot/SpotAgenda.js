var options = require('../../vueComponents/options');
var slotAgenda = require('./SlotAgenda.js');

module.exports = {
    template: '#spot-agenda',
    delimiters: options.delimiters,
    props: {
        spot: {type: Object, required: true}
    },
    components: {
        spotSlotAgenda: slotAgenda
    },
    data: function () {
        return {}
    },
    methods: {
        close: function () {
            this.$emit('close-spot-agenda', this.spot);
        }
    }
};
