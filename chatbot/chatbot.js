// chatbot.js - Professional, Upgraded Version
document.addEventListener('DOMContentLoaded', () => {
    // DOM Elements
    const chatWindow = document.getElementById('chatbot-window');
    const fab = document.getElementById('chatbot-fab');
    const closeBtn = document.getElementById('chatbot-close-btn');
    const messagesContainer = document.getElementById('chatbot-messages');
    const input = document.getElementById('chatbot-input');
    const sendBtn = document.getElementById('chatbot-send-btn');

    // Configuration
    const API_URL = '../chatbot/chatbot.php'; // Correct path to your PHP API
    let isWaitingForResponse = false;

    // Utility Functions
    function formatTime() {
        return new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }

    // Event Listeners
    fab.addEventListener('click', () => toggleChat(true));
    closeBtn.addEventListener('click', () => toggleChat(false));
    sendBtn.addEventListener('click', handleUserInput);
    
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !isWaitingForResponse) {
            e.preventDefault();
            handleUserInput();
        }
    });

    // Chat Functions
    function toggleChat(show) {
        if (show) {
            chatWindow.classList.remove('hidden');
            fab.classList.add('hidden');
            input.focus();
            
            if (messagesContainer.children.length === 0) {
                setTimeout(() => {
                    addMessage(
                        "Hello! I'm VietTransit's travel assistant. I can help you find tours and hotels in Vietnam. What would you like to explore today?", 
                        'bot',
                        false
                    );
                }, 500);
            }
        } else {
            chatWindow.classList.add('hidden');
            fab.classList.remove('hidden');
        }
    }

    async function handleUserInput() {
        const query = input.value.trim();
        if (!query || isWaitingForResponse) return;

        addMessage(query, 'user');
        input.value = '';
        await sendMessage(query);
    }

    async function sendMessage(query) {
        setInputState(false);
        showTypingIndicator();

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ query: query })
            });

            hideTypingIndicator();

            if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
            
            const data = await response.json();

            if (data.error) throw new Error(data.error);

            // *** MAJOR CHANGE HERE ***
            // Pass the isHTML flag to the addMessage function
            addMessage(data.reply, 'bot', data.isHTML || false);

        } catch (error) {
            hideTypingIndicator();
            console.error('Chat error:', error);
            addMessage("I'm sorry, I'm having trouble connecting right now. Please try again in a moment.", 'bot', false);
        } finally {
            setInputState(true);
        }
    }

    function setInputState(enabled) {
        isWaitingForResponse = !enabled;
        input.disabled = !enabled;
        sendBtn.disabled = !enabled;
        
        if (enabled) {
            input.focus();
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        } else {
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        }
    }

    let typingIndicatorId = null;
    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingIndicatorId = 'typing-' + Date.now();
        typingDiv.id = typingIndicatorId;
        typingDiv.className = 'chatbot-message bot-message typing-indicator';
        typingDiv.innerHTML = `<div class="typing-dots"><span></span><span></span><span></span></div>`;
        
        messagesContainer.appendChild(typingDiv);
        scrollToBottom();
    }

    function hideTypingIndicator() {
        if (typingIndicatorId) {
            const typingElement = document.getElementById(typingIndicatorId);
            if (typingElement) typingElement.remove();
            typingIndicatorId = null;
        }
    }

    /**
     * MAJORLY UPDATED FUNCTION
     * Adds a message to the chat, supporting either plain text or HTML.
     * @param {string} text - The message content.
     * @param {string} sender - 'user' or 'bot'.
     * @param {boolean} isHTML - If true, the text will be rendered as HTML.
     */
    function addMessage(text, sender, isHTML = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chatbot-message ${sender}-message`;
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        
        // This is the key change: render HTML if the flag is set,
        // otherwise use textContent to prevent security risks (XSS).
        if (sender === 'bot' && isHTML) {
            contentDiv.innerHTML = text;
        } else {
            contentDiv.textContent = text;
        }
        
        const timeStamp = document.createElement('div');
        timeStamp.className = 'message-timestamp';
        timeStamp.textContent = formatTime();
        
        messageDiv.appendChild(contentDiv);
        messageDiv.appendChild(timeStamp);
        messagesContainer.appendChild(messageDiv);
        
        scrollToBottom();
    }

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    console.log('VietTransit Professional Chatbot initialized');
});