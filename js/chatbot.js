function toggleChat() {
    const window = document.getElementById('chatWindow');
    window.classList.toggle('active');
    if (window.classList.contains('active')) {
        document.getElementById('chatInput').focus();
    }
}

function sendChat() {
    const input = document.getElementById('chatInput');
    const msg = input.value.trim();
    if (!msg) return;

    addMessage(msg, 'user');
    input.value = '';

    // Simulated AI Response
    setTimeout(() => {
        showTyping();
        setTimeout(() => {
            removeTyping();
            const response = getAIResponse(msg);
            addMessage(response, 'bot');
        }, 1500);
    }, 500);
}

function addMessage(text, type) {
    const body = document.getElementById('chatBody');
    const msgDiv = document.createElement('div');
    msgDiv.className = `chat-msg ${type}`;
    msgDiv.textContent = text;
    body.appendChild(msgDiv);
    body.scrollTop = body.scrollHeight;
}

function showTyping() {
    const body = document.getElementById('chatBody');
    const typingDiv = document.createElement('div');
    typingDiv.className = 'chat-msg bot typing';
    typingDiv.id = 'typingIndicator';
    typingDiv.innerHTML = '<span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span>';
    body.appendChild(typingDiv);
    body.scrollTop = body.scrollHeight;
}

function removeTyping() {
    const typing = document.getElementById('typingIndicator');
    if (typing) typing.remove();
}

function getAIResponse(msg) {
    const m = msg.toLowerCase();
    if (m.includes('hello') || m.includes('hi')) return "Hello! I'm LuxeBot, your personal catering assistant. How can I help you today?";
    if (m.includes('price') || m.includes('cost')) return "Our packages range from $45 to $150 per person. You can see full details in the 'Packages' section.";
    if (m.includes('menu') || m.includes('food')) return "We offer everything from Truffle Bruschetta to Grand Buffet Displays. Log in to your portal to browse the full menu!";
    if (m.includes('book')) return "You can book an event by logging into your portal and navigating to the 'Book Event' page.";
    if (m.includes('wedding')) return "Weddings are our specialty! Our Elite package includes a 7-course gourmet menu and full decor services.";
    return "That's a great question! I'm still learning, but our team can give you more details. Would you like me to connect you with a coordinator?";
}

// Handle Enter key
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('chatInput');
    if (input) {
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendChat();
        });
    }
});
