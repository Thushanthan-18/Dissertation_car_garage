document.addEventListener('DOMContentLoaded', function() {
    // Handle login form submission
    const loginForm = document.getElementById('adminLoginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const staffId = document.getElementById('staffId').value;
            const password = document.getElementById('password').value;
            
            // For demo purposes, using hardcoded credentials
            // In production, this should be replaced with proper authentication
            if (staffId === 'ST1234' && password === 'admin123') {
                window.location.href = 'dashboard.html';
            } else {
                alert('Invalid credentials. Please try again.');
            }
        });

        // Toggle password visibility
        const togglePassword = document.querySelector('.toggle-password');
        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const passwordInput = document.getElementById('password');
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
    }

    // Handle notifications
    const notificationBtn = document.querySelector('.notification-btn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function() {
            alert('Notifications feature coming soon!');
        });
    }

    // Handle settings
    const settingsBtn = document.querySelector('.settings-btn');
    if (settingsBtn) {
        settingsBtn.addEventListener('click', function() {
            alert('Settings feature coming soon!');
        });
    }
    
    const logoutBtn = document.querySelector('.logout-btn');
    if (logoutBtn) {
    logoutBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (confirm('Are you sure you want to logout?')) {
        window.location.href = '/car_garage/admin/login_a.php';
        }
    });
    }


    // Handle search
    const searchInput = document.querySelector('.header-search input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            // Add search functionality here
            console.log('Searching for:', this.value);
        });
    }
});
