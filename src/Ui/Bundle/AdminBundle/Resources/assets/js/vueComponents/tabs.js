var options = require('../vueComponents/options');
var spot = require('../agenda/spot/Spot');

var tab = {
    delimiters: options.delimiters,
    template: '<div v-show="isActive"><slot></slot></div>',
    props: {
        name: {
            required: true
        },
        selected: {
            default: false
        }
    },
    data: function () {
        return {
            isActive: false
        }
    },
    mounted: function () {
        this.isActive = this.selected;
    }
};

var tabSpot = {
    extends: tab,
    template: '<div v-show="isActive"><spot ref="spot" v-on:show-agenda-for-sheet-id="showAgendaForSheetId"></spot></div>',
    components: {
        'spot': spot
    }
};

var tabMeeting = {
    extends: tab
};

var tabs = {
    delimiters: options.delimiters,
    components: {
        tabSpot: tabSpot,
        tabMeeting: tabMeeting,
        tab: tab,
    },
    data: function () {
        return {
            tabs: []
        }
    },
    created: function () {
        this.tabs = this.$children;
    },
    methods: {
        /** @param {Object} selectedTab **/
        selectTab: function (selectedTab) {
            this.tabs.forEach(function (tab) {
                tab.isActive = (selectedTab === tab);
            });
        }
    }
};

module.exports = tabs;
