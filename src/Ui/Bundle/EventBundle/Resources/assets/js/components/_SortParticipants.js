const Sortable = require('sortablejs').Sortable;

function SortParticipants(element) {
  new Sortable(element, {
    onSort: () => {
      element.querySelectorAll('input[type="number"]').forEach((input, key) => {
        input.value = key;
      });
    }
  });
}

module.exports = SortParticipants;
