var options = require('../../vueComponents/options');

module.exports = {
    template: '#spot-slot-agenda',
    delimiters: options.delimiters,
    props: {
        agendaSlot: {type: Object, required: true}
    }
};
