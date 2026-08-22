function form_validation() {
    return true;
}

function manageFocus(currentInput) {
    const nextInput = currentInput.nextElementSibling;
    const previousInput = currentInput.previousElementSibling;
    const hasValue = currentInput.value.length > 0;
    if (hasValue && nextInput) {
        nextInput.focus();
    } else if (!hasValue && previousInput) {
        previousInput.focus();
    }
}
