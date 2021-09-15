import options from '../../vueComponents/options';
import AgendaApiEndpoints from "../../components/_AgendaApiEndpoints";
import spotAgenda from './SpotAgenda';
import eventDispatcher from '../../vueComponents/EventDispatcher';

var api = new AgendaApiEndpoints();

export default {
    template: '#spots-agenda',
    delimiters: options.delimiters,
    components: {
        spotAgenda: spotAgenda
    },
    data: function () {
        return {
            spots: [], /** {array} Object spot */
            openedSpots: [] /** {array} Object spot */
        }
    },
    mounted: function () {
        this.loadSpots();

        eventDispatcher.listen('load-spot-detail', function (spotId) {
            this.loadSpotDetail(spotId);
        }.bind(this));
    },
    computed: {
        noSpots: function () {
            return this.spots.length === 0;
        }
    },
    methods: {
        /**
         * @param {Object} spot
         */
        detailAction: function (spot) {
            if (!this.isSpotOpened(spot)) {
                this.loadSpotDetail(spot.id);
            }
        },
        /**
         * @param {Object} spot
         */
        closeSpotAgenda: function (spot) {
            var index = this.getOpenedSpotIndex(spot);
            if (index !== null) {
                this.openedSpots.splice(index, 1);
            }
        },
        /**
         * Load all spots
         */
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
        },
        /**
         * @param {int} spotId
         */
        loadSpotDetail: function (spotId) {
            this.$http.get(api.getSpotsDetailEndpoint(spotId))
                .then(function (response) {
                    var spot = response.data;

                    this.populateSpotAgenda(spot);
                }.bind(this))
                .catch(function (error) {
                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }
                });
        },
        /**
         * @param {Object} spot
         */
        populateSpotAgenda: function (spot) {
            if (!this.isSpotOpened(spot)) {
                this.openedSpots.push(spot);
            }
        },
        /**
         * @param {object} spot
         * @return {boolean}
         */
        isSpotOpened: function (spot) {
            var find = false;

            this.openedSpots.forEach(function (openedSpot) {
                if (openedSpot.id === spot.id) {
                    return find = true;
                }
            });

            return find;
        },
        /**
         * @param {Object} spot
         * @return {int|null}
         */
        getOpenedSpotIndex: function (spot) {
            for (var index = 0; index < this.openedSpots.length; index++) {
                if (spot.id === this.openedSpots[index].id) {
                    return index;
                }
            }

            return null;
        }
    }
};
