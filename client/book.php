<?php
require_once '../config.php';
requireLogin();

$page_title = 'Book an Event';
$page_subtitle = 'Plan your perfect catering experience';

// Fetch data for wizard
$stmt = $pdo->query("SELECT * FROM packages ORDER BY price");
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM menu_items WHERE available = 1 ORDER BY category, name");
$menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$menu_by_cat = [];
foreach($menu_items as $item) {
    $menu_by_cat[$item['category']][] = $item;
}

require_once '../includes/header.php';
?>

<div class="page active">
    <div style="display:flex;">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php require_once '../includes/topbar.php'; ?>
            
            <div class="content-area">
                <div class="order-wizard">
                    <!-- Step Navigation -->
                    <div class="wizard-steps">
                        <div class="wizard-step active" id="step-nav-1" onclick="goToStep(1)">1. Event Details</div>
                        <div class="wizard-step" id="step-nav-2" onclick="goToStep(2)">2. Select Menu</div>
                        <div class="wizard-step" id="step-nav-3" onclick="goToStep(3)">3. Choose Package</div>
                        <div class="wizard-step" id="step-nav-4" onclick="goToStep(4)">4. Confirm</div>
                    </div>

                    <form id="bookingForm" action="../api/save_booking.php" method="POST">
                        <div class="wizard-body">
                            
                            <!-- STEP 1: EVENT DETAILS -->
                            <div class="step-content active" id="step-1">
                                <h2 style="margin-bottom:24px; font-size:24px;">Tell us about your event</h2>
                                <div class="grid-2">
                                    <div class="form-group-dark">
                                        <label>Event Type</label>
                                        <select name="event_type" class="form-input-dark" required>
                                            <option>Wedding Reception</option>
                                            <option>Corporate Gala</option>
                                            <option>Birthday Party</option>
                                            <option>Anniversary Dinner</option>
                                            <option>Product Launch</option>
                                            <option>Other</option>
                                        </select>
                                    </div>
                                    <div class="form-group-dark">
                                        <label>Event Date</label>
                                        <input type="date" name="event_date" class="form-input-dark" required value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                                    </div>
                                    <div class="form-group-dark">
                                        <label>Number of Guests</label>
                                        <input type="number" name="guests" id="guests_input" class="form-input-dark" placeholder="e.g. 50" min="1" required>
                                    </div>
                                    <div class="form-group-dark">
                                        <label>Venue</label>
                                        <input type="text" name="venue" class="form-input-dark" placeholder="Event location" required>
                                    </div>
                                </div>
                                <div class="form-group-dark">
                                    <label>Special Requirements</label>
                                    <textarea name="notes" class="form-input-dark" placeholder="Dietary restrictions, special themes, etc."></textarea>
                                </div>
                                <div style="text-align:right; margin-top:20px;">
                                    <button type="button" class="btn btn-gold" onclick="goToStep(2)">Next: Select Menu &rarr;</button>
                                </div>
                            </div>

                            <!-- STEP 2: SELECT MENU -->
                            <div class="step-content" id="step-2" style="display:none;">
                                <h2 style="margin-bottom:24px; font-size:24px;">Customise Your Menu</h2>
                                
                                <div class="filter-row" id="menu-cats" style="overflow-x:auto; padding-bottom:10px;">
                                    <?php foreach(array_keys($menu_by_cat) as $idx => $cat): ?>
                                        <button type="button" class="filter-btn <?= $idx===0?'active':'' ?>" onclick="showMenuCat('<?= htmlspecialchars($cat, ENT_QUOTES) ?>', this)"><?= $cat ?></button>
                                    <?php endforeach; ?>
                                </div>

                                <div id="menu-items-container" style="min-height: 300px;">
                                    <?php if(empty($menu_by_cat)): ?>
                                        <div style="text-align:center; padding:50px; background:var(--dark3); border-radius:12px; margin-top:20px; border:1px dashed var(--border);">
                                            <div style="font-size:48px; margin-bottom:15px; opacity:0.3;">🍽️</div>
                                            <p style="color:var(--text2)">No menu items are currently available.</p>
                                        </div>
                                    <?php else: 
                                        $cat_keys = array_keys($menu_by_cat);
                                        foreach($menu_by_cat as $cat => $items): 
                                            $isFirst = ($cat === $cat_keys[0]);
                                    ?>
                                        <div class="menu-cat-grid" data-category="<?= htmlspecialchars($cat, ENT_QUOTES) ?>" style="display:<?= $isFirst ? 'grid' : 'none' ?>; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; margin-top:20px;">
                                            <?php foreach($items as $m): ?>
                                                <div class="menu-item-card menu-select-card" 
                                                     data-id="<?= htmlspecialchars($m['id']) ?>" 
                                                     data-name="<?= htmlspecialchars($m['name']) ?>"
                                                     data-emoji="<?= htmlspecialchars($m['emoji']) ?>"
                                                     data-price="<?= (float)$m['price'] ?>"
                                                     onclick="toggleMenuItemFromEl(this)">
                                                    <div style="height:120px; background:var(--dark3); border-radius:8px; margin-bottom:10px; display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative;">
                                                        <?php if(!empty($m['image_path'])): ?>
                                                            <img src="../<?= $m['image_path'] ?>" style="width:100%; height:100%; object-fit:cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                            <div style="font-size:50px; opacity:0.8; display:none;"><?= $m['emoji'] ?></div>
                                                        <?php else: ?>
                                                            <div style="font-size:50px; opacity:0.8;"><?= $m['emoji'] ?></div>
                                                        <?php endif; ?>
                                                        <div class="check-overlay"><i class="fas fa-check-circle"></i></div>
                                                    </div>
                                                    <div style="font-weight:500; font-size:14px;"><?= htmlspecialchars($m['name']) ?></div>
                                                    <div style="font-size:12px; color:var(--gold); font-weight:600;">Rs.<?= number_format($m['price']) ?> / unit</div>
                                                    
                                                    <div class="qty-selector" id="qty-wrap-<?= $m['id'] ?>" style="display:none; margin-top:10px; padding-top:10px; border-top:1px solid var(--border2); justify-content:space-between; align-items:center;">
                                                        <span style="font-size:11px; color:var(--text3);">Quantity:</span>
                                                        <div style="display:flex; align-items:center; gap:5px;">
                                                            <button type="button" onclick="changeQty(event, '<?= $m['id'] ?>', -1)" style="width:24px; height:24px; border-radius:4px; border:1px solid var(--border2); background:var(--dark3); color:var(--text); cursor:pointer;">-</button>
                                                            <input type="number" id="input-qty-<?= $m['id'] ?>" value="1" min="1" 
                                                                   style="width:60px; text-align:center; background:var(--dark4); border:1px solid var(--border2); border-radius:4px; color:var(--gold); font-weight:bold; font-size:13px; outline:none;" 
                                                                   oninput="changeQty(event, '<?= $m['id'] ?>', 0)"
                                                                   onclick="event.stopPropagation();">
                                                            <button type="button" onclick="changeQty(event, '<?= $m['id'] ?>', 1)" style="width:24px; height:24px; border-radius:4px; border:1px solid var(--border2); background:var(--dark3); color:var(--text); cursor:pointer;">+</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; endif; ?>
                                </div>

                                <input type="hidden" name="selected_items" id="selected_items_input">

                                <div style="display:flex; justify-content:space-between; margin-top:30px;">
                                    <button type="button" class="btn btn-ghost" onclick="goToStep(1)">&larr; Back</button>
                                    <button type="button" class="btn btn-gold" onclick="goToStep(3)">Next: Choose Package &rarr;</button>
                                </div>
                            </div>

                            <!-- STEP 3: CHOOSE PACKAGE -->
                            <div class="step-content" id="step-3" style="display:none;">
                                <h2 style="margin-bottom:24px; font-size:24px;">Select a Catering Package</h2>
                                <div class="grid-3">
                                    <?php foreach($packages as $p): ?>
                                        <div class="pkg-selection-card pkg-select-card" 
                                             data-id="<?= htmlspecialchars($p['id']) ?>" 
                                             data-name="<?= htmlspecialchars($p['name']) ?>" 
                                             data-price="<?= (float)$p['price'] ?>" 
                                             onclick="selectPackageFromEl(this)">
                                            <div style="font-size:12px; color:var(--text3); margin-bottom:8px;">PACKAGE</div>
                                            <h3 style="color:var(--text); margin-bottom:10px;"><?= htmlspecialchars($p['name']) ?></h3>
                                            <div style="font-size:32px; color:var(--gold); font-family:'Playfair Display';">Rs.<?= number_format($p['price'], 0) ?><small style="font-size:14px; color:var(--text3)">/pp</small></div>
                                            <p style="font-size:12px; color:var(--text2); margin:15px 0;"><?= htmlspecialchars($p['description']) ?></p>
                                            <div style="border-top:1px solid var(--border2); padding-top:15px;">
                                                <?php foreach(json_decode($p['includes']) as $inc): ?>
                                                    <div style="font-size:11px; margin-bottom:5px;"><i class="fas fa-check" style="color:var(--green); margin-right:8px;"></i><?= htmlspecialchars($inc) ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                            <input type="radio" name="package_id" value="<?= $p['id'] ?>" id="pkg-radio-<?= $p['id'] ?>" style="display:none;">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" id="selected_package_name" name="package_name">
                                <input type="hidden" id="selected_package_price">
                                <div style="display:flex; justify-content:space-between; margin-top:30px;">
                                    <button type="button" class="btn btn-ghost" onclick="goToStep(2)">&larr; Back</button>
                                    <button type="button" class="btn btn-gold" onclick="goToStep(4)">Next: Confirm &rarr;</button>
                                </div>
                            </div>

                            <!-- STEP 4: CONFIRM -->
                            <div class="step-content" id="step-4" style="display:none;">
                                <h2 style="margin-bottom:24px; font-size:24px;">Confirm Your Booking</h2>
                                <div class="grid-2">
                                    <div class="table-card">
                                        <h3 style="margin-bottom:15px; font-size:16px; color:var(--gold);">Order Summary</h3>
                                        <div id="summary-details" style="line-height:2;">
                                            <!-- Injected by JS -->
                                        </div>
                                    </div>
                                    <div class="table-card">
                                        <h3 style="margin-bottom:15px; font-size:16px; color:var(--gold);">Selected Menu</h3>
                                        <div id="summary-menu" style="display:flex; flex-wrap:wrap; gap:8px;">
                                            <!-- Injected by JS -->
                                        </div>
                                    </div>
                                </div>
                                <div class="payment-card" style="margin-top:20px; display:flex; justify-content:space-between; align-items:center;">
                                    <div>
                                        <div style="font-size:14px; color:var(--text3);">Estimated Total</div>
                                        <div id="final-amount" style="font-size:32px; font-family:'Playfair Display'; color:var(--gold);">$0</div>
                                    </div>
                                    <button type="submit" class="btn btn-gold" style="padding:15px 40px;">Place Booking Order</button>
                                </div>
                                <div style="margin-top:20px;">
                                    <button type="button" class="btn btn-ghost" onclick="goToStep(3)">&larr; Back to Menu</button>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.order-wizard { background:var(--surface); border:1px solid var(--border2); border-radius:var(--r); overflow:hidden; }
