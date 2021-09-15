import $ from 'jquery';

function AnchorFocuser(element, location)
{
    this.element  = element;
    this.location = location;

    if (this.element.getAttribute('data-switch-to-tab') === 'anchor') {
        this.focusOnAnchor();
    }
}

AnchorFocuser.prototype.focusOnAnchor = function ()
{
    var hash       = this.location.hash;
    var hashPieces = hash.split('?');

    if (hashPieces.length > 0 && hashPieces[0] !== "") {
        var activeTab = $('[href=' + hashPieces[0] + ']', this.element);

        if (activeTab.length !== 0) {
            activeTab.tab('show');
        }
    }
};

export default AnchorFocuser;
