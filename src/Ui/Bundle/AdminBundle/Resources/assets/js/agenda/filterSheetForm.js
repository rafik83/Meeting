module.exports = {
    template: '#filter-sheet-form',
    props: ['sheets', 'filters'],
    delimiters: ['${', '}'],
    computed: {
        types: function () {
            var types = [];
            this.sheets.forEach(function (sheet) {
                if (types.indexOf(sheet.type) === -1) {
                    types.push(sheet.type);
                }
            });

            return types;
        }
    }
};
