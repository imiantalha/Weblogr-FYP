function form_validation() {
    let first_name = document.getElementById('first_name').value.trim();
    let last_name = document.getElementById('last_name').value.trim();
    let email = document.getElementById('email').value.trim();
    let username = document.getElementById('username').value.trim();
    let password = document.getElementById('password').value;
    let confirm_password = document.getElementById('confirm_password').value;

    if (first_name === "" || last_name === "") {
        alert('Please enter your first and last name.');
        return false;
    }
    if (email === "") {
        alert('Please enter your email.');
        return false;
    }
    if (username === "") {
        alert('Please enter your username.');
        return false;
    }
    if (password === "") {
        alert('Please enter your password.');
        return false;
    }
    if (confirm_password === "") {
        alert('Please confirm your password.');
        return false;
    }
    if (password !== confirm_password) {
        alert('Password must be same.');
        return false;
    }
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
