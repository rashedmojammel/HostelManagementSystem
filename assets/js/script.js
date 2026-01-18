// =============================================
// Campus Nest - Hostel Management System
// Complete JavaScript Validation & Features
// =============================================

'use strict';

// =============================================
// Validation Functions
// =============================================

/**
 * Validate email format
 */
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Validate phone number (Bangladesh format)
 * Accepts: 01712345678, +8801712345678, 8801712345678
 */
function validatePhone(phone) {
    const regex = /^(\+?880|0)?1[3-9]\d{8}$/;
    return regex.test(phone.replace(/[\s-]/g, ''));
}

/**
 * Check password strength
 */
function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    
    const strengthLevels = [
        { text: 'Very Weak', color: '#ef4444', score: 1 },
        { text: 'Weak', color: '#f59e0b', score: 2 },
        { text: 'Fair', color: '#eab308', score: 3 },
        { text: 'Good', color: '#10b981', score: 4 },
        { text: 'Strong', color: '#059669', score: 5 },
        { text: 'Very Strong', color: '#047857', score: 6 }
    ];
    
    return strengthLevels[strength - 1] || strengthLevels[0];
}

/**
 * Validate required fields
 */
function validateRequired(value, fieldName) {
    if (!value || value.trim() === '') {
        return { valid: false, message: `${fieldName} is required` };
    }
    return { valid: true };
}

/**
 * Validate minimum length
 */
function validateMinLength(value, minLength, fieldName) {
    if (value.length < minLength) {
        return { 
            valid: false, 
            message: `${fieldName} must be at least ${minLength} characters` 
        };
    }
    return { valid: true };
}

/**
 * Validate maximum length
 */
function validateMaxLength(value, maxLength, fieldName) {
    if (value.length > maxLength) {
        return { 
            valid: false, 
            message: `${fieldName} must not exceed ${maxLength} characters` 
        };
    }
    return { valid: true };
}

/**
 * Validate number range
 */
function validateNumberRange(value, min, max, fieldName) {
    const num = parseFloat(value);
    if (isNaN(num) || num < min || num > max) {
        return { 
            valid: false, 
            message: `${fieldName} must be between ${min} and ${max}` 
        };
    }
    return { valid: true };
}

/**
 * Validate date (not in past)
 */
function validateFutureDate(dateValue, fieldName) {
    const selectedDate = new Date(dateValue);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        return { 
            valid: false, 
            message: `${fieldName} cannot be in the past` 
        };
    }
    return { valid: true };
}

// =============================================
// UI Helper Functions
// =============================================

/**
 * Show error message for a field
 */
function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    // Remove existing error
    clearError(fieldId);
    
    // Add error styling
    field.style.borderColor = '#ef4444';
    field.style.backgroundColor = '#fef2f2';
    
    // Create error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.color = '#ef4444';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    errorDiv.style.fontWeight = '500';
    errorDiv.innerHTML = `<span style="margin-right: 0.25rem;">⚠️</span>${message}`;
    errorDiv.id = fieldId + '_error';
    
    field.parentElement.appendChild(errorDiv);
}

/**
 * Clear error message for a field
 */
function clearError(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    field.style.borderColor = '';
    field.style.backgroundColor = '';
    
    const errorMsg = document.getElementById(fieldId + '_error');
    if (errorMsg) {
        errorMsg.remove();
    }
}

/**
 * Show success message for a field
 */
function showSuccess(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    clearError(fieldId);
    field.style.borderColor = '#10b981';
}

/**
 * Show password strength indicator
 */
