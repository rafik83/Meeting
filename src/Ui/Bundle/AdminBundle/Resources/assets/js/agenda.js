import Vue     from 'vue';
import store   from './agenda/store/index';
import options from './vueComponents/options';
import spot    from './agenda/spot/Spot';
import meeting from './agenda/meeting/Meeting';
import tabs from './vueComponents/tabs';

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
