document.addEventListener('DOMContentLoaded', function() {
    initializePasswordToggles();
    addRealTimeValidation();
    addFormSubmissionValidation();
    toggleStudentField(); // ensure correct visibility on load
});

function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const toggleBtn = passwordInput.parentElement.querySelector('.toggle-password');

    if (passwordInput && toggleBtn) {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleBtn.textContent = '';
        } else {
            passwordInput.type = 'password';
            toggleBtn.textContent = '';
        }
    }
}

function initializePasswordToggles() {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const passwordInput = this.parentElement.querySelector('input[type="password"], input[type="text"]');
            if (passwordInput) togglePassword(passwordInput.id);
        });
    });
}

// REPLACE this function in script.js
function toggleStudentField() {
    const roleSelect = document.getElementById('role');
    const studentGroup = document.getElementById('studentNumberGroup');
    const studentInput = document.getElementById('studentNumber');

    // ADD THIS CHECK — exits if elements don't exist (i.e. on signup page)
    if (!roleSelect || !studentGroup || !studentInput) return;

    if (roleSelect.value === 'student') {
        studentGroup.style.display = 'block';
        studentInput.required = true;
    } else {
        studentGroup.style.display = 'none';
        studentInput.required = false;
    }
}

function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validateStudentNumber(studentNumber) {
    const studentNumberRegex = /^[0-9]{6,15}$/;
    return studentNumberRegex.test(studentNumber);
}

function addStudentNumberInputRestriction() {
    // covers both login and signup student number fields
    const studentNumberInputs = document.querySelectorAll('#studentNumber, #signupStudentNumber');
    studentNumberInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
}

function addRealTimeValidation() {
    const inputs = document.querySelectorAll('input[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() { validateField(this); });
        input.addEventListener('input', debounce(function() {
            if (this.classList.contains('invalid')) validateField(this);
        }, 300));
    });
    addStudentNumberInputRestriction();
}

function validateField(field) {
    const value = field.value.trim();
    const fieldType = field.type;
    const fieldId = field.id;
    
    field.classList.remove('valid', 'invalid');
    let isValid = true;
    let errorMessage = '';

    if (!value) {
        isValid = false;
        errorMessage = 'This field is required';
    } else if (fieldType === 'email') {
        isValid = validateEmail(value);
        errorMessage = 'Please enter a valid email address';
    } else if (fieldId.includes('Password') || fieldId.includes('password')) {
        isValid = value.length >= 6;
        errorMessage = 'Password must be at least 6 characters long';
    } else if (fieldId === 'studentNumber' || fieldId === 'signupStudentNumber') {
        isValid = validateStudentNumber(value);
        errorMessage = 'ID must be 6-15 digits only';
    }

    field.classList.add(isValid ? 'valid' : 'invalid');

    const existingError = field.parentElement.querySelector('.field-error');
    if (existingError) existingError.remove();

    if (!isValid) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = errorMessage;
        errorDiv.style.color = '#a43825';
        errorDiv.style.fontSize = '12px';
        errorDiv.style.marginTop = '5px';
        field.parentElement.appendChild(errorDiv);
    }
    
    return isValid;
}

function addFormSubmissionValidation() {
    const forms = document.querySelectorAll('#loginForm, #signupForm');

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const inputs = this.querySelectorAll('input[required]');
            let isFormValid = true;

            inputs.forEach(input => {
                if (!validateField(input)) isFormValid = false;
            });

            if (!isFormValid) {
                e.preventDefault();
                const firstInvalid = this.querySelector('.invalid');
                if (firstInvalid) firstInvalid.focus();
                return;
            }

            // For login, let PHP handle actual authentication
            // So we just let the form submit normally
        });
    });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function googleSignup() {
    alert('Google Sign Up coming soon!');
}

function facebookSignup() {
    alert('Facebook Sign Up coming soon!');
}

