// Main JavaScript file for ecommerce website

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle (if needed for responsive design)
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#dc3545';
                } else {
                    field.style.borderColor = '#ddd';
                }
            });
            
            // Password confirmation check
            const password = form.querySelector('input[name="password"]');
            const confirmPassword = form.querySelector('input[name="confirm_password"]');
            
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                isValid = false;
                confirmPassword.style.borderColor = '#dc3545';
                alert('Passwords do not match!');
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
    
    // Quantity input validation
    const quantityInputs = document.querySelectorAll('input[type="number"]');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const min = parseInt(this.min) || 0;
            const max = parseInt(this.max) || Infinity;
            const value = parseInt(this.value);
            
            if (value < min) {
                this.value = min;
            } else if (value > max) {
                this.value = max;
            }
        });
    });
    
    // Auto-hide success/error messages
    const alerts = document.querySelectorAll('.success, .error, .alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 300);
        }, 5000);
    });
    
    // Product image preview
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = input.parentNode.querySelector('.image-preview');
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.className = 'image-preview';
                        preview.style.marginTop = '10px';
                        input.parentNode.insertBefore(preview, input.nextSibling);
                    }
                    
                    preview.innerHTML = `
                        <p>Preview:</p>
                        <img src="${e.target.result}" style="max-width: 200px; height: auto; border: 1px solid #ddd; border-radius: 4px;">
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    });
    
    // Cart quantity updates
    const cartQuantityInputs = document.querySelectorAll('.cart-item-quantity input');
    cartQuantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const cartItemTotal = this.closest('.cart-item').querySelector('.cart-item-total');
            const price = parseFloat(this.closest('.cart-item').querySelector('.cart-item-price').textContent.replace('$', ''));
            const quantity = parseInt(this.value) || 0;
            const total = price * quantity;
            
            if (cartItemTotal) {
                cartItemTotal.textContent = '$' + total.toFixed(2);
            }
            
            updateCartTotal();
        });
    });
    
    // Update cart total
    function updateCartTotal() {
        const cartTotals = document.querySelectorAll('.cart-item-total');
        let total = 0;
        
        cartTotals.forEach(totalElement => {
            const value = parseFloat(totalElement.textContent.replace('$', '')) || 0;
            total += value;
        });
        
        const cartTotalElement = document.querySelector('.cart-total h3');
        if (cartTotalElement) {
            cartTotalElement.textContent = 'Total: $' + total.toFixed(2);
        }
    }
    
    // Search functionality
    const searchForm = document.querySelector('form[method="get"]');
    if (searchForm) {
        const searchInput = searchForm.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                // Add search suggestions or live search if needed
                // This is a placeholder for future enhancement
            });
        }
    }
    
    // Smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Loading states for buttons
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('form');
            if (form && form.checkValidity()) {
                this.innerHTML = 'Loading...';
                this.disabled = true;
            }
        });
    });
    
    // Product card hover effects (enhanced)
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Back to top button (optional)
    const backToTopButton = document.createElement('button');
    backToTopButton.innerHTML = '↑';
    backToTopButton.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        font-size: 20px;
        cursor: pointer;
        display: none;
        z-index: 1000;
        transition: opacity 0.3s;
    `;
    
    document.body.appendChild(backToTopButton);
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopButton.style.display = 'block';
        } else {
            backToTopButton.style.display = 'none';
        }
    });
    
    backToTopButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Console log for debugging (remove in production)
    console.log('Ecommerce website JavaScript loaded successfully!');
});

// Utility functions
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^[\+]?[1-9][\d]{0,15}$/;
    return re.test(phone.replace(/\s/g, ''));
}

// Export functions for use in other scripts
window.ecommerceUtils = {
    formatCurrency,
    validateEmail,
    validatePhone
};