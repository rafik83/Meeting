require('jQuery-QueryBuilder');
var $ = require('jquery');

function FilterBuilder(element) {
    this.element = element;

    $(element).queryBuilder({
        filters: [{
            id: 'name',
            label: 'Name',
            type: 'string'
        }, {
            id: 'category',
            label: 'Category',
            type: 'integer',
            input: 'select',
            values: {
                1: 'Books',
                2: 'Movies',
                3: 'Music',
                4: 'Tools',
                5: 'Goodies',
                6: 'Clothes'
            },
            operators: ['equal', 'not_equal', 'in', 'not_in', 'is_null', 'is_not_null']
        }]
    });
}

module.exports = FilterBuilder;
