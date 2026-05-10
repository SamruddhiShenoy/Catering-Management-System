<?php $base_url = '/Catering_Management_System'; ?>
<footer class="public-footer">
    <p>&copy; <?= date('Y') ?> CaterBook. All rights reserved.</p>
</footer>

<!-- AI Chatbot Widget -->
<div class="chat-widget">
    <div class="chat-window" id="chatWindow">
        <div class="chat-header">
            <h3><i class="fas fa-robot"></i> LuxeBot AI</h3>
            <button class="chat-close" onclick="toggleChat()">&times;</button>
        </div>
        <div class="chat-body" id="chatBody">
            <div class="chat-msg bot">Hi there! How can CaterBook make your next event unforgettable?</div>
        </div>
        <div class="chat-input-area">
            <input type="text" class="chat-input" id="chatInput" placeholder="Ask anything...">
            <button class="chat-send" onclick="sendChat()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
    <button class="chat-btn" onclick="toggleChat()">💬</button>
</div>

<!-- Removed toast.js external reference, now inline in header -->
<script src="<?= $base_url ?>/js/3d-background.js?v=<?= time() ?>"></script>
<script src="<?= $base_url ?>/js/animations.js?v=<?= time() ?>"></script>
<script src="<?= $base_url ?>/js/app.js?v=<?= time() ?>"></script>
<script src="<?= $base_url ?>/js/chatbot.js?v=<?= time() ?>"></script>
<script src="<?= $base_url ?>/js/components/notifications.js?v=<?= time() ?>"></script>
<script>
    // Show toasts from PHP sessions if they exist
    <?php if(isset($_SESSION['toast'])): ?>
        toast("<?= addslashes($_SESSION['toast']['msg']) ?>", "<?= $_SESSION['toast']['type'] ?>");
        <?php unset($_SESSION['toast']); ?>
    <?php endif; ?>
</script>
</body>
</html>
