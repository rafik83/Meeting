var Vue   = require('vue'),
    axios = require('axios');

Vue.prototype.$http = axios;

var app = new Vue({
    el: '#agenda',
    delimiters: ['${', '}'],
    data: {
        sheets: [],
        agendas: [],
        focus: null
    },
    methods: {
        loadSheets: function () {
            axios.get('/app_dev.php/admin/fr/event/2/agenda/sheets')
                .then((response) => {
                    this.sheets = response.data;
                })
                .catch((error) => {
                    console.log(error);
                });
        },
        showAgenda: function(sheet) {
            this.agendas.push(sheet);
            this.focus = sheet;
        },
        closeAgenda: function (sheet) {
            this.agendas.splice(this.agendas.indexOf(sheet), 1);

            if (this.focus == sheet) {
                this.focus = null;
            }
        },
        focusAgenda: function (sheet) {
            this.focus = sheet;
        }
    }
});

app.loadSheets();
