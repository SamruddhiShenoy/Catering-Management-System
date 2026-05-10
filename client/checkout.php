<?php
require_once '../config.php';
requireLogin();

$booking_id = $_GET['id'] ?? '';
if (!$booking_id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND client_id = ?");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header("Location: index.php");
    exit;
}

$is_advance_paid = ($booking['payment_status'] === 'advance_paid' || $booking['payment_status'] === 'paid');
$remaining_balance = $booking['amount'] - $booking['paid_amount'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_type = $_POST['payment_type'] ?? 'advance';
    $amount_to_pay = ($payment_type === 'full') ? $remaining_balance : $booking['advance_amount'];
    
    // Simulate successful payment processing
    sleep(1); 
    
    $new_status = 'confirmed';
    $pay_status = ($payment_type === 'full' || $remaining_balance <= $amount_to_pay) ? 'paid' : 'advance_paid';
    
    $stmt = $pdo->prepare("UPDATE bookings SET payment_status = ?, status = ?, paid_amount = paid_amount + ? WHERE id = ?");
    $stmt->execute([$pay_status, $new_status, $amount_to_pay, $booking_id]);
    
    $_SESSION['toast'] = ['msg' => 'Payment successful! Your booking is ' . ($pay_status === 'paid' ? 'fully paid' : 'confirmed with advance') . '.', 'type' => 'success'];
    header("Location: index.php");
    exit;
}

$page_title = 'Secure Checkout';
require_once '../includes/header.php';
?>

