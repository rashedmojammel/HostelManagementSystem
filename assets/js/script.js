// =============================================
// Hostel Management System - Main JavaScript
// =============================================

// =============================================
// Form Validation Functions
// =============================================

/**
 * Validate email format
 * @param {string} email 
 * @returns {boolean}
 */
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Validate phone number (Bangladesh format: 01XXXXXXXXX)
 * @param {string} phone 
 * @returns {boolean}
 */
function validatePhone(phone) {
    const regex = /^01[3-9]\d{8}$/;
    return regex.test(phone);
}

/**
 * Validate password strength
 * @param {string} password 
 * @returns {boolean}
 */
function validatePassword(password) {
    // At least 6 characters
    return password.length >= 6;
}

/**
 * Validate registration form
 * @param {Event} event 
 */
function validateRegistrationForm(event) {
    const fullName = document.getElementById('full_name').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    // Check if all fields are filled
    if (!fullName || !email || !phone || !password || !confirmPassword) {
        alert('Please fill in all fields');
        event.preventDefault();
        return false;
    }
    
    // Validate email
    if (!validateEmail(email)) {
        alert('Please enter a valid email address');
        event.preventDefault();
        return false;
    }
    
    // Validate phone
    if (!validatePhone(phone)) {
        alert('Please enter a valid phone number (01XXXXXXXXX)');
        event.preventDefault();
        return false;
    }
    
    // Validate password
    if (!validatePassword(password)) {
        alert('Password must be at least 6 characters long');
        event.preventDefault();
        return false;
    }
    
    // Check if passwords match
    if (password !== confirmPassword) {
        alert('Passwords do not match');
        event.preventDefault();
        return false;
    }
    
    return true;
}

/**
 * Validate login form
 * @param {Event} event 
 */
function validateLoginForm(event) {
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    
    // Check if all fields are filled
    if (!email || !password) {
        alert('Please fill in all fields');
        event.preventDefault();
        return false;
    }
    
    // Validate email
    if (!validateEmail(email)) {
        alert('Please enter a valid email address');
        event.preventDefault();
        return false;
    }
    
    return true;
}

// =============================================
// Confirmation Dialogs
// =============================================

/**
 * Confirm delete action
 * @param {string} message 
 * @returns {boolean}
 */
function confirmDelete(message = 'Are you sure you want to delete this?') {
    return confirm(message);
}

/**
 * Confirm action
 * @param {string} message 
 * @returns {boolean}
 */
function confirmAction(message) {
    return confirm(message);
}

// =============================================
// Alert Auto-hide
// =============================================

/**
 * Auto-hide alerts after 5 seconds
 */
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });
});

// =============================================
// Form Submit Handlers
// =============================================

/**
 * Handle booking form submission
 * @param {Event} event 
 */
function validateBookingForm(event) {
    const roomId = document.getElementById('room_id').value;
    const checkInDate = document.getElementById('check_in_date').value;
    
    if (!roomId) {
        alert('Please select a room');
        event.preventDefault();
        return false;
    }
    
    if (!checkInDate) {
        alert('Please select check-in date');
        event.preventDefault();
        return false;
    }
    
    // Check if date is not in the past
    const today = new Date().toISOString().split('T')[0];
    if (checkInDate < today) {
        alert('Check-in date cannot be in the past');
        event.preventDefault();
        return false;
    }
    
    return true;
}

/**
 * Handle complaint form submission
 * @param {Event} event 
 */
function validateComplaintForm(event) {
    const subject = document.getElementById('subject').value.trim();
    const description = document.getElementById('description').value.trim();
    
    if (!subject || !description) {
        alert('Please fill in all fields');
        event.preventDefault();
        return false;
    }
    
    if (description.length < 10) {
        alert('Description must be at least 10 characters long');
        event.preventDefault();
        return false;
    }
    
    return true;
}

// =============================================
// Dynamic Form Features
// =============================================

/**
 * Calculate total price based on room type and duration
 */
function calculateBookingPrice() {
    const roomSelect = document.getElementById('room_id');
    const checkInDate = document.getElementById('check_in_date');
    const checkOutDate = document.getElementById('check_out_date');
    const priceDisplay = document.getElementById('total_price');
    
    if (roomSelect && checkInDate && checkOutDate && priceDisplay) {
        const roomPrice = parseFloat(roomSelect.options[roomSelect.selectedIndex].dataset.price || 0);
        const checkIn = new Date(checkInDate.value);
        const checkOut = new Date(checkOutDate.value);
        
        if (checkInDate.value && checkOutDate.value && checkOut > checkIn) {
            const days = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
            const totalPrice = roomPrice * days;
            priceDisplay.textContent = `Total: ৳${totalPrice.toFixed(2)}`;
        } else {
            priceDisplay.textContent = 'Total: ৳0.00';
        }
    }
}

// =============================================
// Search and Filter Functions
// =============================================

/**
 * Filter table rows based on search input
 * @param {string} inputId - ID of search input
 * @param {string} tableId - ID of table to filter
 */
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    
    if (input && table) {
        input.addEventListener('keyup', function() {
            const filter = input.value.toUpperCase();
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length; j++) {
                    const cell = cells[j];
                    if (cell) {
                        const textValue = cell.textContent || cell.innerText;
                        if (textValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                rows[i].style.display = found ? '' : 'none';
            }
        });
    }
}

// =============================================
// Utility Functions
// =============================================

/**
 * Format number as currency
 * @param {number} amount 
 * @returns {string}
 */
function formatCurrency(amount) {
    return '৳' + parseFloat(amount).toFixed(2);
}

/**
 * Print current page
 */
function printPage() {
    window.print();
}

/**
 * Go back to previous page
 */
function goBack() {
    window.history.back();
}
