<?php
require_once '../config.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$booking_id = $_GET['id'] ?? '';

if (empty($booking_id)) {
    die("Booking ID required.");
}

// Fetch booking and client details
$stmt = $pdo->prepare("
    SELECT b.*, u.name as client_name, u.email as client_email, u.phone as client_phone 
    FROM bookings b 
    JOIN users u ON b.client_id = u.id 
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Booking not found.");
}

// Security Check: Only Admin or the Client who owns the booking can view it
if (!isAdmin() && $booking['client_id'] != $_SESSION['user_id']) {
    die("Unauthorized access.");
}

// Fetch Menu Items details
$menu_items_display = [];
$items_total = 0;
if (!empty($booking['selected_menu'])) {
    $selections = json_decode($booking['selected_menu'], true);
    if (is_array($selections)) {
        foreach ($selections as $sel) {
            $stmt = $pdo->prepare("SELECT name, price FROM menu_items WHERE id = ?");
            $stmt->execute([$sel['id']]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($item) {
                $qty = (int)$sel['qty'];
                $sub = (float)$item['price'] * $qty;
                $menu_items_display[] = [
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'qty' => $qty,
                    'subtotal' => $sub
                ];
                $items_total += $sub;
            }
        }
    } else {
        // Fallback for old comma-separated format if needed
        $ids = explode(',', $booking['selected_menu']);
        foreach($ids as $id) {
            $stmt = $pdo->prepare("SELECT name, price FROM menu_items WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($item) {
                $menu_items_display[] = ['name' => $item['name'], 'price' => $item['price'], 'qty' => $booking['guests'], 'subtotal' => (float)$item['price'] * $booking['guests']];
                $items_total += (float)$item['price'] * $booking['guests'];
            }
        }
    }
}

// Fetch package price for breakdown
$stmt = $pdo->prepare("SELECT price FROM packages WHERE name = ?");
$stmt->execute([$booking['package']]);
$pkg_base_price = (float)($stmt->fetchColumn() ?: 0);

// Setup Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// HTML Content
$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: "DejaVu Sans", sans-serif; color: #333; line-height: 1.4; }
        .invoice-box { max-width: 800px; margin: auto; padding: 20px; border: 1px solid #eee; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #D4A843; padding-bottom: 10px; overflow: auto; }
        .logo { color: #D4A843; font-size: 24px; font-weight: bold; float: left; }
        .invoice-title { float: right; text-align: right; }
        .details { margin-bottom: 30px; clear: both; padding-top: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th { background: #f8f8f8; text-align: left; padding: 10px; border-bottom: 1px solid #eee; color: #666; font-size: 11px; text-transform: uppercase; }
        .table td { padding: 10px; border-bottom: 1px solid #eee; font-size: 12px; }
        .total-section { text-align: right; margin-top: 20px; border-top: 2px solid #D4A843; padding-top: 10px; }
        .total-row { display: flex; justify-content: flex-end; margin-bottom: 5px; }
        .footer { text-align: center; margin-top: 40px; font-size: 10px; color: #999; }
        .status-paid { color: #2ecc8a; font-weight: bold; }
        .status-unpaid { color: #e84060; font-weight: bold; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <div class="logo">CaterBook</div>
            <div class="invoice-title">
                <div style="font-size: 18px; color: #666">INVOICE</div>
                <div style="color: #999">#' . $booking['id'] . '</div>
            </div>
        </div>

        <table style="width: 100%; margin-bottom: 20px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <strong>Billed To:</strong><br>
                    ' . htmlspecialchars($booking['client_name']) . '<br>
                    ' . htmlspecialchars($booking['client_email']) . '<br>
                    ' . htmlspecialchars($booking['client_phone'] ?: 'N/A') . '
                </td>
                <td style="width: 50%; text-align: right; vertical-align: top;">
                    <strong>Event Details:</strong><br>
                    Event: ' . htmlspecialchars($booking['event']) . '<br>
                    Date: ' . $booking['date'] . '<br>
                    Venue: ' . htmlspecialchars($booking['venue']) . '<br>
                    Payment: <span class="' . ($booking['payment_status'] === 'paid' ? 'status-paid' : 'status-unpaid') . '">' . strtoupper($booking['payment_status'] ?: 'unpaid') . '</span>
                </td>
            </tr>
        </table>

        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50%">Item / Description</th>
                    <th style="text-align: center">Rate</th>
                    <th style="text-align: center">Qty</th>
                    <th style="text-align: right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>' . htmlspecialchars($booking['package'] ?: 'Custom') . ' Package</strong><br>
                        <small style="color: #999">Base catering service & management</small>
                    </td>
                    <td style="text-align: center">₹' . number_format($pkg_base_price, 2) . '</td>
                    <td style="text-align: center">' . $booking['guests'] . '</td>
                    <td style="text-align: right">₹' . number_format($pkg_base_price * $booking['guests'], 2) . '</td>
                </tr>';

foreach($menu_items_display as $item) {
    $html .= '
                <tr>
                    <td>' . htmlspecialchars($item['name']) . '</td>
                    <td style="text-align: center">₹' . number_format((float)$item['price'], 2) . '</td>
                    <td style="text-align: center">' . $item['qty'] . '</td>
                    <td style="text-align: right">₹' . number_format((float)$item['subtotal'], 2) . '</td>
                </tr>';
}

$html .= '
            </tbody>
        </table>

        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 30px;">
            <div style="width: 40%; font-size: 11px; color: #666; background: #f9f9f9; padding: 15px; border-radius: 5px;">
                <strong>PAYMENT SUMMARY:</strong><br><br>
                <table style="width: 100%;">
                    <tr>
                        <td>Subtotal:</td>
                        <td style="text-align: right">₹' . number_format($pkg_base_price * $booking['guests'] + $items_total, 2) . '</td>
                    </tr>
                    <tr style="color: #2ecc8a;">
                        <td>Discount (20%):</td>
                        <td style="text-align: right">- ₹' . number_format(($pkg_base_price * $booking['guests'] + $items_total) * 0.2, 2) . '</td>
                    </tr>
                    <tr>
                        <td>Grand Total:</td>
                        <td style="text-align: right">₹' . number_format($booking['amount'], 2) . '</td>
                    </tr>
                    <tr>
                        <td>Total Paid:</td>
                        <td style="text-align: right">₹' . number_format($booking['paid_amount'], 2) . '</td>
                    </tr>
                    <tr style="font-weight: bold; color: #D4A843; border-top: 1px solid #ddd;">
                        <td style="padding-top: 5px;">Balance Due:</td>
                        <td style="text-align: right; padding-top: 5px;">₹' . number_format($booking['amount'] - $booking['paid_amount'], 2) . '</td>
                    </tr>
                </table>
            </div>

            <div style="width: 50%; text-align: right; font-size: 14px;">
                <div style="margin-bottom: 5px; color: #999;">Package Total: ₹' . number_format($pkg_base_price * $booking['guests'], 2) . '</div>
                <div style="margin-bottom: 5px; color: #999;">Add-ons Total: ₹' . number_format($items_total, 2) . '</div>
                <div style="margin-bottom: 5px; color: #2ecc8a;">Promo Discount (20%): -₹' . number_format(($pkg_base_price * $booking['guests'] + $items_total) * 0.2, 2) . '</div>
                <div style="font-size: 20px; font-weight: bold; color: #D4A843; border-top: 2px solid #D4A843; padding-top: 10px; margin-top: 10px;">
                    GRAND TOTAL: ₹' . number_format($booking['amount'], 2) . '
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for choosing CaterBook. This is a system-generated invoice.</p>
            <p>Generated on ' . date('Y-m-d H:i:s') . '</p>
        </div>
    </div>
</body>
</html>
';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output PDF
$dompdf->stream("Invoice_" . $booking['id'] . ".pdf", ["Attachment" => false]);
?>
