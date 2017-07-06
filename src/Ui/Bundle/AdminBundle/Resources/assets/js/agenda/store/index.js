var Vue  = require('vue'),
    Vuex = require('vuex');

Vue.use(Vuex);

module.exports = new Vuex.Store({
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
