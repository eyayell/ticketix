<?php
session_start();
require_once __DIR__ . '/config.php';
$conn = getDBConnection();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

// Get booking data from POST
if (!isset($_POST['booking_data'])) {
    header("Location: TICKETIX NI CLAIRE.php");
    exit();
}

$bookingData = json_decode($_POST['booking_data'], true);
$seatTotal = floatval($_POST['seat_total'] ?? 0);
$foodTotal = floatval($_POST['food_total'] ?? 0);
$grandTotal = floatval($_POST['grand_total'] ?? 0);

if (!$bookingData) {
    header("Location: TICKETIX NI CLAIRE.php");
    exit();
}

$movieTitle = $bookingData['movie'] ?? '';
$branchName = $bookingData['branch'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Ticketix</title>
    <link rel="icon" type="image/png" href="images/brand x.png" />
    <link rel="stylesheet" href="css/ticketix-main.css">
    <link rel="stylesheet" href="css/payment.css">
</head>
<body>
    <div class="payment-container">
        <a href="checkout.php" class="btn-back" onclick="history.back(); return false;">← Back</a>
        <h1>Payment Method</h1>
        <div class="total-amount">Total: ₱<?= number_format($grandTotal, 2) ?></div>
        
        <?php if (isset($_GET['error'])): ?>
        <div class="error-message">
            <strong>Error:</strong> <?= htmlspecialchars($_GET['error']) ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="process-booking.php" id="paymentForm">
            <input type="hidden" name="booking_data" value="<?= htmlspecialchars($_POST['booking_data']) ?>">
            <input type="hidden" name="seat_total" value="<?= $seatTotal ?>">
            <input type="hidden" name="food_total" value="<?= $foodTotal ?>">
            <input type="hidden" name="grand_total" value="<?= $grandTotal ?>">
            <input type="hidden" name="payment_type" id="paymentType" value="">
            <input type="hidden" name="reference_number" id="referenceNumber" value="">
            <input type="hidden" name="debug" value="1">
            
            <div class="payment-methods">
                <!-- Credit Card Option -->
                <div class="payment-option" onclick="selectPayment('credit-card', event)">
                    <label>
                        <input type="radio" name="payment_method" value="credit-card" onchange="selectPayment('credit-card', event)">
                        <span class="payment-icon">💳</span>
                        <span>Credit Card</span>
                    </label>
                    
                    <!-- Credit Card Sub-options -->
                    <div class="sub-options" id="creditSubOptions" style="display: none;">
                        <div class="sub-option" onclick="event.stopPropagation(); selectSubOption('visa', 'credit-card');">
                            <input type="radio" name="credit_sub_option" value="visa" id="visa" onchange="selectSubOption('visa', 'credit-card')">
                            <label for="visa">
                                <span class="sub-option-icon">💳</span>
                                <span>Visa</span>
                            </label>
                        </div>
                        <div class="sub-option" onclick="event.stopPropagation(); selectSubOption('mastercard', 'credit-card');">
                            <input type="radio" name="credit_sub_option" value="mastercard" id="mastercard" onchange="selectSubOption('mastercard', 'credit-card')">
                            <label for="mastercard">
                                <span class="sub-option-icon">💳</span>
                                <span>Mastercard</span>
                            </label>
                        </div>
                        <div class="sub-option" onclick="event.stopPropagation(); selectSubOption('amex', 'credit-card');">
                            <input type="radio" name="credit_sub_option" value="amex" id="amex" onchange="selectSubOption('amex', 'credit-card')">
                            <label for="amex">
                                <span class="sub-option-icon">💳</span>
                                <span>American Express</span>
                            </label>
                        </div>
                        <div class="sub-option" onclick="event.stopPropagation(); selectSubOption('discover', 'credit-card');">
                            <input type="radio" name="credit_sub_option" value="discover" id="discover" onchange="selectSubOption('discover', 'credit-card')">
                            <label for="discover">
                                <span class="sub-option-icon">💳</span>
                                <span>Discover</span>
                            </label>
                        </div>
                        <div class="reference-wrapper" id="cardReferenceWrapper">
                            <label class="reference-label" for="cardReference">Card Reference</label>
                            <input type="text" name="card_reference" id="cardReference" class="reference-input" placeholder="Enter last 4 digits (e.g., 1234)" maxlength="4">
                            <small class="reference-hint">Use any 4 digits for testing. In production, enter the last four digits of your card.</small>
                        </div>
                    </div>
                </div>
                
                <!-- E-Wallet Option -->
                <div class="payment-option" onclick="selectPayment('e-wallet', event)">
                    <label>
                        <input type="radio" name="payment_method" value="e-wallet" onchange="selectPayment('e-wallet', event)">
                        <span class="payment-icon">📱</span>
                        <span>E-Wallet</span>
                    </label>
                    
                    <!-- E-Wallet Sub-options -->
                    <div class="sub-options" id="ewalletSubOptions" style="display: none;">
                        <div class="sub-option" onclick="event.stopPropagation(); selectSubOption('gcash', 'e-wallet');">
                            <input type="radio" name="ewallet_sub_option" value="gcash" id="gcash" onchange="selectSubOption('gcash', 'e-wallet')">
                            <label for="gcash">
                                <span class="sub-option-icon">📱</span>
                                <span>GCash</span>
                            </label>
                        </div>
                        <div class="sub-option" onclick="event.stopPropagation(); selectSubOption('paymaya', 'e-wallet');">
                            <input type="radio" name="ewallet_sub_option" value="paymaya" id="paymaya" onchange="selectSubOption('paymaya', 'e-wallet')">
                            <label for="paymaya">
                                <span class="sub-option-icon">📱</span>
                                <span>PayMaya</span>
                            </label>
                        </div>
                        <div class="sub-option" onclick="event.stopPropagation(); selectSubOption('paypal', 'e-wallet');">
                            <input type="radio" name="ewallet_sub_option" value="paypal" id="paypal" onchange="selectSubOption('paypal', 'e-wallet')">
                            <label for="paypal">
                                <span class="sub-option-icon">📱</span>
                                <span>PayPal</span>
                            </label>
                        </div>
                        <div class="sub-option" onclick="event.stopPropagation(); selectSubOption('grabpay', 'e-wallet');">
                            <input type="radio" name="ewallet_sub_option" value="grabpay" id="grabpay" onchange="selectSubOption('grabpay', 'e-wallet')">
                            <label for="grabpay">
                                <span class="sub-option-icon">📱</span>
                                <span>GrabPay</span>
                            </label>
                        </div>
                        <div class="reference-wrapper" id="ewalletReferenceWrapper">
                            <label class="reference-label" for="ewalletReference">E-Wallet Reference</label>
                            <input type="text" name="ewallet_reference" id="ewalletReference" class="reference-input" placeholder="Enter transaction reference (e.g., GCASH123456)" maxlength="20">
                            <small class="reference-hint">For demo purposes you can type any value like GCASH123456. In production, enter the reference from your e-wallet receipt.</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn-pay" id="payButton" disabled>Complete Payment</button>
        </form>
    </div>
    
    <script>
        let selectedMainType = '';
        let selectedSubOption = '';
        
        function selectPayment(type, eventElement) {
            selectedMainType = type;
            selectedSubOption = '';

            const radio = document.querySelector(`input[value="${type}"]`);
            if (radio) {
                radio.checked = true;
            }

            document.querySelectorAll('.payment-option').forEach(opt => {
                opt.classList.remove('selected');
            });

            if (eventElement && eventElement.currentTarget) {
                eventElement.currentTarget.classList.add('selected');
            } else {
                document.querySelectorAll('.payment-option').forEach(opt => {
                    if (opt.querySelector(`input[value="${type}"]`)) {
                        opt.classList.add('selected');
                    }
                });
            }

            // Hide sub-option wrappers initially
            const creditSubOptions = document.getElementById('creditSubOptions');
            const ewalletSubOptions = document.getElementById('ewalletSubOptions');
            const cardWrapper = document.getElementById('cardReferenceWrapper');
            const ewalletWrapper = document.getElementById('ewalletReferenceWrapper');
            const cardLabel = document.querySelector('label.reference-label[for="cardReference"]');
            const walletLabel = document.querySelector('label.reference-label[for="ewalletReference"]');

            if (creditSubOptions) creditSubOptions.style.display = (type === 'credit-card') ? 'block' : 'none';
            if (ewalletSubOptions) ewalletSubOptions.style.display = (type === 'e-wallet') ? 'block' : 'none';
            if (cardWrapper) cardWrapper.classList.remove('active');
            if (ewalletWrapper) ewalletWrapper.classList.remove('active');
            if (cardLabel) cardLabel.textContent = 'Card Reference';
            if (walletLabel) walletLabel.textContent = 'E-Wallet Reference';

            document.getElementById('paymentType').value = '';
            document.getElementById('referenceNumber').value = '';
            const cardRefInput = document.getElementById('cardReference');
            const walletRefInput = document.getElementById('ewalletReference');
            if (cardRefInput) {
                cardRefInput.value = '';
                cardRefInput.dataset.subOption = '';
            }
            if (walletRefInput) {
                walletRefInput.value = '';
                walletRefInput.dataset.subOption = '';
            }

            updatePayButton();
        }
        
        function selectSubOption(subOption, mainType) {
            selectedSubOption = subOption;
            selectedMainType = mainType;

            const paymentTypeInput = document.getElementById('paymentType');
            paymentTypeInput.value = subOption;

            const cardWrapper = document.getElementById('cardReferenceWrapper');
            const ewalletWrapper = document.getElementById('ewalletReferenceWrapper');
            const cardRefInput = document.getElementById('cardReference');
            const walletRefInput = document.getElementById('ewalletReference');

            const displayNames = {
                visa: 'Visa',
                mastercard: 'Mastercard',
                amex: 'American Express',
                discover: 'Discover',
                gcash: 'GCash',
                paymaya: 'PayMaya',
                paypal: 'PayPal',
                grabpay: 'GrabPay'
            };
            const friendlyName = displayNames[subOption] || subOption;

            if (mainType === 'credit-card') {
                if (cardWrapper) cardWrapper.classList.add('active');
                if (ewalletWrapper) ewalletWrapper.classList.remove('active');
                if (cardRefInput) {
                    cardRefInput.placeholder = `Enter last 4 digits (${friendlyName})`;
                    cardRefInput.dataset.subOption = subOption;
                    cardRefInput.focus();
                }
                const cardLabel = document.querySelector('label.reference-label[for="cardReference"]');
                if (cardLabel) {
                    cardLabel.textContent = `${friendlyName} Reference`;
                }
                if (walletRefInput) {
                    walletRefInput.value = '';
                    walletRefInput.dataset.subOption = '';
                }
            } else if (mainType === 'e-wallet') {
                if (ewalletWrapper) ewalletWrapper.classList.add('active');
                if (cardWrapper) cardWrapper.classList.remove('active');
                if (walletRefInput) {
                    walletRefInput.placeholder = `Enter ${friendlyName} reference (e.g., ${subOption.toUpperCase()}123456)`;
                    walletRefInput.dataset.subOption = subOption;
                    walletRefInput.focus();
                }
                const walletLabel = document.querySelector('label.reference-label[for="ewalletReference"]');
                if (walletLabel) {
                    walletLabel.textContent = `${friendlyName} Reference`;
                }
                if (cardRefInput) {
                    cardRefInput.value = '';
                    cardRefInput.dataset.subOption = '';
                }
            }

            updatePayButton();
        }
        
        function updatePayButton() {
            const payButton = document.getElementById('payButton');
            if (selectedMainType && selectedSubOption) {
                payButton.disabled = false;
            } else {
                payButton.disabled = true;
            }
        }
        
        // Handle reference number input for credit card
        const cardReferenceInput = document.getElementById('cardReference');
        const ewalletReferenceInput = document.getElementById('ewalletReference');
        document.querySelectorAll('.reference-wrapper').forEach(wrapper => {
            ['click', 'focus'].forEach(evt => {
                wrapper.addEventListener(evt, event => event.stopPropagation());
            });
        });

        if (cardReferenceInput) {
            ['click', 'focus'].forEach(evt => {
                cardReferenceInput.addEventListener(evt, event => event.stopPropagation());
            });
            cardReferenceInput.addEventListener('input', function() {
                const subOption = this.dataset.subOption || selectedSubOption;
                if (this.value.length >= 4 && subOption) {
                    document.getElementById('referenceNumber').value = subOption.toUpperCase() + '-' + this.value;
                } else {
                    document.getElementById('referenceNumber').value = '';
                }
            });
        }

        // Handle reference number input for e-wallet
        if (ewalletReferenceInput) {
            ['click', 'focus'].forEach(evt => {
                ewalletReferenceInput.addEventListener(evt, event => event.stopPropagation());
            });
            ewalletReferenceInput.addEventListener('input', function() {
                const subOption = this.dataset.subOption || selectedSubOption;
                if (this.value.length > 0 && subOption) {
                    document.getElementById('referenceNumber').value = subOption.toUpperCase() + '-' + this.value;
                } else {
                    document.getElementById('referenceNumber').value = '';
                }
            });
        }
        
        // Handle form submission
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const paymentType = document.getElementById('paymentType').value;
            if (!paymentType || !selectedMainType || !selectedSubOption) {
                e.preventDefault();
                alert('Please select a payment method and sub-option.');
                return false;
            }
            
            // Generate reference number if not provided
            const refNumber = document.getElementById('referenceNumber').value;
            if (!refNumber || refNumber === '') {
                const prefix = selectedSubOption ? selectedSubOption.toUpperCase() : (selectedMainType === 'credit-card' ? 'CARD' : 'PAY');
                if (selectedMainType === 'credit-card') {
                    const digits = (cardReferenceInput && cardReferenceInput.value.length >= 4)
                        ? cardReferenceInput.value
                        : String(Math.floor(1000 + Math.random() * 9000));
                    document.getElementById('referenceNumber').value = prefix + '-' + digits;
                    if (cardReferenceInput && cardReferenceInput.value.length < 4) {
                        cardReferenceInput.value = digits;
                    }
                } else if (selectedMainType === 'e-wallet') {
                    const walletDigits = String(Math.floor(100000 + Math.random() * 900000));
                    document.getElementById('referenceNumber').value = prefix + '-' + walletDigits;
                    if (ewalletReferenceInput && ewalletReferenceInput.value.length === 0) {
                        ewalletReferenceInput.value = walletDigits;
                    }
                }
            }
            
            // Show loading state
            const submitBtn = document.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
        });
    </script>
</body>
</html>

