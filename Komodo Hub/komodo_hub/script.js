async function handleForm(formId, endpoint) {
    const form = document.getElementById(formId);
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing...';

        const formData = {};
        new FormData(form).forEach((value, key) => {
            formData[key] = value;
        });

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.error || 'Request failed');
            }

            if (result.redirect) {
                window.location.href = result.redirect;
            } else {
                alert(result.success || 'Action completed');
                if (formId === 'signupForm') {
                    window.location.href = 'dashboard.html';
                }
            }
        } catch (error) {
            console.error('Error:', error);
            alert(error.message || 'Failed to submit form');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    input.type = input.type === 'password' ? 'text' : 'password';
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('signupForm')) {
        handleForm('signupForm', 'signup.php');
    }
    if (document.getElementById('loginForm')) {
        handleForm('loginForm', 'signin.php');
    }
});
