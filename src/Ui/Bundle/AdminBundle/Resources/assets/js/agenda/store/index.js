import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        modal: {
            userAgendaVersion: {
                open: false,
                participantId: null,
            },
        },
    },
    mutations: {
        OPEN_USER_AGENDA_VERSION_MODAL: function (state, participantId) {
            state.modal.userAgendaVersion.open = true;
            state.modal.userAgendaVersion.participantId = participantId;
        },
        CLOSE_USER_AGENDA_VERSION_MODAL: function (state) {
            state.modal.userAgendaVersion.open = false;
            state.modal.userAgendaVersion.participantId = null;
        }
    }
});
