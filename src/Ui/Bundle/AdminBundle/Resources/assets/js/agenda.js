var Vue                    = require('vue'),
    store                  = require('./agenda/store/index'),
    options                = require('./vueComponents/options'),
    spot                   = require('./agenda/spot/Spot'),
    meeting                = require('./agenda/meeting/Meeting'),
    Nl2br                  = require('vue-nl2br');

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
Vue.component('nl2br', Nl2br);

new Vue({
    el: '#agenda',
    delimiters: options.delimiters,
    store: store
});
