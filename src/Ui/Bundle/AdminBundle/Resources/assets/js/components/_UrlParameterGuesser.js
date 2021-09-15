function UrlParameterGuesser ()
{
}

UrlParameterGuesser.prototype.get = function (name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');

    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    var results = regex.exec(location.search);

    return results === null ? null : decodeURIComponent(results[1].replace(/\+/g, ' '));
};

UrlParameterGuesser.prototype.has = function (name) {
    return this.get(name) !== null;
};

export default UrlParameterGuesser;
