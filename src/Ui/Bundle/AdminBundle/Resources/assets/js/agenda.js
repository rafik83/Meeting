var Vue                    = require('vue'),
    store                  = require('./agenda/store/index'),
    options                = require('./vueComponents/options'),
    spot                   = require('./agenda/spot/Spot'),
    meeting                = require('./agenda/meeting/Meeting');

var tabs = require('./vueComponents/tabs');

Vue.component('Modal', {
    template: '#modal-template',
    props: ['show'],
    methods: {
        close: function () {
            this.$emit('close-modal');
        }
    }
});
Vue.component('spot', spot);
Vue.component('meeting', meeting);

new Vue({
    el: '#agenda',
    delimiters: options.delimiters,
    store: store
});
