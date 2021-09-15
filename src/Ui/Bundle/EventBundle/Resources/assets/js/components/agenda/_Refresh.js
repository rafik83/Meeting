function Refresh() {
    this.needToBeRefreshed = false;
    window.setTimeout(this.changeNeedToBeRefreshed.bind(this), 300000);
    window.onfocus = this.checkForRefresh.bind(this);
}

Refresh.prototype.changeNeedToBeRefreshed = function() {
    this.needToBeRefreshed = true;
};

Refresh.prototype.areThereModalOpen = function() {
    return document.querySelectorAll('.modal.in').length > 0;
};

Refresh.prototype.getCurrentUrlWithoutFragment = function() {
    return window.location.href.split('#')[0];
};

Refresh.prototype.checkForRefresh = function() {
    if (!this.needToBeRefreshed) {
        return;
    }

    if (this.areThereModalOpen()) {
        return;
    }

    location.href = this.getCurrentUrlWithoutFragment();
};

export default Refresh;
