var sortSheetForm = require('./../form/sortSheetForm'),
    options       = require('../../../vueComponents/options');

module.exports = {
    template: '#sort-modal',
    delimiters: options.delimiters,
    props: ['sheets', 'show'],
    components: {
        'sort-sheet-form': sortSheetForm
    },
    data: function () {
        return {
            formSort: {
                selected: false
            },
            sort: {
                selected: false
            }
        }
    },

    methods: {
        save: function () {
            this.setUsedSort();
            this.$emit('sort-sheets', this.sort.selected);
            this.$emit('close-modal');
        },
        reset: function () {
            this.sort = {
                selected: false
            };
        },
        setUsedSort: function() {
            Object.assign(this.sort, this.formSort);
        }
    },
    watch: {
        selected: function(value) {
            this.sort.selected = value;
        }
    }
};
