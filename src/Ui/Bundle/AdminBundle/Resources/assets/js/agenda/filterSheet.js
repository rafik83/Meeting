module.exports = {
    template: '#filter-sheet-form',
    props: ['sheets', 'show'],
    delimiters: ['${', '}'],
    data: function () {
        return {
            selectedTypes: [],
            filteredSheets: [],
            hasSentMeetingRequest: false,
            hasMeetingToApprove: false,
            hasNotEnoughtAvailableSlot: false
        }
    },
    methods: {
        save: function () {
            var filteredSheet = this.sheets;

            if (this.selectedTypes.length > 0) {
                filteredSheet = filteredSheet.filter(function (sheet) {
                    return this.selectedTypes.indexOf(sheet.type) !== -1;
                }.bind(this));
            }

            if (this.hasIndicatorFilter) {
                filteredSheet = filteredSheet.filter(function (sheet) {
                    if (this.hasSentMeetingRequest === true
                        && sheet.hasSentMeetingRequest === this.hasSentMeetingRequest) {
                        return true
                    }

                    if (this.hasMeetingToApprove === true
                        && sheet.hasMeetingToApprove === this.hasMeetingToApprove) {
                        return true
                    }

                    return this.hasNotEnoughtAvailableSlot === true
                        && sheet.hasNotEnoughtAvailableSlot === this.hasNotEnoughtAvailableSlot;
                }.bind(this));
            }

            this.filteredSheets = filteredSheet;
            this.$emit('refresh-list', this.filteredSheets);
            this.$emit('close-modal');
        }
    },
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
        hasIndicatorFilter: function () {
            return this.hasSentMeetingRequest === true
                || this.hasMeetingToApprove === true
                || this.hasNotEnoughtAvailableSlot === true;
        }
    }
};
