(() => {
    const checkout = document.getElementById('checkoutForm');
    const modal = document.getElementById('checkoutAddressModal');
    const openButton = document.getElementById('addCheckoutAddress');
    const warning = document.getElementById('checkoutAddressWarning');
    const addressForm = document.getElementById('checkoutAddressForm');
    const formWarning = document.getElementById('checkoutAddressFormWarning');
    const saveButton = document.getElementById('saveCheckoutAddress');
    if (!checkout || !modal || !addressForm) return;

    openButton.addEventListener('click', () => {
        modal.showModal();
        document.dispatchEvent(new Event('address-modal-opened'));
    });
    modal.querySelectorAll('[data-close-address]').forEach(button => {
        button.addEventListener('click', () => modal.close());
    });
    modal.addEventListener('close', () => openButton.focus());

    checkout.addEventListener('submit', event => {
        if (!checkout.querySelector('input[name="address_id"]:checked')) {
            event.preventDefault();
            warning.textContent = 'Please add or select a delivery address before completing your order.';
            warning.hidden = false;
            warning.focus();
            warning.scrollIntoView({ block: 'center', behavior: 'smooth' });
        }
    });
    checkout.addEventListener('change', event => {
        if (event.target.name === 'address_id') warning.hidden = true;
    });
    addressForm.addEventListener('invalid', () => {
        formWarning.textContent = 'Please fill in all required address fields and check their values.';
        formWarning.hidden = false;
    }, true);
    addressForm.addEventListener('submit', event => {
        // Disabled dependent dropdowns are skipped by native browser validation.
        const missing = ['region', 'province', 'city', 'barangay'].find(name => !addressForm.elements.namedItem(name).value);
        if (missing) {
            event.preventDefault();
            formWarning.textContent = 'Please select your region, province, city, and barangay before saving.';
            formWarning.hidden = false;
            formWarning.scrollIntoView({ block: 'center', behavior: 'smooth' });
            return;
        }
        if (saveButton.disabled) {
            event.preventDefault();
            return;
        }
        saveButton.disabled = true;
        saveButton.textContent = 'Saving address...';
    });
    window.addEventListener('pageshow', () => {
        saveButton.disabled = false;
        saveButton.textContent = 'Save and use this address';
    });
    if (modal.dataset.reopen === 'true') openButton.click();
})();