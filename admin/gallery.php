<?php
require_once '../config.php';
requireAdmin();

$page_title = 'Event Gallery Manager';
$page_subtitle = 'Upload and manage photography for client events.';

// Fetch all bookings for the selector
$stmt = $pdo->query("SELECT b.id, b.event, u.name as client_name FROM bookings b JOIN users u ON b.client_id = u.id ORDER BY b.date DESC");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle deletion
if (isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("SELECT file_path FROM event_gallery WHERE id = ?");
    $stmt->execute([$_POST['delete_id']]);
    $photo = $stmt->fetch();
    
    if ($photo) {
        $full_path = '../' . $photo['file_path'];
        if (file_exists($full_path)) {
            unlink($full_path);
        }
        $stmt = $pdo->prepare("DELETE FROM event_gallery WHERE id = ?");
        $stmt->execute([$_POST['delete_id']]);
        $_SESSION['toast'] = ['msg' => 'Photo deleted successfully.', 'type' => 'info'];
    }
    header("Location: gallery.php");
    exit;
}

// Fetch existing gallery items
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
                <!-- Upload Section -->
                <div class="table-card mb-28">
                    <div class="card-header">
                        <h3><i class="fas fa-upload" style="color:var(--gold); margin-right: 10px;"></i> Upload Event Photos</h3>
                    </div>
                    <form id="uploadForm" class="grid-2" style="gap: 20px; align-items: flex-end; padding: 20px;">
                        <div class="form-group-dark">
                            <label>Select Event</label>
                            <select name="booking_id" class="form-input-dark" required>
                                <option value="">-- Choose Booking --</option>
                                <?php foreach($bookings as $b): ?>
                                    <option value="<?= $b['id'] ?>"><?= $b['id'] ?> - <?= htmlspecialchars($b['event']) ?> (<?= htmlspecialchars($b['client_name']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group-dark">
                            <label>Choose Image</label>
                            <input type="file" name="photo" class="form-input-dark" accept="image/*" required>
                        </div>
                        <div style="grid-column: span 2;">
                            <button type="submit" class="btn btn-gold"><i class="fas fa-cloud-upload-alt"></i> Upload to Gallery</button>
                        </div>
                    </form>
                    <div id="uploadStatus" style="padding: 0 20px 20px; display: none;">
                        <div class="inv-bar"><div id="uploadProgress" class="inv-fill" style="width: 0%; background: var(--gold);"></div></div>
                    </div>
                </div>

                <!-- Gallery Grid -->
                <div class="grid-3">
                    <?php if(empty($gallery)): ?>
                        <div style="grid-column: span 3; text-align: center; padding: 60px; background: var(--surface); border-radius: var(--r); border: 1px dashed var(--border);">
                            <i class="fas fa-images" style="font-size: 48px; color: var(--dark5); margin-bottom: 16px;"></i>
                            <p style="color: var(--text3);">No photos in the gallery yet. Start uploading!</p>
                        </div>
                    <?php endif; ?>

                    <?php foreach($gallery as $img): ?>
                        <div class="table-card" style="padding: 0; overflow: hidden; position: relative;">
                            <img src="../<?= $img['file_path'] ?>" alt="Event Photo" style="width: 100%; height: 200px; object-fit: cover; display: block;">
                            <div style="padding: 15px;">
                                <div style="font-size: 14px; font-weight: 600; color: var(--text);"><?= htmlspecialchars($img['event']) ?></div>
                                <div style="font-size: 12px; color: var(--text3); margin-bottom: 10px;"><?= $img['date'] ?></div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span class="tag tag-blue" style="font-size: 10px;"><?= $img['booking_id'] ?></span>
                                    <form method="POST" onsubmit="return confirm('Delete this photo?');">
                                        <input type="hidden" name="delete_id" value="<?= $img['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-ghost btn-icon" style="color: var(--accent);"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const statusDiv = document.getElementById('uploadStatus');
    const progressBar = document.getElementById('uploadProgress');
    
    statusDiv.style.display = 'block';
    progressBar.style.width = '0%';

    try {
        const response = await fetch('../api/upload_handler.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            progressBar.style.width = '100%';
            progressBar.style.background = 'var(--green)';
            setTimeout(() => location.reload(), 800);
        } else {
            toast(result.message, 'error');
            statusDiv.style.display = 'none';
        }
    } catch (error) {
        toast('Upload failed.', 'error');
        statusDiv.style.display = 'none';
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
