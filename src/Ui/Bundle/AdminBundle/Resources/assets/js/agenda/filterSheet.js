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
                // TODO: regarder si type de fiche existe déjà dans types, si non l'ajouter dedans
            });

            return types;
        }
    }
};
