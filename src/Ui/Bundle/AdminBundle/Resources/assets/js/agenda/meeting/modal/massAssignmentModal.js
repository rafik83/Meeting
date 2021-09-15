import options from '../../../vueComponents/options';
import massAssignmentForm from './../form/massAssignmentForm';
import AgendaApiEndpoints from "../../../components/_AgendaApiEndpoints";
import querystring from 'querystring';

var api = new AgendaApiEndpoints();

export default {
    template: '#mass-assignment-modal-template',
    delimiters: options.delimiters,
    props: ['show'],
    components: {'mass-assignment-form': massAssignmentForm},
    data: function () {
        return {
            massId: null,
            child: 'undefined',
        }
    },
    watch: {
        'show': function () {
            var child = this.$refs.massAssignmentForm;
            if (typeof child !== 'undefined') {
                child.reset();
            }
        }
    },
    methods: {
        /**
         * @param {int} massId
         */
        init: function (massId) {
            this.child = this.$refs.massAssignmentForm;
            if (typeof this.child !== 'undefined') {
                this.massId = massId;
                this.child.init(massId);
            }
        },
        save: function () {
            this.$http
                .post(api.getUpdateMassAssignmentEndpoint(this.massId),
                    querystring.stringify({
                        begin: this.child.beginTime,
                        end: this.child.endTime,
                        enabled: this.child.enabled
                    })
                )
                .then(function () {
                    this.$emit('mass-assignment-updated');
                    this.close();
                }.bind(this))
                .catch(function (error) {
                    if (error.response) {
                        alert(error.response.data);
                    } else {
                        alert(error.message);
                    }
                }.bind(this));
        },
        close: function () {
            this.$emit('close-modal');
        },
    }
};
