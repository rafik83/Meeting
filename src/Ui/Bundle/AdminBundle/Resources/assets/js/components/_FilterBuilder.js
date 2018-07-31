require('jQuery-QueryBuilder');
var $ = require('jquery');

function FilterBuilder(builder, submitRules) {
    this.builder = $(builder);
    this.init(builder);

    submitRules.addEventListener('click', this.getRules.bind(this));
}

FilterBuilder.prototype.init = function(builder) {
    this.builder.queryBuilder({
        lang_code: builder.getAttribute('data-locale'),
        filters: JSON.parse(builder.getAttribute('data-rules'))
    });
};

FilterBuilder.prototype.getRules = function() {
    let result = this.builder.queryBuilder('getRules');

    if (!$.isEmptyObject(result)) {
        alert(JSON.stringify(result, null, 2));
    }
};

module.exports = FilterBuilder;
