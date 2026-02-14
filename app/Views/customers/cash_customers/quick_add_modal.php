<!-- Quick Add Cash Customer Modal -->
<div class="modal fade" id="quickAddCashCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCenterTitle">Quick Add Cash Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickAddCashCustomerForm">
                <div class="modal-body">
                    <div id="quickAddError" class="alert alert-danger d-none"></div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="quick_customer_name" class="form-label">Customer Name</label>
                            <input type="text" id="quick_customer_name" name="name" class="form-control" placeholder="Enter Name" required>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col mb-0">
                            <label for="quick_mobile" class="form-label">Mobile</label>
                            <input type="text" id="quick_mobile" name="mobile" class="form-control" placeholder="10 Digit Mobile" required pattern="[0-9]{10}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveQuickCustomer">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quickAddForm = document.getElementById('quickAddCashCustomerForm');
    const quickAddError = document.getElementById('quickAddError');
    const btnSave = document.getElementById('btnSaveQuickCustomer');

    if(quickAddForm) {
        quickAddForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic Validation
            const name = document.getElementById('quick_customer_name').value.trim();
            const mobile = document.getElementById('quick_mobile').value.trim();
            
            if(!name || !mobile) {
                quickAddError.textContent = 'Name and Mobile are required.';
                quickAddError.classList.remove('d-none');
                return;
            }
            
            if(!/^\d{10}$/.test(mobile)) {
                quickAddError.textContent = 'Mobile number must be 10 digits.';
                quickAddError.classList.remove('d-none');
                return;
            }

            // Prepare Data
            const formData = new FormData();
            formData.append('name', name);
            formData.append('mobile', mobile);
            // Append CSRF if available globally
            const csrfToken = document.querySelector('input[name="<?= csrf_token() ?>"]');
            if(csrfToken) {
                formData.append('<?= csrf_token() ?>', csrfToken.value);
            }

            // Disable button
            btnSave.disabled = true;
            quickAddError.classList.add('d-none');

            // AJAX Request
            fetch('<?= base_url('customers/cash-customers/find-or-create') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken ? csrfToken.value : '' // For safety
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btnSave.disabled = false;
                
                if(data.success) {
                    // Close Modal
                    const modalEl = document.getElementById('quickAddCashCustomerModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();
                    
                    // Reset Form
                    quickAddForm.reset();

                    // Dispatch Custom Event for Invoice Page to listen to
                    const event = new CustomEvent('cashCustomerAdded', { 
                        detail: { 
                            id: data.customer_id, 
                            name: name,
                            mobile: mobile 
                        } 
                    });
                    document.dispatchEvent(event);
                    
                    // Optional: If there is a specific Select2 or input, update it directly if we knew the ID
                    // But event dispatch is cleaner.
                    
                    alert('Customer Added Successfully!');
                } else {
                    quickAddError.textContent = data.message || 'Error adding customer.';
                    quickAddError.classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                quickAddError.textContent = 'An error occurred processing request.';
                quickAddError.classList.remove('d-none');
                btnSave.disabled = false;
            });
        });
    }
});
</script>
