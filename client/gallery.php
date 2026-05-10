<?php
require_once '../config.php';
requireLogin();

$page_title = 'My Event Gallery';
$page_subtitle = 'Relive the moments from your special occasions.';
$client_id = $_SESSION['user_id'];

// Fetch all photos to serve as a portfolio for the clients
$stmt = $pdo->query("
    SELECT g.*, b.event, b.date 
    FROM event_gallery g 
    JOIN bookings b ON g.booking_id = b.id 
    ORDER BY g.uploaded_at DESC
");
$gallery = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="page active">
    <div style="display:flex;">
        <?php require_once '../includes/sidebar.php'; ?>
        <div class="main-content">
            <?php require_once '../includes/topbar.php'; ?>
            
            <div class="content-area">
                <div class="grid-3">
                    <?php if(empty($gallery)): ?>
                        <div style="grid-column: span 3; text-align: center; padding: 100px 20px; background: var(--surface); border-radius: var(--r); border: 1px solid var(--border);">
                            <div style="font-size: 64px; margin-bottom: 24px; opacity: 0.2;">📸</div>
                            <h3 style="color: var(--text); margin-bottom: 12px;">Your Gallery is Empty</h3>
                            <p style="color: var(--text3); max-width: 400px; margin: 0 auto;">Photography from your events will appear here once our team uploads them. Stay tuned!</p>
                        </div>
                    <?php endif; ?>

                    <?php foreach($gallery as $img): ?>
                        <div class="table-card" style="padding: 0; overflow: hidden; border-radius: var(--r); cursor: pointer;" onclick="openLightbox('<?= $base_url ?>/<?= $img['file_path'] ?>')">
                            <div style="position: relative; overflow: hidden;">
                                <img src="<?= $base_url ?>/<?= $img['file_path'] ?>" alt="Event Photo" style="width: 100%; height: 250px; object-fit: cover; display: block; transition: 0.5s;" class="gallery-thumb">
                                <div class="gallery-overlay" style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(10,10,15,0.9), transparent); opacity: 0; transition: 0.3s; display: flex; flex-direction: column; justify-content: flex-end; padding: 20px;">
                                    <div style="color: var(--gold); font-family: 'Playfair Display', serif; font-size: 18px;"><?= htmlspecialchars($img['event']) ?></div>
                                    <div style="color: var(--text2); font-size: 12px;"><?= $img['date'] ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Simple Lightbox -->
<div id="lightbox" style="position: fixed; inset: 0; background: rgba(0,0,0,0.95); z-index: 10000; display: none; align-items: center; justify-content: center; padding: 40px; cursor: pointer;" onclick="this.style.display='none'">
    <img id="lightboxImg" src="" style="max-width: 100%; max-height: 100%; border-radius: 8px; box-shadow: 0 0 50px rgba(0,0,0,1);">
    <div style="position: absolute; top: 20px; right: 20px; color: #fff; font-size: 30px;">&times;</div>
</div>

<style>
.table-card:hover .gallery-thumb { transform: scale(1.1); }
.table-card:hover .gallery-overlay { opacity: 1; }
</style>

<script>
function openLightbox(src) {
    const lb = document.getElementById('lightbox');
    const img = document.getElementById('lightboxImg');
    img.src = src;
    lb.style.display = 'flex';
}
</script>

<?php require_once '../includes/footer.php'; ?>
