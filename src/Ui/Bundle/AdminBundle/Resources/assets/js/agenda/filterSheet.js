module.exports = {
    template: '#filter-sheet-form',
    props: ['sheets'],
    data: function () {
        return {
            test: 'hello world'
        }
    },
    methods: {
        save: function () {

        }
    },
    computed: {
        types: function () {
            var types = [];

            this.sheets.forEach(function (sheet) {

                if (types.indexOf(sheet.types) === -1) {
                    types.push(sheet.types);
                }
            });
        }
    }
};
