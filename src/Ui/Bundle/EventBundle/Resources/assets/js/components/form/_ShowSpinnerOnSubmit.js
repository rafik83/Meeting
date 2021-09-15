export function showSpinnerOnSubmit() {
    document.querySelectorAll('[data-show-spin-on-submit]').forEach((button) => {
        button.addEventListener('submit', (event) => {
            document.getElementById(button.getAttribute('data-show-spin-on-submit')).classList.remove('hide');
        });
    });
}
