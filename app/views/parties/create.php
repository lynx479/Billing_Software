<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Create New Party (Customer / Vendor)</h5>
        <a href="<?php echo APP_URL; ?>/parties/created" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Parties
        </a>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo APP_URL; ?>/parties/create" id="partyForm">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Party Type <span class="text-danger">*</span></label>
                    <select name="party_type" class="form-select" required>
                        <option value="CUSTOMER">Customer</option>
                        <option value="VENDOR">Vendor / Supplier</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Contact Person Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Rahul Sharma" maxlength="35">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Business / Company Name</label>
                    <input type="text" name="business_name" class="form-control" placeholder="e.g. Apex Retail" maxlength="70">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control" placeholder="9876543210" maxlength="10">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="rahul@example.com">
                </div>

                <!-- Tax Information: GSTIN & PAN -->
                <div class="col-md-6">
                    <label class="form-label">GSTIN (Optional)</label>
                    <input type="text" name="gstin" class="form-control" placeholder="32AAAAA0000A1Z5" maxlength="15">
                </div>
                <div class="col-md-6">
                    <label class="form-label">PAN Number</label>
                    <input type="text" name="pan" class="form-control" placeholder="ABCDE1234F" maxlength="10">
                </div>

                <!-- Location: State with Code, City, Pincode -->
                <div class="col-md-4">
                    <label class="form-label fw-bold">State &amp; Code <span class="text-danger">*</span></label>
                    <select name="state" class="form-select" required>
                        <option value="">-- Select State --</option>
                        <?php foreach ($states as $s): ?>
                            <option value="<?php echo htmlspecialchars($s['state_name']); ?>">
                                [<?php echo $s['state_code']; ?>] <?php echo htmlspecialchars($s['state_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">City <span class="text-danger">*</span></label>
                    <input type="text" name="city" id="primaryCity" class="form-control" placeholder="e.g. Kochi, Mumbai" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Pincode <span class="text-danger">*</span></label>
                    <input type="text" name="pincode" class="form-control" placeholder="682001" maxlength="6" required>
                </div>

                <!-- Structured Billing Address Fields -->
                <div class="col-12 mt-4">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-1">Billing Address Details <span class="text-danger">*</span></h6>
                </div>

                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Building / Door No.</label>
                    <input type="text" id="bill_bldg" class="form-control form-control-sm" placeholder="e.g. Bldg 4B, Room 12">
                </div>
                <div class="col-md-8">
                    <label class="form-label small text-muted mb-1">Street</label>
                    <input type="text" id="bill_street" class="form-control form-control-sm" placeholder="e.g. MG Road">
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Address Line 1</label>
                    <input type="text" id="bill_line1" class="form-control form-control-sm" placeholder="Floor, Wing, Flat / Suite">
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Address Line 2</label>
                    <input type="text" id="bill_line2" class="form-control form-control-sm" placeholder="Landmark">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Area</label>
                    <input type="text" id="bill_area" class="form-control form-control-sm" placeholder="e.g. Industrial Area">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">City</label>
                    <input type="text" id="bill_city" class="form-control form-control-sm" placeholder="e.g. Kochi">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">District</label>
                    <input type="text" id="bill_dist" class="form-control form-control-sm" placeholder="e.g. Ernakulam">
                </div>

                <!-- Hidden inputs containing bundled address strings for the database -->
                <input type="hidden" name="address" id="finalBillingAddress">
                <input type="hidden" name="shipping_address" id="finalShippingAddress">

                <!-- Shipping Address Checkbox & Toggle -->
                <div class="col-12 mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="same_as_billing" id="sameAsBilling" value="1" checked>
                        <label class="form-check-label fw-bold" for="sameAsBilling">
                            Shipping address is the same as Billing address
                        </label>
                    </div>
                </div>

                <!-- Structured Shipping Address Container -->
                <div class="col-12 d-none" id="shippingAddressContainer">
                    <div class="p-3 bg-light rounded border mt-2">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="bi bi-truck text-primary me-1"></i> Shipping Address Details
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">Building / Door No.</label>
                                <input type="text" id="ship_bldg" class="form-control form-control-sm" placeholder="e.g. Bldg 4B, Room 12">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small text-muted mb-1">Street</label>
                                <input type="text" id="ship_street" class="form-control form-control-sm" placeholder="e.g. MG Road">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1">Address Line 1</label>
                                <input type="text" id="ship_line1" class="form-control form-control-sm" placeholder="Floor, Wing, Flat / Suite">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1">Address Line 2</label>
                                <input type="text" id="ship_line2" class="form-control form-control-sm" placeholder="Landmark">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">Area</label>
                                <input type="text" id="ship_area" class="form-control form-control-sm" placeholder="e.g. Industrial Area">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">City</label>
                                <input type="text" id="ship_city" class="form-control form-control-sm" placeholder="e.g. Kochi">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">District</label>
                                <input type="text" id="ship_dist" class="form-control form-control-sm" placeholder="e.g. Ernakulam">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-end">
                <a href="<?php echo APP_URL; ?>/parties/created" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save"></i> Save Party</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sameCheckbox = document.getElementById('sameAsBilling');
    const shippingContainer = document.getElementById('shippingAddressContainer');
    const partyForm = document.getElementById('partyForm');
    const primaryCity = document.getElementById('primaryCity');
    const billCity = document.getElementById('bill_city');

    // Sync primary location city to billing city if billing city is empty
    primaryCity.addEventListener('input', function() {
        if (!billCity.value.trim() || billCity.dataset.synced === 'true') {
            billCity.value = this.value;
            billCity.dataset.synced = 'true';
        }
    });

    billCity.addEventListener('input', function() {
        billCity.dataset.synced = 'false';
    });

    // Toggle Shipping Fields
    sameCheckbox.addEventListener('change', function () {
        if (this.checked) {
            shippingContainer.classList.add('d-none');
        } else {
            shippingContainer.classList.remove('d-none');
        }
    });

    // Helper: Compile individual fields into a single address string
    function compileAddress(prefix) {
        const parts = [
            document.getElementById(prefix + '_bldg')?.value.trim(),
            document.getElementById(prefix + '_street')?.value.trim(),
            document.getElementById(prefix + '_line1')?.value.trim(),
            document.getElementById(prefix + '_line2')?.value.trim(),
            document.getElementById(prefix + '_area')?.value.trim(),
            document.getElementById(prefix + '_city')?.value.trim(),
            document.getElementById(prefix + '_dist')?.value.trim()
        ].filter(Boolean);

        return parts.join(', ');
    }

    // Pack compiled addresses into hidden inputs before submitting
    partyForm.addEventListener('submit', function () {
        const billingAddress = compileAddress('bill');
        document.getElementById('finalBillingAddress').value = billingAddress;

        if (sameCheckbox.checked) {
            document.getElementById('finalShippingAddress').value = billingAddress;
        } else {
            document.getElementById('finalShippingAddress').value = compileAddress('ship');
        }
    });
});
</script>