<div class="page active">
    <div style="display:flex;">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php require_once '../includes/topbar.php'; ?>
            
            <div class="content-area">
                <div class="grid-2" style="max-width: 1100px; margin: 0 auto; align-items: start; gap:30px;">
                    
                    <!-- LEFT: Payment Options & Form -->
                    <div>
                        <div class="table-card card-3d" style="margin-bottom:20px;">
                            <h3 style="margin-bottom:20px; color:var(--text)">Choose Payment Plan</h3>
                            <form method="POST" id="paymentForm">
                                <div style="display:flex; gap:15px; margin-bottom:25px;">
                                    <!-- ADVANCE OPTION -->
                                    <label class="payment-option" style="flex:1; cursor:pointer; <?= $is_advance_paid ? 'opacity:0.4; pointer-events:none;' : '' ?>">
                                        <input type="radio" name="payment_type" value="advance" <?= $is_advance_paid ? '' : 'checked' ?> style="display:none;" onchange="updatePayBtn(<?= $booking['advance_amount'] ?>)">
                                        <div class="opt-box" style="padding:20px; border:2px solid var(--border); border-radius:12px; text-align:center; transition:0.3s; position:relative;">
                                            <?php if($is_advance_paid): ?>
                                                <div style="position:absolute; top:5px; right:5px; color:var(--green);"><i class="fas fa-check-circle"></i></div>
                                            <?php endif; ?>
                                            <div style="font-size:12px; color:var(--text3); text-transform:uppercase;">Pay Advance</div>
                                            <div style="font-size:24px; color:var(--gold); font-weight:700; margin:5px 0;">Rs.<?= number_format($booking['advance_amount']) ?></div>
                                            <div style="font-size:11px; color:var(--text3);"><?= $is_advance_paid ? 'Already Paid' : '(Package Cost Only)' ?></div>
                                        </div>
                                    </label>

                                    <!-- FULL / REMAINING OPTION -->
                                    <label class="payment-option" style="flex:1; cursor:pointer;">
                                        <input type="radio" name="payment_type" value="full" <?= $is_advance_paid ? 'checked' : '' ?> style="display:none;" onchange="updatePayBtn(<?= $remaining_balance ?>)">
                                        <div class="opt-box" style="padding:20px; border:2px solid var(--border); border-radius:12px; text-align:center; transition:0.3s;">
                                            <div style="font-size:12px; color:var(--text3); text-transform:uppercase;"><?= $is_advance_paid ? 'Pay Balance' : 'Pay Full Amount' ?></div>
                                            <div style="font-size:24px; color:var(--gold); font-weight:700; margin:5px 0;">Rs.<?= number_format($remaining_balance) ?></div>
                                            <div style="font-size:11px; color:var(--text3);"><?= $is_advance_paid ? '(Outstanding Amount)' : '(Total incl. Menu)' ?></div>
                                        </div>
                                    </label>
                                </div>

                                <div class="form-group-dark">
                                    <label>Cardholder Name</label>
                                    <input type="text" class="form-input-dark" placeholder="Name on card" required value="<?= htmlspecialchars($_SESSION['user_name']) ?>">
                                </div>
                                <div class="form-group-dark">
                                    <label>Card Number</label>
                                    <input type="text" class="form-input-dark" placeholder="0000 0000 0000 0000" maxlength="19" required>
                                </div>
                                <div style="display:flex; gap:15px;">
                                    <div class="form-group-dark" style="flex:1"><label>Expiry</label><input type="text" class="form-input-dark" placeholder="MM/YY" required></div>
                                    <div class="form-group-dark" style="flex:1"><label>CVC</label><input type="text" class="form-input-dark" placeholder="123" required></div>
                                </div>
                                
                                <button type="submit" class="btn btn-gold btn-full" style="margin-top:20px; font-size:16px; padding:18px" id="payBtn">
                                    Pay Rs.<?= number_format($is_advance_paid ? $remaining_balance : $booking['advance_amount']) ?> Now
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- RIGHT: Breakdown Summary -->
                    <div class="table-card" style="background:var(--dark3); border:1px solid var(--border2);">
                        <h3 style="margin-bottom:20px; color:var(--gold); font-family:'Playfair Display';">Booking Breakdown</h3>
                        <div style="line-height:2.2; font-size:14px;">
                            <div style="display:flex; justify-content:space-between; color:var(--text2);">
                                <span>Package Base (<?= $booking['package'] ?>):</span>
                                <strong>Rs.<?= number_format($booking['advance_amount']) ?></strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; color:var(--text2);">
                                <span>Menu Add-ons:</span>
                                <strong>Rs.<?= number_format($booking['amount'] - $booking['advance_amount']) ?></strong>
                            </div>
                            <div style="margin:15px 0; border-top:1px dashed var(--border2);"></div>
                            <div style="display:flex; justify-content:space-between; color:var(--text); font-size:18px;">
                                <span>Grand Total:</span>
                                <strong style="color:var(--gold);">Rs.<?= number_format($booking['amount']) ?></strong>
                            </div>
                            <?php if($booking['paid_amount'] > 0): ?>
                                <div style="display:flex; justify-content:space-between; color:var(--green); font-size:14px;">
                                    <span>Already Paid:</span>
                                    <strong>- Rs.<?= number_format($booking['paid_amount']) ?></strong>
                                </div>
                                <div style="display:flex; justify-content:space-between; color:var(--text); font-size:16px; font-weight:bold; border-top:1px solid var(--border2); margin-top:5px; padding-top:5px;">
                                    <span>Balance Due:</span>
                                    <strong>Rs.<?= number_format($remaining_balance) ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="margin-top:25px; padding:15px; background:rgba(212,168,67,0.05); border-radius:10px; border:1px solid rgba(212,168,67,0.1); font-size:12px; color:var(--text3);">
                            <i class="fas fa-info-circle" style="color:var(--gold); margin-right:5px;"></i>
                            <?php if($is_advance_paid): ?>
                                You have already paid the <strong>Advance</strong>. Please settle the remaining balance to complete your payment.
                            <?php else: ?>
                                Paying the <strong>Advance</strong> confirms your date and booking. The remaining balance can be paid later.
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
.payment-option input:checked + .opt-box {
    border-color: var(--gold) !important;
    background: rgba(212,168,67,0.1);
    box-shadow: 0 0 15px rgba(212,168,67,0.2);
}
</style>

<script>
function updatePayBtn(amt) {
    document.getElementById('payBtn').textContent = 'Pay Rs.' + amt.toLocaleString() + ' Now';
}

document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('payBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    btn.disabled = true;
});
</script>

<?php require_once '../includes/footer.php'; ?>
