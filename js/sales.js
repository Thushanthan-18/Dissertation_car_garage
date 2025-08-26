document.addEventListener('DOMContentLoaded', function() {
    // Get all filter elements
    const searchInput = document.getElementById('carSearch');
    const typeFilter = document.getElementById('typeFilter');
    const makeFilter = document.getElementById('makeFilter');
    const priceFilter = document.getElementById('priceFilter');
    const carsGrid = document.querySelector('.cars-grid');

    // Function to filter cars
    function filterCars() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedType = typeFilter.value;
        const selectedMake = makeFilter.value;
        const selectedPrice = priceFilter.value;

        // Get all car cards
        const carCards = document.querySelectorAll('.car-card');

        carCards.forEach(card => {
            let showCard = true;

            // Search term filter
            const cardText = card.textContent.toLowerCase();
            if (!cardText.includes(searchTerm)) {
                showCard = false;
            }

            // Type filter
            if (selectedType !== 'all' && card.dataset.type !== selectedType) {
                showCard = false;
            }

            // Make filter
            if (selectedMake !== 'all' && card.dataset.make !== selectedMake) {
                showCard = false;
            }

            // Price filter
            if (selectedPrice !== 'all') {
                const cardPrice = parseInt(card.dataset.price);
                const [minPrice, maxPrice] = selectedPrice.split('-').map(price => 
                    price === '+' ? Infinity : parseInt(price)
                );

                if (cardPrice < minPrice || cardPrice > maxPrice) {
                    showCard = false;
                }
            }

            // Show or hide card
            card.style.display = showCard ? 'block' : 'none';
        });

        // Show message if no results
        const noResults = document.querySelector('.no-results');
        const visibleCards = document.querySelectorAll('.car-card[style="display: block"]');
        
        if (visibleCards.length === 0) {
            if (!noResults) {
                const message = document.createElement('div');
                message.className = 'no-results';
                message.textContent = 'No cars match your search criteria.';
                carsGrid.appendChild(message);
            }
        } else {
            if (noResults) {
                noResults.remove();
            }
        }
    }

    // Add event listeners to filters
    searchInput.addEventListener('input', filterCars);
    typeFilter.addEventListener('change', filterCars);
    makeFilter.addEventListener('change', filterCars);
    priceFilter.addEventListener('change', filterCars);

    // Handle booking buttons
    document.querySelectorAll('.book-test-drive-btn, .book-rental-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            const carName = this.closest('.car-card').querySelector('h3').textContent;
            const isRental = this.classList.contains('book-rental-btn');
            const message = isRental 
                ? `Book rental for ${carName}`
                : `Book test drive for ${carName}`;
            
            // Scroll to contact form
            const contactSection = document.querySelector('#contact');
            if (contactSection) {
                contactSection.scrollIntoView({ behavior: 'smooth' });
                
                // Pre-fill message in contact form if it exists
                const messageInput = document.querySelector('#message');
                if (messageInput) {
                    messageInput.value = message;
                }
            }
        });
    });
});
