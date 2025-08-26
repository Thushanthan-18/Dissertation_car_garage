document.addEventListener("DOMContentLoaded", function () {
    const tabButtons = document.querySelectorAll(".tab-btn");
    const forms = document.querySelectorAll(".auth-form");
  
    tabButtons.forEach((btn) => {
      btn.addEventListener("click", () => {
        tabButtons.forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");
  
        const target = btn.getAttribute("data-tab");
  
        forms.forEach((form) => {
          if (form.id === target + "Form") {
            form.classList.add("active");
          } else {
            form.classList.remove("active");
          }
        });
      });
    });
  });


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

    // social login buttons
    const socialBtns = document.querySelectorAll('.social-btn');
    if (socialBtns.length > 0) {
        socialBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                alert('Social login feature coming soon!');
            });
        });
    }

    //  notifications
    const notificationBtn = document.querySelector('.notification-btn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function() {
            alert('Notifications feature coming soon!');
        });
    }

    //  booking actions
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

    //  quick action buttons
    const actionCards = document.querySelectorAll('.action-card');
    actionCards.forEach(card => {
        card.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.querySelector('span').textContent;
            alert(`${action} feature coming soon!`);
        });
    });
