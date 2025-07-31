// chatbot.js - Fixed English version for LLM Backend on port 3000
document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Element References ---
    const chatWindow = document.getElementById('chatbot-window');
    const fab = document.getElementById('chatbot-fab');
    const closeBtn = document.getElementById('chatbot-close-btn');
    const messagesContainer = document.getElementById('chatbot-messages');
    const input = document.getElementById('chatbot-input');
    const sendBtn = document.getElementById('chatbot-send-btn');
    const statusIndicator = document.getElementById('chatbot-status'); // CHANGED: Added status indicator reference

    // --- Configuration - Updated for port 3000 ---
    const API_URL = 'http://localhost:3000/api/chat';
    const HEALTH_URL = 'http://localhost:3000/api/health';
    const MAX_RETRIES = 3;
    const RETRY_DELAY = 1000; // ms

    // --- State Management ---
    let sessionId = generateSessionId();
    let isWaitingForResponse = false;
    let retryCount = 0;
    let lastUserQuery = ''; // CHANGED: To store the last user query for retry

    // --- Utility Functions ---
    function generateSessionId() {
        return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    function formatTime() {
        return new Date().toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }

    function sanitizeHTML(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // --- Event Listeners ---
    fab.addEventListener('click', () => toggleChat(true));
    closeBtn.addEventListener('click', () => toggleChat(false));
    sendBtn.addEventListener('click', handleUserInput);
    
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleUserInput();
        }
    });

    // Auto-resize input
    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 120) + 'px';
    });

    // --- Chat Functions ---
    function toggleChat(show) {
        if (show) {
            chatWindow.classList.remove('hidden');
            fab.classList.add('hidden');
            input.focus();
            
            // Send welcome message only if chat is empty
            if (messagesContainer.children.length === 0) {
                setTimeout(() => {
                    addMessage(
                        "Hello! I'm VietTransit's AI assistant. I can help you find tour packages and hotels. How can I help you today?", 
                        'bot'
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

        lastUserQuery = query; // CHANGED: Store query for potential retry
        addMessage(query, 'user');
        input.value = '';
        input.style.height = 'auto';
        await sendMessage(query);
    }
    
    // CHANGED: Separated sending logic to allow for easier retries
    async function sendMessage(query) {
        setInputState(false);
        const typingId = showTypingIndicator();

        try {
            const response = await sendMessageWithRetry(query);
            removeTypingIndicator(typingId);
            
            if (response && response.reply) {
                addMessage(response.reply, 'bot');
                retryCount = 0; // Reset retry count on success
            } else {
                throw new Error('Invalid response format');
            }
        } catch (error) {
            removeTypingIndicator(typingId);
            handleError(error);
        } finally {
            setInputState(true);
        }
    }


    async function sendMessageWithRetry(query) {
        for (let attempt = 1; attempt <= MAX_RETRIES; attempt++) {
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout

                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        query: query,
                        session_id: sessionId 
                    }),
                    signal: controller.signal
                });

                clearTimeout(timeoutId);

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({})); // Try to parse error
                    throw new Error(`HTTP ${response.status}: ${errorData.error || response.statusText}`);
                }

                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error);
                }

                return data;

            } catch (error) {
                console.warn(`Attempt ${attempt} failed:`, error.message);
                
                if (attempt === MAX_RETRIES) {
                    throw error;
                }
                
                await new Promise(resolve => setTimeout(resolve, RETRY_DELAY * attempt));
            }
        }
    }

    function setInputState(enabled) {
        isWaitingForResponse = !enabled;
        input.disabled = !enabled;
        sendBtn.disabled = !enabled;
        
        if (enabled) {
            input.focus();
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            sendBtn.title = 'Send message';
        } else {
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            sendBtn.title = 'Processing...';
        }
    }

    function showTypingIndicator() {
        const typingId = 'typing_' + Date.now();
        const messageDiv = document.createElement('div');
        messageDiv.className = 'chatbot-message bot-message typing-indicator';
        messageDiv.id = typingId;
        messageDiv.innerHTML = `
            <div class="typing-dots">
                <span></span>
                <span></span>
                <span></span>
            </div>
        `;
        
        messagesContainer.appendChild(messageDiv);
        scrollToBottom();
        return typingId;
    }

    function removeTypingIndicator(typingId) {
        const element = document.getElementById(typingId);
        if (element) {
            element.remove();
        }
    }

    function addMessage(text, sender, isHTML = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chatbot-message ${sender}-message`;
        
        const timeStamp = document.createElement('div');
        timeStamp.className = 'message-timestamp';
        timeStamp.textContent = formatTime();
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        
        if (isHTML && sender === 'bot') {
            // Allow HTML for bot responses (for links, formatting)
            contentDiv.innerHTML = text;
        } else {
            // Sanitize all other input to prevent XSS
            contentDiv.textContent = text;
        }
        
        messageDiv.appendChild(contentDiv);
        messageDiv.appendChild(timeStamp);
        
        messagesContainer.appendChild(messageDiv);
        scrollToBottom();
        
        // Add animation
        requestAnimationFrame(() => {
            messageDiv.style.opacity = '0';
            messageDiv.style.transform = 'translateY(20px)';
            requestAnimationFrame(() => {
                messageDiv.style.transition = 'all 0.3s ease';
                messageDiv.style.opacity = '1';
                messageDiv.style.transform = 'translateY(0)';
            });
        });
    }

    function handleError(error) {
        console.error('Chat error:', error);
        
        let errorMessage;
        if (error.message.includes('Failed to fetch') || error.name === 'AbortError') {
            errorMessage = "I can't connect to the server. Please check your internet connection or try again later.";
        } else if (error.message.includes('HTTP 500')) {
            errorMessage = "The server is having some trouble. Please try again in a few minutes.";
        } else if (error.message.includes('HTTP 429')) {
            errorMessage = "You're sending messages too fast! Please wait a moment.";
        } else {
            errorMessage = "I'm sorry, an unexpected error occurred. Please try sending your message again.";
        }
        
        addMessage(errorMessage, 'bot');
        
        // CHANGED: Added a more functional retry button
        if (retryCount < MAX_RETRIES) {
            retryCount++;
            const retryDiv = document.createElement('div');
            retryDiv.className = 'chatbot-message system-message';
            const retryButton = document.createElement('button');
            retryButton.className = 'retry-button';
            retryButton.innerHTML = '🔄 Try Again';
            retryButton.onclick = () => {
                retryDiv.remove(); // Remove the retry button on click
                sendMessage(lastUserQuery);
            };
            retryDiv.appendChild(retryButton);
            messagesContainer.appendChild(retryDiv);
            scrollToBottom();
        }
    }


    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // --- Health Check ---
    async function checkServerHealth() {
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 second timeout
            
            const response = await fetch(HEALTH_URL, { signal: controller.signal });
            clearTimeout(timeoutId);

            if (response.ok) {
                const data = await response.json();
                if (data.status === 'healthy') {
                    statusIndicator.classList.add('online');
                    statusIndicator.classList.remove('offline');
                    statusIndicator.title = 'Online';
                    return;
                }
            }
            throw new Error('Health check failed');
        } catch (error) {
            console.warn('Health check failed:', error.message);
            statusIndicator.classList.remove('online');
            statusIndicator.classList.add('offline');
            statusIndicator.title = 'Offline - Server might be down';
        }
    }

    // --- Initialization ---
    function initialize() {
        console.log('VietTransit Chatbot initialized');
        console.log('Session ID:', sessionId);
        console.log('API URL:', API_URL);
        
        // Check server health on startup
        checkServerHealth();
        
        // Periodic health check (every 2 minutes)
        setInterval(checkServerHealth, 2 * 60 * 1000);
    }

    // Initialize the chatbot
    initialize();
});