const Sortable = require('sortablejs').Sortable;

function SortParticipants(element) {
  new Sortable(element, {
    draggable: '.user'
  });
}

module.exports = SortParticipants;
