var options = require('../vueComponents/options'),
    DateTimePicker = require('../components/_DateTimePicker');
require('moment/locale/fr');
require('moment/locale/en-gb');

module.exports = {
    template: '#mass-assignment-form',
    delimiters: options.delimiters,
    props: [],
    data: function () {
        return {}
    },
    mounted: function () {
        [].forEach.call(document.querySelectorAll('[data-datatimepicker]'), function (element) {
            new DateTimePicker(element);
        });
    },
    methods: {
        init: function () {

        }
    }
};
