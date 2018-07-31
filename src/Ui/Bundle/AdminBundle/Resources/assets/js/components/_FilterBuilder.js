require('jQuery-QueryBuilder');
var $ = require('jquery');

function FilterBuilder(hiddenInput, builder, submitRules) {
    this.builder = $(builder);
    this.hiddenInput = $(hiddenInput);
    this.init(builder);

    submitRules.addEventListener('click', this.getRules.bind(this));
}

FilterBuilder.prototype.init = function(builder) {
    this.builder.queryBuilder({
        lang_code: builder.getAttribute('data-locale'),
        filters: JSON.parse(builder.getAttribute('data-filters')),
        rules: builder.getAttribute('data-rules') ? JSON.parse(builder.getAttribute('data-rules')) : ''
    });
};

FilterBuilder.prototype.getRules = function() {
    let result = this.builder.queryBuilder('getRules');

    if (!$.isEmptyObject(result)) {
        this.hiddenInput.val(JSON.stringify(result));
    }
};

module.exports = FilterBuilder;
