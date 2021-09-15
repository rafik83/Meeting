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
    var hours = 0;
    var minutes = 0;

    if (splitYear[1]) {
        var splitTime = splitYear[1].split(':');
        hours = splitTime[0];
        minutes = splitTime[1];
    }

    var day = splitDate[0];
    var month = splitDate[1];
    var year = splitYear[0];

    return new Date(year, month - 1, day, hours, minutes);
};

DateTimeManipulation.prototype.formatDate = function (date)
{
    if (typeof date === 'undefined' || date === null) {
        return null;
    }

    var day = this.addPaddingZero(date.getDate());
    var hours = this.addPaddingZero(date.getHours());
    var minutes = this.addPaddingZero(date.getMinutes());
    var months = this.addPaddingZero(date.getMonth() + 1);

    return day + "/" + months + "/" +  + date.getFullYear() + " " + hours + ':' + minutes;
};

DateTimeManipulation.prototype.addPaddingZero = function (number)
{
    return number < 10 ? '0' + number : number;
};

export default DateTimeManipulation;