function showPasswordStrength(strength) {
    let strengthDiv = document.getElementById('password-strength');
    
    if (!strengthDiv) {
        strengthDiv = document.createElement('div');
        strengthDiv.id = 'password-strength';
        strengthDiv.style.marginTop = '0.5rem';
        strengthDiv.style.fontSize = '0.875rem';
        
        const passwordField = document.getElementById('password');
        if (passwordField && passwordField.parentElement) {
            passwordField.parentElement.appendChild(strengthDiv);
        }
    }
    
    // Create strength bars
    const bars = [1, 2, 3, 4, 5, 6].map(i => {
        const isActive = i <= strength.score;
        return `<div style="flex: 1; height: 5px; background: ${
            isActive ? strength.color : '#e5e7eb'
        }; border-radius: 3px; transition: all 0.3s;"></div>`;
    }).join('');
    
    strengthDiv.innerHTML = `
        <div style="display: flex; gap: 0.25rem; margin-bottom: 0.5rem;">
            ${bars}
        </div>
        <span style="color: ${strength.color}; font-weight: 600;">
            Password Strength: ${strength.text}
        </span>
    `;
}

/**
 * Show loading spinner on button
 */
function showLoading(buttonId) {
    const button = document.getElementById(buttonId);
    if (!button) return;
    
    button.disabled = true;
    button.dataset.originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner"></span> Processing...';
    button.style.opacity = '0.7';
}

/**
 * Hide loading spinner on button
 */
function hideLoading(buttonId) {
    const button = document.getElementById(buttonId);
    if (!button) return;
    
    button.disabled = false;
    button.innerHTML = button.dataset.originalText || 'Submit';
    button.style.opacity = '1';
}

// =============================================
// Form Validation Handlers
// =============================================

/**
 * Validate Registration Form
 */
function validateRegistrationForm(form) {
    let isValid = true;
    const errors = [];
    
    // Get field values
    const fullName = document.getElementById('full_name')?.value.trim();
    const email = document.getElementById('email')?.value.trim();
    const phone = document.getElementById('phone')?.value.trim();
    const password = document.getElementById('password')?.value;
    const confirmPassword = document.getElementById('confirm_password')?.value;
    
    // Full Name validation
    let validation = validateRequired(fullName, 'Full name');
    if (!validation.valid) {
        showError('full_name', validation.message);
        errors.push(validation.message);
        isValid = false;
    } else {
        validation = validateMinLength(fullName, 3, 'Full name');
        if (!validation.valid) {
            showError('full_name', validation.message);
            errors.push(validation.message);
            isValid = false;
        } else {
            showSuccess('full_name');
        }
    }
    
    // Email validation
    validation = validateRequired(email, 'Email');
    if (!validation.valid) {
        showError('email', validation.message);
        errors.push(validation.message);
        isValid = false;
    } else if (!validateEmail(email)) {
        showError('email', 'Please enter a valid email address');
        errors.push('Invalid email format');
        isValid = false;
    } else {
        showSuccess('email');
    }
    
    // Phone validation
    validation = validateRequired(phone, 'Phone number');
    if (!validation.valid) {
        showError('phone', validation.message);
        errors.push(validation.message);
        isValid = false;
    } else if (!validatePhone(phone)) {
        showError('phone', 'Please enter a valid Bangladesh phone number (e.g., 01712345678)');
        errors.push('Invalid phone number');
        isValid = false;
    } else {
        showSuccess('phone');
    }
    
    // Password validation
    validation = validateRequired(password, 'Password');
    if (!validation.valid) {
        showError('password', validation.message);
        errors.push(validation.message);
        isValid = false;
    } else {
        validation = validateMinLength(password, 6, 'Password');
        if (!validation.valid) {
            showError('password', validation.message);
            errors.push(validation.message);
            isValid = false;
        } else {
            showSuccess('password');
        }
    }
    
    // Confirm Password validation
    validation = validateRequired(confirmPassword, 'Confirm password');
    if (!validation.valid) {
        showError('confirm_password', validation.message);
        errors.push(validation.message);
        isValid = false;
    } else if (password !== confirmPassword) {
        showError('confirm_password', 'Passwords do not match');
        errors.push('Passwords do not match');
        isValid = false;
    } else {
        showSuccess('confirm_password');
    }
    
    return isValid;
}

/**
 * Validate Login Form
 */
