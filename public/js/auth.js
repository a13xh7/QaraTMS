document.addEventListener('DOMContentLoaded', function() {
    // Email validation
    const emailField = document.getElementById('email');
    const emailError = document.getElementById('emailError');
    
    function validateEmail(email) {
        const validDomains = ['@admin.id', '@gmail.com', '@admin.com', '@guest.com'];
        if (!email) {
            return 'Please fill the email field';
        }
        
        if (!email.includes('@')) {
            return 'Please include an \'@\' in the email address';
        }
        
        const domain = '@' + email.split('@')[1];
        if (!validDomains.includes(domain)) {
            return 'Email domain not allowed. Use @gmail.com, @admin.com, or @guest.com';
        }
        
        return null; // no error
    }

    emailField.addEventListener('blur', function() {
        const error = validateEmail(this.value);
        if (error) {
            emailError.textContent = error;
            emailError.style.display = 'block';
            emailField.classList.add('is-invalid');
        } else {
            emailError.style.display = 'none';
            emailField.classList.remove('is-invalid');
        }
    });

    // Password toggle functionality
    const toggleIcon = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');
    
    if (toggleIcon && passwordField) {
        toggleIcon.addEventListener('click', function() {
            if (passwordField.type === 'password') {
                // Show password
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            } else {
                // Hide password
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            }
        });
    }

    const copyButtons = document.querySelectorAll('.copy-message');
    copyButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const message = this.getAttribute('data-message');
            
            const copyOperation = new Promise((resolve, reject) => {
                if (!navigator.clipboard) {
                    try {
                        const textArea = document.createElement('textarea');
                        textArea.value = message;
                        Object.assign(textArea.style, {
                            position: 'fixed',
                            top: '0',
                            left: '-9999px',
                            opacity: '0'
                        });
                        document.body.appendChild(textArea);
                        textArea.focus();
                        textArea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textArea);
                        resolve();
                    } catch (err) {
                        reject(err);
                    }
                } else {
                    navigator.clipboard.writeText(message)
                        .then(resolve)
                        .catch(reject);
                }
            });

            const originalHTML = this.innerHTML;
            const originalClasses = [...this.classList];

            copyOperation
                .then(() => {
                    requestAnimationFrame(() => {
                        this.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
                        this.classList.remove('btn-outline-secondary');
                        this.classList.add('btn-success');
                    });
                })
                .catch((err) => {
                    console.error('Copy failed:', err);
                    requestAnimationFrame(() => {
                        this.innerHTML = '<i class="fas fa-times me-1"></i> Failed!';
                        this.classList.remove('btn-outline-secondary');
                        this.classList.add('btn-danger');
                    });
                })
                .finally(() => {
                    setTimeout(() => {
                        requestAnimationFrame(() => {
                            this.innerHTML = originalHTML;
                            this.classList.remove('btn-success', 'btn-danger');
                            originalClasses.forEach(cls => this.classList.add(cls));
                        });
                    }, 2000);
                });
        });
    });
}); 