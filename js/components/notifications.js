/**
 * LuxeCater Pro Notification System
 * Handles real-time stock alerts and system messages
 */

document.addEventListener('DOMContentLoaded', () => {
    checkStockAlerts();
});

async function checkStockAlerts() {
    try {
        const response = await fetch('/Catering_Management_System/api/inventory_status.php');
        const data = await response.json();
        
        if (data.low_stock_count > 0) {
            showNotificationBadge(data.low_stock_count);
            
            // If it's an admin, show a specific toast for low stock
            if (data.is_admin) {
                toast(`⚠️ Low Stock Alert: ${data.low_stock_count} items need attention!`, 'warning');
            }
        }
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
    }
}

function showNotificationBadge(count) {
    const dot = document.getElementById('notifDot');
    if (dot) {
        dot.style.display = 'block';
        dot.textContent = count > 9 ? '9+' : count;
        dot.style.fontSize = '10px';
        dot.style.color = 'white';
        dot.style.textAlign = 'center';
        dot.style.lineHeight = '14px';
    }
}
