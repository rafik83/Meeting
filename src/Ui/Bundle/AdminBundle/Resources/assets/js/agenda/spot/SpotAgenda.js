import options from '../../vueComponents/options';
import slotAgenda from './SlotAgenda';

export default {
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
