function addSubmitEventListenerOnElementChange(target, formName, elementName) {
    const form = target.querySelector(`form[name="${formName}"]`);

    if (null !== form) {
        form.querySelectorAll(`[name="${formName}[${elementName}]"]`)
            .forEach(function (elementInput) {
                elementInput.addEventListener('change', function () {
                    form.submit();
                });
            })
        ;
    }
}

export default addSubmitEventListenerOnElementChange;
