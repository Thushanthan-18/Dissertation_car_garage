// Wait for the page to load
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

                // Update active nav link
                document.querySelectorAll('.nav-links a').forEach(link => {
                    link.classList.remove('active');
                });
                this.classList.add('active');
            }
        });
    });

    // Service detail toggle
    document.querySelectorAll('.service-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetDetail = document.querySelector(targetId);

            // Hide all service details first
            document.querySelectorAll('.service-detail').forEach(detail => {
                detail.classList.remove('active');
            });

            // Show the clicked service detail
            if (targetDetail) {
                targetDetail.classList.add('active');
                targetDetail.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Show loading animation for 2 seconds
    setTimeout(() => {
        document.querySelector('.loading-screen').style.display = 'none';
        document.querySelector('.main-content').classList.remove('hidden');
    }, 2000);

    // Fake user database
    const users = [
        { username: 'demo', password: 'password123', fullName: 'Demo User', email: 'demo@example.com' }
    ];

    let currentUser = null;

    // Modal handling
    const loginBtn = document.getElementById('loginBtn');
    const loginModal = document.getElementById('loginModal');
    const registerModal = document.getElementById('registerModal');
    const closeBtns = document.querySelectorAll('.close');

    // Password toggle buttons
    const togglePassword = document.getElementById('togglePassword');
    const toggleRegPassword = document.getElementById('toggleRegPassword');
    const toggleRegConfirmPassword = document.getElementById('toggleRegConfirmPassword');

    // Password inputs
    const passwordInput = document.getElementById('password');
    const regPasswordInput = document.getElementById('regPassword');
    const regConfirmPasswordInput = document.getElementById('regConfirmPassword');


    function updateAuthButton() {
        const loginBtn = document.getElementById('loginBtn');
        if (currentUser) {
            loginBtn.textContent = currentUser.fullName;
            // Add logout option
            if (!document.querySelector('.user-menu')) {
                const userMenu = document.createElement('div');
                userMenu.className = 'user-menu';
                userMenu.innerHTML = '<a href="#" id="logoutBtn">Logout</a>';
                loginBtn.parentElement.appendChild(userMenu);

                loginBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    userMenu.classList.toggle('active');
                });

                document.getElementById('logoutBtn').addEventListener('click', (e) => {
                    e.preventDefault();
                    logout();
                });

                // Close menu when clicking outside
                document.addEventListener('click', () => {
                    userMenu.classList.remove('active');
                });
            }
        } else {
            loginBtn.textContent = 'Login';
            const userMenu = document.querySelector('.user-menu');
            if (userMenu) userMenu.remove();
        }
    }

    // Modal controls
    loginBtn.addEventListener('click', () => {
        if (!currentUser) {
            loginModal.style.display = 'block';
        }
    });

    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const modalId = btn.getAttribute('data-modal');
            document.getElementById(modalId).style.display = 'none';
        });
    });

    // Close modals when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === loginModal) loginModal.style.display = 'none';
        if (e.target === registerModal) registerModal.style.display = 'none';
    });

    // Toggle password visibility
    function setupPasswordToggle(toggleBtn, passwordInput) {
        toggleBtn.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            toggleBtn.classList.toggle('fa-eye');
            toggleBtn.classList.toggle('fa-eye-slash');
        });
    }

    setupPasswordToggle(togglePassword, passwordInput);
    setupPasswordToggle(toggleRegPassword, regPasswordInput);
    setupPasswordToggle(toggleRegConfirmPassword, regConfirmPasswordInput);

    // Show message in form
    function showMessage(formId, message, type) {
        const messageDiv = document.querySelector(`#${formId} .form-message`);
        messageDiv.textContent = message;
        messageDiv.className = `form-message ${type}`;
    }

    // Handle login
    document.getElementById('loginForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const username = document.getElementById('username').value;
        const password = passwordInput.value;
        
        const user = users.find(u => u.username === username && u.password === password);
        
        if (user) {
            currentUser = user;
            showMessage('loginForm', 'Login successful!', 'success');
            setTimeout(() => {
                loginModal.style.display = 'none';
                updateAuthButton();
            }, 1500);
        } else {
            showMessage('loginForm', 'Invalid username or password', 'error');
        }
    });

    // Handle registration
    document.getElementById('registerForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const newUser = {
            fullName: document.getElementById('regFullName').value,
            email: document.getElementById('regEmail').value,
            username: document.getElementById('regUsername').value,
            password: document.getElementById('regPassword').value
        };

        const confirmPassword = document.getElementById('regConfirmPassword').value;

        if (newUser.password !== confirmPassword) {
            showMessage('registerForm', 'Passwords do not match', 'error');
            return;
        }

        if (users.some(u => u.username === newUser.username)) {
            showMessage('registerForm', 'Username already exists', 'error');
            return;
        }

        users.push(newUser);
        showMessage('registerForm', 'Registration successful! You can now login.', 'success');
        
        setTimeout(() => {
            registerModal.style.display = 'none';
            loginModal.style.display = 'block';
        }, 1500);
    });

    // Switch between login and registration
    document.getElementById('createAccount').addEventListener('click', (e) => {
        e.preventDefault();
        loginModal.style.display = 'none';
        registerModal.style.display = 'block';
    });

    document.getElementById('backToLogin').addEventListener('click', (e) => {
        e.preventDefault();
        registerModal.style.display = 'none';
        loginModal.style.display = 'block';
    });

    // Logout function
    function logout() {
        currentUser = null;
        updateAuthButton();
    }

    // Handle contact form submission
    document.getElementById('contactForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = {
            fullName: document.getElementById('fullName').value,
            vehicleReg: document.getElementById('vehicleReg').value,
            contactNumber: document.getElementById('contactNumber').value,
            message: document.getElementById('message').value
        };
        
        // Here you would typically send the form data to your server
        console.log('Contact form submitted:', formData);
        alert('Thank you for your message. We will get back to you soon!');
        e.target.reset();
    });
});

// Initialize Google Maps
function initMap() {
    // Replace these coordinates with your garage's actual location
    const garageLocation = { lat: 51.5074, lng: -0.1278 }; // London coordinates

    const map = new google.maps.Map(document.getElementById('map'), {
        zoom: 15,
        center: garageLocation,
        styles: [
            {
                featureType: 'poi',
                elementType: 'labels',
                stylers: [{ visibility: 'off' }]
            }
        ]
    });

    // Add a marker for the garage location
    const marker = new google.maps.Marker({
        position: garageLocation,
        map: map,
        title: 'AutoCare Pro'
    });

    // Add an info window
    const infoWindow = new google.maps.InfoWindow({
        content: '<div style="padding: 10px;"><h3>AutoCare Pro</h3><p>123 Garage Street<br>London, UK</p></div>'
    });

    marker.addListener('click', () => {
        infoWindow.open(map, marker);
    });
};
function acceptCookies() {
    document.getElementById('cookie-banner').style.display = 'none';
    localStorage.setItem('cookiesAccepted', 'true');
}

window.onload = function() {
    if (localStorage.getItem('cookiesAccepted') === 'true') {
        document.getElementById('cookie-banner').style.display = 'none';
    }
};

