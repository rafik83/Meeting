import options from '../../../vueComponents/options';

export default {
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
        sheetsWithFollower: function() {
            var sheets = this.sheets;

            sheets = sheets.filter(function(sheet) {
                return sheet.hasFollower;
            });

            return sheets;
        },
        followers: function () {
            var followers = {};

            this.sheetsWithFollower.forEach(function (sheet) {
                var follower = {
                    id: sheet.follower.id,
                    // Last name is only used to sort
                    lastName: sheet.follower.lastName,
                    fullname: sheet.follower.firstName + ' ' + sheet.follower.lastName
                };
                // Use follower id to avoid duplication
                followers[follower.id] = follower;
            });

            return this.sortFollowersByLastName(followers);
        },
        follower_unassigned: function() {
            return 'follower_unassigned';
        }
    },
    methods: {
        sortFollowersByLastName: function(followers) {
            var sortable = [];

            // Switch object into array to sort
            Object.keys(followers).map(function(follower) {
                sortable.push(followers[follower]);
            });

            return sortable.sort(function(followerA, followerB){
                if (followerA.lastName < followerB.lastName) return -1;
                if (followerA.lastName > followerB.lastName) return 1;
                return 0;
            });
        },
    }
};
