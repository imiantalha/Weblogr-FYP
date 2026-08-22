(() => {
    const messageClass = 'client-validation-message';

    const injectStyles = () => {
        if (document.getElementById('weblogr-validation-styles')) return;
        const style = document.createElement('style');
        style.id = 'weblogr-validation-styles';
        style.textContent = `.${messageClass}{display:block;margin:5px 0;color:#b42318;font-size:12px;font-weight:600;line-height:1.4}.input-error{border-color:#f04438!important;box-shadow:0 0 0 3px rgba(240,68,56,.08)!important}`;
        document.head.appendChild(style);
    };

    const getMessage = (field) => {
        if (field.validity.valueMissing) return `${field.dataset.label || 'This field'} is required.`;
        if (field.validity.typeMismatch) return 'Please enter a valid email address.';
        if (field.validity.tooShort) return `${field.dataset.label || 'This field'} must be at least ${field.minLength} characters.`;
        if (field.validity.tooLong) return `${field.dataset.label || 'This field'} must be ${field.maxLength} characters or fewer.`;
        if (field.validity.patternMismatch) return field.dataset.error || `Please enter a valid ${field.dataset.label || 'value'}.`;
        return '';
    };

    const showError = (field, message) => {
        field.classList.add('input-error');
        field.setAttribute('aria-invalid', 'true');
        let messageElement = field.parentElement?.querySelector(`.${messageClass}[data-for="${field.name}"]`);
        if (!messageElement) {
            messageElement = document.createElement('small');
            messageElement.className = messageClass;
            messageElement.dataset.for = field.name;
            field.insertAdjacentElement('afterend', messageElement);
        }
        messageElement.textContent = message;
    };

    const clearError = (field) => {
        field.classList.remove('input-error');
        field.removeAttribute('aria-invalid');
        const messageElement = field.parentElement?.querySelector(`.${messageClass}[data-for="${field.name}"]`);
        if (messageElement) messageElement.remove();
    };

    const validateField = (field) => {
        if (field.disabled || field.type === 'hidden' || !field.required) return true;
        const message = getMessage(field);
        if (message) {
            showError(field, message);
            return false;
        }
        clearError(field);
        return true;
    };

    const bindForms = () => {
        injectStyles();
        document.querySelectorAll('form').forEach((form) => {
            if (form.dataset.validationBound === 'true' || !form.querySelector('[required]')) return;
            form.dataset.validationBound = 'true';
            form.noValidate = true;

            form.querySelectorAll('input, textarea, select').forEach((field) => {
                if (field.required) field.addEventListener('blur', () => validateField(field));
                field.addEventListener('input', () => {
                    if (field.classList.contains('input-error')) validateField(field);
                });
                field.addEventListener('change', () => validateField(field));
            });

            form.addEventListener('submit', (event) => {
                let firstInvalid = null;
                form.querySelectorAll('input, textarea, select').forEach((field) => {
                    if (!validateField(field) && firstInvalid === null) firstInvalid = field;
                });
                if (firstInvalid) {
                    event.preventDefault();
                    firstInvalid.focus();
                }
            });
        });
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bindForms);
    else bindForms();
})();
