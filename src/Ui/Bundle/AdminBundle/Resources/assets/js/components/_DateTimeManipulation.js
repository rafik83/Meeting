function DateTimeManipulation()
{
}

DateTimeManipulation.prototype.getTimestampByInternationalFormat = function (date)
{
    if (!date || -1 === date.indexOf('/')) {
        return;
    }

    var splitDate = date.split('/');
    var splitYear = splitDate[2].split(' ');
    var splitTime = splitYear[1].split(':');

    var day = splitDate[0];
    var month = splitDate[1];
    var year = splitYear[0];
    var hours = splitTime[0];
    var minutes = splitTime[1];

    return new Date(year, month, day, hours, minutes);
};

DateTimeManipulation.prototype.formatDate = function (date)
{
    if (typeof date === 'undefined' || date === null) {
        return null;
    }

    var hours = this.addPaddingZero(date.getHours());
    var minutes = this.addPaddingZero(date.getMinutes());
    var months = this.addPaddingZero(date.getMonth() + 1);

    return date.getDate() + "/" + months + "/" +  + date.getFullYear() + " " + hours + ':' + minutes;
};

DateTimeManipulation.prototype.addPaddingZero = function (number)
{
    return number < 10 ? '0' + number : number;
};

module.exports = DateTimeManipulation;
