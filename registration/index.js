function form_validation() {
    let fullname = document.getElementById('fullname').value.trim();
    let email = document.getElementById('email').value.trim();
    let username = document.getElementById('username').value.trim();
    let password = document.getElementById('password').value;
    let confirm_password = document.getElementById('confirm_password').value;

    // Check for non-empty values
    if (fullname === "") {
        alert('Please enter your full name.');
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

    // Check if passwords match
    if (password !== confirm_password) {
        alert('Password must be same.');
        return false;
    }

    return true; // Form submission allowed
}


function manageFocus(currentInput) {
    const nextInput = currentInput.nextElementSibling;
    const previousInput = currentInput.previousElementSibling;
    const hasValue = currentInput.value.length > 0; // Check for actual value

    // Focus on next box on input (excluding backspace)
    if (hasValue && nextInput) {
        nextInput.focus();
    } else if (!hasValue && previousInput) { // Focus on previous on backspace
                previousInput.focus();
            }
}