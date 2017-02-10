var options = require('../vueComponents/options'),
    massAssignmentForm = require('./massAssignmentForm');

module.exports = {
    template: '#mass-assignment-modal-template',
    delimiters: options.delimiters,
    props: ['show', 'agendaSlot'],
    components: { 'mass-assignment-form': massAssignmentForm },
    data: function () {
        return  {}
    },
    methods: {
        save: function () {
            
        }
    }
};