function validateLoginForm(form) {
    let isValid = true;
    
    const email = document.getElementById('email')?.value.trim();
    const password = document.getElementById('password')?.value;
    
    // Email validation
    let validation = validateRequired(email, 'Email');
    if (!validation.valid) {
        showError('email', validation.message);
        isValid = false;
    } else if (!validateEmail(email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    } else {
        showSuccess('email');
    }
    
    // Password validation
    validation = validateRequired(password, 'Password');
    if (!validation.valid) {
        showError('password', validation.message);
        isValid = false;
    } else {
        showSuccess('password');
    }
    
    return isValid;
}

/**
 * Validate Room Form (Admin)
 */
function validateRoomForm(form) {
    let isValid = true;
    
    const roomNumber = document.getElementById('room_number')?.value.trim();
    const roomType = document.getElementById('room_type')?.value;
    const capacity = document.getElementById('capacity')?.value;
    const price = document.getElementById('price')?.value;
    const floorNumber = document.getElementById('floor_number')?.value;
    
    // Room Number
    let validation = validateRequired(roomNumber, 'Room number');
    if (!validation.valid) {
        showError('room_number', validation.message);
        isValid = false;
    } else {
        showSuccess('room_number');
    }
    
    // Room Type
    if (!roomType) {
        showError('room_type', 'Please select a room type');
        isValid = false;
    } else {
        showSuccess('room_type');
    }
    
    // Capacity
    validation = validateNumberRange(capacity, 1, 10, 'Capacity');
    if (!validation.valid) {
        showError('capacity', validation.message);
        isValid = false;
    } else {
        showSuccess('capacity');
    }
    
    // Price
    validation = validateNumberRange(price, 100, 100000, 'Price');
    if (!validation.valid) {
        showError('price', validation.message);
        isValid = false;
    } else {
        showSuccess('price');
    }
    
    // Floor Number
    validation = validateNumberRange(floorNumber, 1, 20, 'Floor number');
    if (!validation.valid) {
        showError('floor_number', validation.message);
        isValid = false;
    } else {
        showSuccess('floor_number');
    }
    
    return isValid;
}

/**
 * Validate Complaint Form
 */
function validateComplaintForm(form) {
    let isValid = true;
    
    const complaintType = document.getElementById('complaint_type')?.value;
    const subject = document.getElementById('subject')?.value.trim();
    const description = document.getElementById('description')?.value.trim();
    
    // Complaint Type
    if (!complaintType) {
        showError('complaint_type', 'Please select a complaint type');
        isValid = false;
    } else {
        showSuccess('complaint_type');
    }
    
    // Subject
    let validation = validateRequired(subject, 'Subject');
    if (!validation.valid) {
        showError('subject', validation.message);
        isValid = false;
    } else {
        validation = validateMinLength(subject, 5, 'Subject');
        if (!validation.valid) {
            showError('subject', validation.message);
            isValid = false;
        } else {
            showSuccess('subject');
        }
    }
    
    // Description
    validation = validateRequired(description, 'Description');
    if (!validation.valid) {
        showError('description', validation.message);
        isValid = false;
    } else {
        validation = validateMinLength(description, 10, 'Description');
        if (!validation.valid) {
            showError('description', validation.message);
            isValid = false;
        } else {
            showSuccess('description');
        }
    }
    
    return isValid;
}

/**
 * Validate Booking Form
 */
function validateBookingForm(form) {
    let isValid = true;
    
    const checkInDate = document.getElementById('check_in_date')?.value;
    
    // Check-in Date
    let validation = validateRequired(checkInDate, 'Check-in date');
    if (!validation.valid) {
        showError('check_in_date', validation.message);
        isValid = false;
    } else {
        validation = validateFutureDate(checkInDate, 'Check-in date');
        if (!validation.valid) {
            showError('check_in_date', validation.message);
            isValid = false;
        } else {
            showSuccess('check_in_date');
        }
    }
    
    return isValid;
}

/**
 * Validate Event Form
 */
function validateEventForm(form) {
    let isValid = true;
    
    const title = document.getElementById('title')?.value.trim();
    const eventDate = document.getElementById('event_date')?.value;
    const eventTime = document.getElementById('event_time')?.value;
    const location = document.getElementById('location')?.value.trim();
    const description = document.getElementById('description')?.value.trim();
    
    // Title
    let validation = validateRequired(title, 'Title');
    if (!validation.valid) {
        showError('title', validation.message);
        isValid = false;
    } else {
        showSuccess('title');
    }
    
    // Event Date
    validation = validateRequired(eventDate, 'Event date');
    if (!validation.valid) {
        showError('event_date', validation.message);
        isValid = false;
    } else {
        showSuccess('event_date');
    }
    
    // Event Time
    validation = validateRequired(eventTime, 'Event time');
    if (!validation.valid) {
        showError('event_time', validation.message);
        isValid = false;
    } else {
        showSuccess('event_time');
    }
    
    // Location
    validation = validateRequired(location, 'Location');
    if (!validation.valid) {
        showError('location', validation.message);
        isValid = false;
    } else {
        showSuccess('location');
    }
    
    // Description
    validation = validateRequired(description, 'Description');
    if (!validation.valid) {
        showError('description', validation.message);
        isValid = false;
    } else {
        showSuccess('description');
    }
    
    return isValid;
}

/**
 * Validate Payment Form
 */
function validatePaymentForm(form) {
    let isValid = true;
    
    const paymentMethod = document.getElementById('payment_method')?.value;
    const transactionId = document.getElementById('transaction_id')?.value.trim();
    
    // Payment Method
    if (!paymentMethod) {
        showError('payment_method', 'Please select a payment method');
        isValid = false;
    } else {
        showSuccess('payment_method');
    }
    
    // Transaction ID
    let validation = validateRequired(transactionId, 'Transaction ID');
    if (!validation.valid) {
        showError('transaction_id', validation.message);
        isValid = false;
    } else {
        validation = validateMinLength(transactionId, 5, 'Transaction ID');
        if (!validation.valid) {
            showError('transaction_id', validation.message);
            isValid = false;
        } else {
            showSuccess('transaction_id');
        }
    }
    
    return isValid;
}

/**
 * Validate Profile Update Form
 */
function validateProfileForm(form) {
    let isValid = true;
    
    const fullName = document.getElementById('full_name')?.value.trim();
    const email = document.getElementById('email')?.value.trim();
    const phone = document.getElementById('phone')?.value.trim();
    
    // Full Name
    let validation = validateRequired(fullName, 'Full name');
    if (!validation.valid) {
        showError('full_name', validation.message);
        isValid = false;
    } else {
        showSuccess('full_name');
    }
    
    // Email
    validation = validateRequired(email, 'Email');
    if (!validation.valid) {
        showError('email', validation.message);
        isValid = false;
    } else if (!validateEmail(email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    } else {
        showSuccess('email');
    }
    
    // Phone
    validation = validateRequired(phone, 'Phone');
    if (!validation.valid) {
        showError('phone', validation.message);
        isValid = false;
    } else if (!validatePhone(phone)) {
        showError('phone', 'Please enter a valid phone number');
        isValid = false;
    } else {
        showSuccess('phone');
    }
    
    return isValid;
}

/**
 * Validate Password Change Form
 */
function validatePasswordChangeForm(form) {
    let isValid = true;
    
    const currentPassword = document.getElementById('current_password')?.value;
    const newPassword = document.getElementById('new_password')?.value;
    const confirmPassword = document.getElementById('confirm_password')?.value;
    
    // Current Password
    let validation = validateRequired(currentPassword, 'Current password');
    if (!validation.valid) {
        showError('current_password', validation.message);
        isValid = false;
    } else {
        showSuccess('current_password');
    }
    
    // New Password
    validation = validateRequired(newPassword, 'New password');
    if (!validation.valid) {
        showError('new_password', validation.message);
        isValid = false;
    } else {
        validation = validateMinLength(newPassword, 6, 'New password');
        if (!validation.valid) {
            showError('new_password', validation.message);
            isValid = false;
        } else if (newPassword === currentPassword) {
            showError('new_password', 'New password must be different from current password');
            isValid = false;
        } else {
            showSuccess('new_password');
        }
    }
    
    // Confirm Password
    validation = validateRequired(confirmPassword, 'Confirm password');
    if (!validation.valid) {
        showError('confirm_password', validation.message);
        isValid = false;
    } else if (newPassword !== confirmPassword) {
        showError('confirm_password', 'Passwords do not match');
        isValid = false;
    } else {
        showSuccess('confirm_password');
    }
    
    return isValid;
}

// =============================================
// Event Listeners & DOM Ready
// =============================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================
    // Registration Form Validation
    // ==========================================
    const registerForm = document.querySelector('form[action*="register"]');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            if (!validateRegistrationForm(this)) {
                e.preventDefault();
            }
        });
        
        // Real-time password strength indicator
        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const strength = checkPasswordStrength(this.value);
                showPasswordStrength(strength);
            });
        }
        
        // Real-time confirm password matching
        const confirmPasswordInput = document.getElementById('confirm_password');
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                const password = document.getElementById('password')?.value;
                if (this.value && password !== this.value) {
                    showError('confirm_password', 'Passwords do not match');
                } else if (this.value) {
                    clearError('confirm_password');
                    showSuccess('confirm_password');
                }
            });
        }
    }
    
    // ==========================================
    // Login Form Validation
    // ==========================================
    const loginForm = document.querySelector('form[action*="login"]');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            if (!validateLoginForm(this)) {
                e.preventDefault();
            }
        });
    }
    
    // ==========================================
    // Room Form Validation
    // ==========================================
    const roomForm = document.querySelector('form[name="room_form"]');
    if (roomForm) {
        roomForm.addEventListener('submit', function(e) {
            if (!validateRoomForm(this)) {
                e.preventDefault();
            }
        });
    }
    
    // ==========================================
    // Complaint Form Validation
    // ==========================================
    const complaintForm = document.querySelector('form[name="complaint_form"]');
    if (complaintForm) {
        complaintForm.addEventListener('submit', function(e) {
            if (!validateComplaintForm(this)) {
                e.preventDefault();
            }
        });
        
        // Character counter for description
        const descriptionField = document.getElementById('description');
        if (descriptionField) {
            descriptionField.addEventListener('input', function() {
                const charCount = this.value.length;
                let counterDiv = document.getElementById('char-counter');
                
                if (!counterDiv) {
                    counterDiv = document.createElement('div');
                    counterDiv.id = 'char-counter';
                    counterDiv.style.fontSize = '0.875rem';
                    counterDiv.style.marginTop = '0.25rem';
                    counterDiv.style.color = '#6b7280';
                    this.parentElement.appendChild(counterDiv);
                }
                
                counterDiv.textContent = `${charCount} characters (minimum 10 required)`;
                counterDiv.style.color = charCount >= 10 ? '#10b981' : '#6b7280';
            });
        }
    }
    
    // ==========================================
    // Booking Form Validation
    // ==========================================
    const bookingForm = document.querySelector('form[name="booking_form"]');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            if (!validateBookingForm(this)) {
                e.preventDefault();
            }
        });
    }
    
    // ==========================================
    // Event Form Validation
    // ==========================================
    const eventForm = document.querySelector('form[name="event_form"]');
    if (eventForm) {
        eventForm.addEventListener('submit', function(e) {
            if (!validateEventForm(this)) {
                e.preventDefault();
            }
        });
    }
    
    // ==========================================
    // Payment Form Validation
    // ==========================================
    const paymentForm = document.querySelector('form[name="payment_form"]');
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            if (!validatePaymentForm(this)) {
                e.preventDefault();
            }
        });
    }
    
    // ==========================================
    // Profile Update Form Validation
    // ==========================================
    const profileForm = document.querySelector('form[name="profile_form"]');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            if (!validateProfileForm(this)) {
                e.preventDefault();
            }
        });
    }
    
    // ==========================================
    // Password Change Form Validation
    // ==========================================
    const passwordForm = document.querySelector('form[name="password_form"]');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            if (!validatePasswordChangeForm(this)) {
                e.preventDefault();
            }
        });
    }
    
    // ==========================================
    // Real-time Email Validation
    // ==========================================
    const emailInputs = document.querySelectorAll('input[type="email"], input[name="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !validateEmail(this.value)) {
                showError(this.id, 'Invalid email format');
            } else if (this.value) {
                clearError(this.id);
                showSuccess(this.id);
            }
        });
    });
    
    // ==========================================
    // Real-time Phone Validation
    // ==========================================
    const phoneInputs = document.querySelectorAll('input[name="phone"]');
    phoneInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !validatePhone(this.value)) {
                showError(this.id, 'Invalid phone number (use format: 01712345678)');
            } else if (this.value) {
                clearError(this.id);
                showSuccess(this.id);
            }
        });
    });
    
    // ==========================================
    // Auto-hide Success/Error Alerts
    // ==========================================
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.remove();
                }
            }, 500);
        }, 5000); // Hide after 5 seconds
    });
    
    // ==========================================
    // Confirm Before Delete Actions
    // ==========================================
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const itemType = this.dataset.confirmDelete || 'item';
            if (!confirm(`Are you sure you want to delete this ${itemType}? This action cannot be undone.`)) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // ==========================================
    // Table Search Functionality
    // ==========================================
    const searchInputs = document.querySelectorAll('.table-search');
    searchInputs.forEach(searchInput => {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tableId = this.dataset.table;
            const table = document.getElementById(tableId);
            
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            }
        });
    });
    
    // ==========================================
    // Smooth Scroll for Anchor Links
    // ==========================================
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId !== '#' && targetId !== '#!') {
                const target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });
    
    // ==========================================
    // Print Functionality
    // ==========================================
    const printButtons = document.querySelectorAll('[data-print]');
    printButtons.forEach(button => {
        button.addEventListener('click', function() {
            window.print();
        });
    });
    
    // ==========================================
    // Tooltips (Simple Implementation)
    // ==========================================
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltipText = this.dataset.tooltip;
            const tooltip = document.createElement('div');
            tooltip.className = 'custom-tooltip';
            tooltip.textContent = tooltipText;
            tooltip.style.cssText = `
                position: absolute;
                background: #1f2937;
                color: white;
                padding: 0.5rem 0.75rem;
                border-radius: 0.375rem;
                font-size: 0.875rem;
                z-index: 1000;
                pointer-events: none;
            `;
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
            tooltip.style.left = (rect.left + (rect.width - tooltip.offsetWidth) / 2) + 'px';
            
            this.dataset.tooltipId = 'tooltip_' + Date.now();
            tooltip.id = this.dataset.tooltipId;
        });
        
        element.addEventListener('mouseleave', function() {
            const tooltipId = this.dataset.tooltipId;
            if (tooltipId) {
                const tooltip = document.getElementById(tooltipId);
                if (tooltip) {
                    tooltip.remove();
                }
            }
        });
    });
});

// =============================================
// Utility Functions
// =============================================

/**
 * Confirm delete action
 */
function confirmDelete(itemType = 'item') {
    return confirm(`Are you sure you want to delete this ${itemType}? This action cannot be undone.`);
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return '৳' + parseFloat(amount).toLocaleString('en-BD', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Format date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}

/**
 * Debounce function for search
 */
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

// =============================================
// Export functions for use in other scripts
// =============================================
window.validateEmail = validateEmail;
window.validatePhone = validatePhone;
window.confirmDelete = confirmDelete;
window.showError = showError;
window.clearError = clearError;
window.showSuccess = showSuccess;
window.formatCurrency = formatCurrency;
window.formatDate = formatDate;
window.showLoading = showLoading;
window.hideLoading = hideLoading;

console.log('✅ Campus Nest JavaScript loaded successfully!');
