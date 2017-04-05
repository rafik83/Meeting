var options = require('../vueComponents/options');
var Vue = require('vue');

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

var tabs = {
    delimiters: options.delimiters,
    components: {
        tab: tab
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

Vue.component('tab', tab);
Vue.component('tabs', tabs);

