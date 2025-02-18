document.addEventListener('DOMContentLoaded', function() {
    // Handle login/register tabs
    const tabBtns = document.querySelectorAll('.tab-btn');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    if (tabBtns.length > 0) {
        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons and forms
                tabBtns.forEach(b => b.classList.remove('active'));
                loginForm.classList.remove('active');
                registerForm.classList.remove('active');

                // Add active class to clicked button and corresponding form
                this.classList.add('active');
                if (this.dataset.tab === 'login') {
                    loginForm.classList.add('active');
                } else {
                    registerForm.classList.add('active');
                }
            });
        });
    }

    // Handle login form submission
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            // For demo purposes, using hardcoded credentials
            // In production, this should be replaced with proper authentication
            if (email === 'customer@example.com' && password === 'customer123') {
                window.location.href = 'dashboard.html';
            } else {
                alert('Invalid credentials. Please try again.');
            }
        });
    }

    // Handle register form submission
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const name = document.getElementById('regName').value;
            const email = document.getElementById('regEmail').value;
            const phone = document.getElementById('regPhone').value;
            const password = document.getElementById('regPassword').value;
            const confirmPassword = document.getElementById('regConfirmPassword').value;

            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return;
            }

            // For demo purposes
            alert('Registration successful! Please login with your credentials.');
            
            // Switch to login tab
            document.querySelector('[data-tab="login"]').click();
        });
    }

    // Toggle password visibility
    const togglePasswordBtns = document.querySelectorAll('.toggle-password');
    if (togglePasswordBtns.length > 0) {
        togglePasswordBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const passwordInput = this.previousElementSibling;
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
    }

    // Handle social login buttons
    const socialBtns = document.querySelectorAll('.social-btn');
    if (socialBtns.length > 0) {
        socialBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                alert('Social login feature coming soon!');
            });
        });
    }

    // Handle notifications
    const notificationBtn = document.querySelector('.notification-btn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function() {
            alert('Notifications feature coming soon!');
        });
    }

    // Handle booking actions
    const rescheduleButtons = document.querySelectorAll('.reschedule-btn');
    const cancelButtons = document.querySelectorAll('.cancel-btn');

    rescheduleButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            alert('Reschedule feature coming soon!');
        });
    });

    cancelButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Are you sure you want to cancel this booking?')) {
                alert('Booking cancelled successfully!');
                // In production, this should make an API call to cancel the booking
            }
        });
    });

    // Handle quick action buttons
    const actionCards = document.querySelectorAll('.action-card');
    actionCards.forEach(card => {
        card.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.querySelector('span').textContent;
            alert(`${action} feature coming soon!`);
        });
    });
});