.wizard-steps { display:flex; background:var(--dark3); border-bottom:1px solid var(--border2); }
.wizard-step { flex:1; padding:20px; text-align:center; font-size:14px; color:var(--text3); font-weight:500; cursor:pointer; position:relative; transition:.3s; }
.wizard-step.active { color:var(--gold); background:rgba(212,168,67,0.05); }
.wizard-step.active::after { content:''; position:absolute; bottom:0; left:0; width:100%; height:3px; background:var(--gold); }
.wizard-step.done { color:var(--green); }

.menu-item-card { 
    background:var(--surface); 
    border:1px solid var(--border2); 
    border-radius:var(--r); 
    padding:20px; 
    transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    opacity: 1 !important;
    visibility: visible !important;
}
.menu-select-card { cursor:pointer; border:2px solid transparent; }
.menu-select-card:hover { border-color:var(--gold); transform: translateY(-4px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
.menu-select-card.selected { border-color:var(--gold); background:rgba(212,168,67,0.05); }

.pkg-selection-card { 
    background:var(--surface); 
    border:2px solid transparent; 
    border-radius:var(--r); 
    padding:24px; 
    transition: 0.3s;
    cursor: pointer;
    opacity: 1 !important;
    visibility: visible !important;
}
.pkg-select-card:hover { border-color: var(--border); }
.pkg-select-card.selected { border-color:var(--gold); background:rgba(212,168,67,0.05); }
.pkg-select-card.selected::after { content:'\f058'; font-family:'Font Awesome 6 Free'; font-weight:900; position:absolute; top:10px; right:10px; color:var(--gold); font-size:20px; }
.menu-select-card.selected .check-overlay { opacity:1; }

.check-overlay { position:absolute; inset:0; background:rgba(212,168,67,0.4); display:flex; align-items:center; justify-content:center; font-size:32px; color:#fff; opacity:0; transition:.2s; }

.filter-btn.active { background:rgba(212,168,67,0.1); border-color:var(--gold); color:var(--gold); }
</style>

<script>
let currentStep = 1;
let selectedItems = new Map();

// Debug data from PHP
console.log('Menu Data:', <?= json_encode($menu_by_cat) ?>);
console.log('Package Data:', <?= json_encode($packages) ?>);

function goToStep(n) {
    if (n > currentStep && !validateStep(currentStep)) return;
    
    document.querySelectorAll('.step-content').forEach(s => s.style.display = 'none');
    document.getElementById('step-' + n).style.display = 'block';
    
    document.querySelectorAll('.wizard-step').forEach((s, idx) => {
        s.classList.remove('active');
        if(idx + 1 === n) s.classList.add('active');
        if(idx + 1 < n) s.classList.add('done');
    });
    
    currentStep = n;
    if(n === 4) updateSummary();
}

function validateStep(n) {
    if(n === 1) {
        const guests = document.getElementById('guests_input').value;
        if(!guests || guests < 1) { toast('Please enter guest count', 'error'); return false; }
    }
    if(n === 3) {
        if(!document.getElementById('selected_package_name').value) { toast('Please select a package', 'error'); return false; }
    }
    return true;
}

function selectPackageFromEl(el) {
    const id = el.dataset.id;
    const name = el.dataset.name;
    const price = el.dataset.price;
    
    document.querySelectorAll('.pkg-select-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    
    const radio = el.querySelector('input[type="radio"]');
    if(radio) radio.checked = true;
    
    document.getElementById('selected_package_name').value = name;
    document.getElementById('selected_package_price').value = price;
}

function selectPackage(id, name, price) {
    // Compatibility
    const el = Array.from(document.querySelectorAll('.pkg-select-card')).find(c => c.dataset.id === id);
    if(el) selectPackageFromEl(el);
}

function showMenuCat(catName, btn) {
    console.log('Switching to category:', catName);
    document.querySelectorAll('.menu-cat-grid').forEach(g => {
        if(g.dataset.category === catName) {
            g.style.display = 'grid';
            console.log('Match found for:', catName);
        } else {
            g.style.display = 'none';
        }
    });
    document.querySelectorAll('#menu-cats .filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

function toggleMenuItemFromEl(el) {
    const id = el.dataset.id;
    const name = el.dataset.name;
    const price = parseFloat(el.dataset.price || 0);
    const qtyWrap = document.getElementById('qty-wrap-' + id);

    if (selectedItems.has(id)) {
        selectedItems.delete(id);
        el.classList.remove('selected');
        if(qtyWrap) qtyWrap.style.display = 'none';
    } else {
        selectedItems.set(id, { name, price, qty: 1 });
        el.classList.add('selected');
        if(qtyWrap) qtyWrap.style.display = 'flex';
        document.getElementById('input-qty-' + id).value = 1;
    }
    syncHiddenInput();
}

function changeQty(event, id, delta) {
    event.stopPropagation();
    const input = document.getElementById('input-qty-' + id);
    let val = parseInt(input.value || 0);
    
    // If it's a button press (+/-), we apply delta and floor it at 1
    if (delta !== 0) {
        val += delta;
        if (val < 1) val = 1;
        input.value = val;
    } else {
        // If it's manual typing (oninput), we just take the value
        // but we allow 0 or empty while typing, and we'll clean it up later if needed
    }
    
    if (selectedItems.has(id)) {
        let item = selectedItems.get(id);
        item.qty = val;
        selectedItems.set(id, item);
    }
    syncHiddenInput();
}

function syncHiddenInput() {
    // Send as JSON for easier parsing of quantities
    const itemsArray = [];
    selectedItems.forEach((val, key) => {
        itemsArray.push({ id: key, qty: val.qty });
    });
    document.getElementById('selected_items_input').value = JSON.stringify(itemsArray);
    console.log('Selected Items:', itemsArray);
}

function toggleMenuItem(id, name, emoji) {
    // Keep for backward compatibility if needed, but we use toggleMenuItemFromEl now
    const el = document.getElementById('mi-' + id);
    if(el) toggleMenuItemFromEl(el);
}

function updateSummary() {
    const guests = parseInt(document.getElementById('guests_input').value || 0);
    const pkg = document.getElementById('selected_package_name').value;
    const pkgPrice = parseFloat(document.getElementById('selected_package_price').value || 0);
    
    let menuItemsTotal = 0;
    let menuHTML = '';
    
    selectedItems.forEach(item => {
        const subtotal = item.price * item.qty;
        menuItemsTotal += subtotal;
        menuHTML += `<div style="background:var(--dark3); padding:10px; border-radius:8px; border:1px solid var(--border2); display:flex; justify-content:space-between; align-items:center; width:100%;">
            <div>
                <div style="font-size:13px; color:var(--text);">${item.name}</div>
                <div style="font-size:11px; color:var(--text3);">Rs.${item.price} x ${item.qty} units</div>
            </div>
            <span style="font-size:13px; color:var(--gold); font-weight:600;">Rs.${subtotal.toLocaleString()}</span>
        </div>`;
    });

    const advanceTotal = pkgPrice * guests;
    const subtotal = advanceTotal + menuItemsTotal;
    const discount = subtotal * 0.20;
    const finalTotal = subtotal - discount;

    document.getElementById('summary-details').innerHTML = `
        <div style="display:flex; justify-content:space-between;"><span>Event Type:</span><strong>${document.querySelector('[name="event_type"]').value}</strong></div>
        <div style="display:flex; justify-content:space-between;"><span>Date:</span><strong>${document.querySelector('[name="event_date"]').value}</strong></div>
        <div style="display:flex; justify-content:space-between;"><span>Guests:</span><strong>${guests}</strong></div>
        <div style="margin:15px 0; border-top:1px dashed var(--border2);"></div>
        <div style="display:flex; justify-content:space-between;"><span>Package (${pkg}):</span><strong>Rs.${pkgPrice} x ${guests}</strong></div>
        <div style="display:flex; justify-content:space-between; margin-top:10px; font-weight:600;"><span>Total Amount:</span><strong>Rs.${subtotal.toLocaleString()}</strong></div>
        <div style="display:flex; justify-content:space-between; color:var(--green); font-size:13px;"><span>(-) Promo Discount (20%):</span><strong>Rs.${discount.toLocaleString()}</strong></div>
        <div style="display:flex; justify-content:space-between; color:var(--gold); font-size:20px; font-weight:bold; margin-top:10px; border-top:2px solid var(--border2); padding-top:10px;"><span>Grand Total:</span><strong>Rs.${finalTotal.toLocaleString()}</strong></div>
        <div style="margin-top:10px; font-size:11px; color:var(--text3); text-align:right;">(Total minus Discount Amount)</div>
        <div style="margin:15px 0; border-top:1px dashed var(--border2);"></div>
        <div style="display:flex; justify-content:space-between; color:var(--text2);"><span>Advance Payable Now:</span><strong>Rs.${advanceTotal.toLocaleString()}</strong></div>
    `;

    document.getElementById('summary-menu').innerHTML = menuHTML || '<p style="color:var(--text3); width:100%; text-align:center;">No specific items selected.</p>';
    document.getElementById('final-amount').textContent = 'Rs.' + finalTotal.toLocaleString();
}
</script>

<?php require_once '../includes/footer.php'; ?>
