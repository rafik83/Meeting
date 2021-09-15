import $ from 'jquery';
import 'jQuery-QueryBuilder';
import frTranslations from '../vendor/jQuery-QueryBuilder/i18n/fr'

function FilterBuilder(hiddenInput, builder, submitRules) {
    this.builder = $(builder);
    this.hiddenInput = $(hiddenInput);
    this.init(builder);

    submitRules.addEventListener('click', this.getRules.bind(this));
}

FilterBuilder.prototype.init = function(builder) {
    var locale = builder.getAttribute('data-locale');
    var filters = JSON.parse(builder.getAttribute('data-filters'));
    var rules = builder.getAttribute('data-rules') ? JSON.parse(builder.getAttribute('data-rules')) : '';

    this.builder.queryBuilder({
        lang_code: locale,
        lang: 'fr' === locale ? frTranslations : {}, // default jQuery-QueryBuilder included language is 'en'
        filters: filters,
        rules: rules
    });
};

FilterBuilder.prototype.getRules = function() {
    var result = this.builder.queryBuilder('getRules');

    if (!$.isEmptyObject(result)) {
        this.hiddenInput.val(JSON.stringify(result));
    }
};

export default FilterBuilder;
