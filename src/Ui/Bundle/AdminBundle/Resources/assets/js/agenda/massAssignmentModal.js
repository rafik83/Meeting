var options = require('../vueComponents/options'),
    massAssignmentForm = require('./massAssignmentForm');

module.exports = {
    template: '#mass-assignment-modal-template',
    delimiters: options.delimiters,
    props: ['show'],
    components: { 'mass-assignment-form': massAssignmentForm },
    data: function () {
        return  {}
    },
    methods: {
        /**
         * @param {int} massId
         */
        init: function(massId) {
            var child = this.$refs.massAssignmentForm;
            if (typeof child !== 'undefined') {
                child.init(massId);
            }
        },
        save: function () {
            
        }
    }
};
