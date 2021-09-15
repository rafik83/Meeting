import options from '../../../vueComponents/options';
import sortSheetForm from "./../form/sortSheetForm";

var DEFAULT_SELECTED_SORT = 'alphabeticalAsc';

export default {
    template: '#sort-modal',
    delimiters: options.delimiters,
    props: ['sheets', 'show'],
    components: {
        'sort-sheet-form': sortSheetForm
    },
    data: function () {
        return {
            formSort: {
                selected: DEFAULT_SELECTED_SORT
            },
            sort: {
                selected: DEFAULT_SELECTED_SORT
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
            this.formSort = {
                selected: DEFAULT_SELECTED_SORT
            };
            this.sort = {
                selected: DEFAULT_SELECTED_SORT
            };
            this.save();
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
