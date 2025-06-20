document.addEventListener('DOMContentLoaded', function() {
    const formSections = document.querySelectorAll('.form-section');
    const cakeOrderForm = document.getElementById('cakeOrderForm');
    const modal = document.getElementById('confirmationModal');
    const closeButton = document.querySelector('.close-button');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalOrderId = document.getElementById('modalOrderId');
    const modalProceedBtn = document.getElementById('modalProceedBtn');

    modal.style.display = 'none';

    let currentStep = 0;

    function showStep(step) {
        formSections.forEach((section, index) => {
            if (index === step) {
                section.classList.add('active');
            } else {
                section.classList.remove('active');
            }
        });
        currentStep = step;
    }

    function validateStep(step) {
        let isValid = true;
        const currentActiveSection = formSections[step];
        currentActiveSection.querySelectorAll('[required]').forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.reportValidity(); // This shows the browser's default validation message
            }
        });

        // MODIFIED: Validate only the single 'phone_number' input for step 0
        // The 'area_code' input no longer exists, so remove references to it.
        if (step === 0) {
            const phoneNumberInput = document.getElementById('phone_number');
            if (phoneNumberInput) { // Ensure the element exists before checking validity
                if (!phoneNumberInput.checkValidity()) { // This checks its pattern="[0-9]{10}"
                    isValid = false;
                }
            }
        }
        return isValid;
    }

    // Navigation buttons
    document.querySelectorAll('.next-btn').forEach(button => {
        button.addEventListener('click', function() {
            if (validateStep(currentStep)) {
                if (currentStep < formSections.length - 1) {
                    showStep(currentStep + 1);
                }
            } else {
                // Validation message is already shown by input.reportValidity()
            }
        });
    });

    document.querySelectorAll('.prev-btn').forEach(button => {
        button.addEventListener('click', function() {
            if (currentStep > 0) {
                showStep(currentStep - 1);
            }
        });
    });

    // Form Submission using Fetch API
    cakeOrderForm.addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission

        // Crucial: Validate the FINAL step before submission
        if (!validateStep(currentStep)) {
            alert('Please fill in all required fields before confirming your order.');
            return; // Stop submission if validation fails
        }

        const orderId = generateOrderId();
        const formData = new FormData(this);
        formData.append('order_id', orderId);

        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                // Log the raw response if it's not OK to see server error details
                return response.text().then(text => { throw new Error(`HTTP error! status: ${response.status}, message: ${text}`); });
            }
            return response.json(); // Expecting JSON response from submit.php
        })
        .then(data => {
            if (data.success) {
                modalTitle.textContent = 'Order Confirmed!';
                modalMessage.textContent = 'Your order has been successfully placed.';
                modalOrderId.textContent = `Order ID: ${data.orderId}`;
                modal.style.display = 'flex'; // Show the modal
            } else {
                modalTitle.textContent = 'Order Failed';
                modalMessage.textContent = data.message || 'There was an error processing your order. Please try again.';
                modalOrderId.textContent = '';
                modal.style.display = 'flex';
            }
        })
        .catch(error => {
            console.error('Error during form submission:', error);
            modalTitle.textContent = 'Submission Error';
            modalMessage.textContent = `A network or server error occurred. Details: ${error.message}`;
            modalOrderId.textContent = '';
            modal.style.display = 'flex';
        });
    });

    // Modal close button functionality
    closeButton.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Modal proceed button functionality
    modalProceedBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        const orderIdFromModal = modalOrderId.textContent.replace('Order ID: ', '');
        window.location.href = `receipt.php?order_id=${orderIdFromModal}`;
    });

    function generateOrderId() {
        const randomNum = Math.floor(Math.random() * 90000) + 10000;
        return `O${randomNum}`;
    }

    showStep(0);

    // Hamburger menu functionality for responsive nav
    const navToggle = document.querySelector('.nav-toggle');
    const navUl = document.querySelector('nav ul');
    if (navToggle && navUl) {
        navToggle.addEventListener('click', () => {
            navUl.classList.toggle('open');
        });
    }
});
