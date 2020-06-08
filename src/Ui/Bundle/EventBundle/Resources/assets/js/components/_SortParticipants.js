import sortableLib from 'sortablejs';
const Sortable = sortableLib.Sortable;

function SortParticipants(element) {
  const rankInputsSelector = 'input[type="number"]';

  new Sortable(element, {
    draggable: '[data-participant]',
    onSort: () => {
      element.querySelectorAll(rankInputsSelector).forEach((input, key) => {
        input.value = key + 1;
      });
    }
  });

  const editSortLink = element.querySelector('[data-edit-sort-participants]');

  editSortLink.addEventListener('click', (event) => {
    event.preventDefault();
    const rankInputs = element.querySelectorAll(rankInputsSelector);
    rankInputs.forEach(input => input.classList.contains('hide') ? input.classList.remove('hide') : input.classList.add('hide'));
    rankInputs[0].focus();
  });
}

export default SortParticipants;
