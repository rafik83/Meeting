import options from '../../../vueComponents/options';
import AgendaApiEndpoints from "../../../components/_AgendaApiEndpoints";

var api = new AgendaApiEndpoints();

export default {
    template: '#agenda-version-modal',
    delimiters: options.delimiters,
    computed: {
        show: function () {
            return this.$store.state.modal.userAgendaVersion.open;
        },
        participantId: function () {
            return this.$store.state.modal.userAgendaVersion.participantId;
        },
        hasNoPhone: function () {
            return this.answerType === 'no-phone';
        },
        hasNoDiff: function () {
            return this.answerType === 'no-diff';
        },
        hasDiff: function () {
            return this.answerType === 'diff';
        }
    },
    data: function () {
        return {
            isLoading: true,
            answerType: null,
        }
    },
    methods: {
        close: function () {
            this.$store.commit('CLOSE_USER_AGENDA_VERSION_MODAL');
            this.isLoading = true;
            this.answerType = null;
            this.diff = null;
        },
        notify: function () {
            this.isLoading = true;

            this.$http
                .post(api.notifyUserAgendaVersion(this.participantId))
                .then(function (response) {
                    this.successAction();
                }.bind(this))
                .catch(function (error) {
                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }
                    this.isLoading = false;
                }.bind(this));
        },
        purge: function () {
            this.isLoading = true;

            this.$http
                .post(api.purgeUserAgendaVersion(this.participantId))
                .then(function (response) {
                    this.successAction();
                }.bind(this))
                .catch(function (error) {
                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }
                    this.isLoading = false;
                }.bind(this));
        },
        successAction: function() {
            this.answerType = null;
            this.diff = null;
            this.close();
        }
    },
    watch: {
        participantId: function () {
            if (this.participantId !== null) {
                this.$http
                    .get(api.getUserAgendaVersion(this.participantId))
                    .then(function (response) {
                        this.answerType = response.data.answerType;
                        this.diff = response.data.diff;
                        this.isLoading = false;
                    }.bind(this))
                    .catch(function (error) {
                        if (error.response) {
                            alert(error.response.data);
                        } else {
                            alert(error.message);
                        }
                    })
            }
        }
    }
};
