var options = require('../../vueComponents/options');
var AgendaApiEndpoints = require('../../components/_AgendaApiEndpoints');

var api = new AgendaApiEndpoints();

module.exports = {
    template: '#agenda-spot',
    delimiters: options.delimiters,
    data: function () {
        return {
            spots: [] /** {array} Object spot */
        }
    },
    mounted: function () {
        this.loadSpots();
    },
    computed: {
        noSpots: function () {
            return this.spots.length === 0;
        }
    },
    methods: {
        loadSpots: function () {
            this.$http.get(api.getSpotsEndpoint())
                .then(function (response) {
                    this.spots = response.data;
                }.bind(this))
                .catch(function (error) {
                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }
                });
        }
    }
};
