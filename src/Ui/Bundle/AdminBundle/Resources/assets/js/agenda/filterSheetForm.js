var options = require('../vueComponents/options');

module.exports = {
    template: '#filter-sheet-form',
    props: ['sheets', 'filters'],
    delimiters: options.delimiters,
    computed: {
        types: function () {
            var types = [];
            this.sheets.forEach(function (sheet) {
                if (types.indexOf(sheet.type) === -1) {
                    types.push(sheet.type);
                }
            });

            return types;
        },
        followers: function () {
            var followers = [];
            var sheets = this.sheets;

            sheets.filter(function(sheet) {
                return !!sheet.followerLastName;
            });

            sheets.sort(function(a, b){
                if(a.followerLastName < b.followerLastName) return -1;
                if(a.followerLastName > b.followerLastName) return 1;
                return 0;
            });

            sheets.forEach(function (sheet) {
                var follower = sheet.followerFirstName + ' ' + sheet.followerLastName;

                if (followers.indexOf(follower) === - 1) {
                    followers.push(follower);
                }
            });

            return followers;
        }
    }
};
