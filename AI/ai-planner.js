// Enhanced Global Functions with Proper URL Construction and Data Persistence
function bookTour(tourId, cityId) {
    if (!tourId || !cityId) {
        alert('Tour information not available');
        return;
    }
    
    saveChatDataBeforeBooking();
    const baseUrl = window.location.origin;
    window.location.href = `${baseUrl}/tour/booking?cityid=${cityId}&tourid=${tourId}`;
}

function viewTourDetails(tourId, cityId) {
    if (!tourId || !cityId) {
        alert('Tour details not available');
        return;
    }
    
    const cityIdToString = {
        10: 'taybac',   11: 'hcm',   12: 'nhatrang',   13: 'hue',   14: 'phuyen',   15: 'dalat',   16: 'phuquoc',  17: 'hoian',
        18: 'hagiang',  19: 'danang',20: 'cantho',     21: 'hanoi'
    };
    
    const cityString = cityIdToString[cityId] || 'hcm';
    saveChatDataBeforeBooking();
    const baseUrl = window.location.origin;
    window.location.href = `${baseUrl}/Journey/tour_detail.php?cityid=${cityString}&tourid=${tourId}`;
}

function viewHotelDetails(hotelId) {
    if (!hotelId) {
        alert('Hotel details not available');
        return;
    }
    
    saveChatDataBeforeBooking();
    const baseUrl = window.location.origin;
    window.location.href = `${baseUrl}/hotelinfo/hoteldescription.php?hotel_id=${hotelId}`;
}

function bookHotel(hotelId) {
    if (!hotelId) {
        alert('Hotel information not available');
        return;
    }
    
    saveChatDataBeforeBooking();
    const baseUrl = window.location.origin;
    window.location.href = `${baseUrl}/hotel/booking?hotel_id=${hotelId}`;
}

function exploreTours(destination) {
    const chatbot = window.travelChatbot;
    if (chatbot && destination) {
        chatbot.messageInput.value = `Show me tour packages to ${destination}`;
        chatbot.sendMessage();
    }
}

function findHotels(destination) {
    const chatbot = window.travelChatbot;
    if (chatbot && destination) {
        chatbot.messageInput.value = `Find accommodation in ${destination}`;
        chatbot.sendMessage();
    }
}

// Data Persistence Functions
function saveChatDataBeforeBooking() {
    const chatbot = window.travelChatbot;
    if (chatbot) {
        const chatData = {
            conversationHistory: chatbot.conversationHistory,
            messagesHTML: chatbot.messages.innerHTML,
            currentChatId: chatbot.currentChatId,
            timestamp: new Date().getTime()
        };
        
        try {
            sessionStorage.setItem('chatData', JSON.stringify(chatData));
            console.log('Chat data saved successfully');
        } catch (error) {
            console.error('Error saving chat data:', error);
        }
    }
}

function restoreChatDataAfterBooking() {
    try {
        const savedData = sessionStorage.getItem('chatData');
        if (savedData) {
            const chatData = JSON.parse(savedData);
            const chatbot = window.travelChatbot;
            
            if (chatbot && chatData.timestamp) {
                const currentTime = new Date().getTime();
                const timeDiff = currentTime - chatData.timestamp;
                const maxAge = 24 * 60 * 60 * 1000; // 24 hours
                
                if (timeDiff < maxAge) {
                    chatbot.conversationHistory = chatData.conversationHistory || [];
                    chatbot.currentChatId = chatData.currentChatId || null;
                    
                    if (chatData.messagesHTML) {
                        chatbot.messages.innerHTML = chatData.messagesHTML;
                        chatbot.hideWelcomeScreen();
                        chatbot.scrollToBottom();
                    }
                    
                    console.log('Chat data restored successfully');
                } else {
                    sessionStorage.removeItem('chatData');
                }
            }
        }
    } catch (error) {
        console.error('Error restoring chat data:', error);
    }
}

class TravelChatbot {
    constructor() {
        // Fixed API endpoint 
        this.apiEndpoint = './rag_chatbot_backend_refactored.php';
        this.currentChatId = null;
        this.isTyping = false;
        this.conversationHistory = [];
        this.chatHistoryList = [];
        this.editingChatId = null;
        this.initialized = false;
        
        this.initializeElements();
        this.bindEvents();
        this.loadChatHistory();
        this.checkForRestoredData();
    }
    
    initializeElements() {
        // Wait for DOM elements to be available
        this.waitForElements().then(() => {
            this.welcomeScreen = document.getElementById('welcomeScreen');
            this.messagesContainer = document.getElementById('messagesContainer');
            this.messages = document.getElementById('messages');
            this.messageInput = document.getElementById('messageInput');
            this.sendBtn = document.getElementById('sendBtn');
            this.loadingModal = document.getElementById('loadingModal');
            this.chatList = document.getElementById('chatList');
            this.newChatBtn = document.getElementById('newChatBtn');
            
            this.initialized = true;
            
            // Initialize after elements are found
            this.bindEvents();
            this.loadChatHistory();
            this.checkForRestoredData();
        }).catch(error => {
            console.error('Failed to initialize chatbot elements:', error);
        });
    }
    
    waitForElements(maxWait = 5000) {
        return new Promise((resolve, reject) => {
            const startTime = Date.now();
            
            const checkElements = () => {
                const requiredElements = [
                    'welcomeScreen', 'messagesContainer', 'messages',
                    'messageInput', 'sendBtn', 'chatList', 'newChatBtn'
                ];
                
                const missingElements = requiredElements.filter(id => !document.getElementById(id));
                
                if (missingElements.length === 0) {
                    resolve();
                } else if (Date.now() - startTime > maxWait) {
                    reject(new Error(`Missing elements: ${missingElements.join(', ')}`));
                } else {
                    setTimeout(checkElements, 100);
                }
            };
            
            checkElements();
        });
    }
    
    checkForRestoredData() {
        if (!this.initialized) return;
        
        setTimeout(() => {
            const savedData = sessionStorage.getItem('chatData');
            if (savedData) {
                restoreChatDataAfterBooking();
            } else {
                this.showWelcomeScreen();
            }
        }, 100);
    }
    
    bindEvents() {
        if (!this.initialized || !this.sendBtn || !this.messageInput) {
            console.warn('Chatbot not fully initialized, skipping event binding');
            return;
        }
        
        // Remove existing listeners to prevent duplicates
        if (this.sendBtn._boundClickHandler) {
            this.sendBtn.removeEventListener('click', this.sendBtn._boundClickHandler);
        }
        if (this.messageInput._boundKeyHandler) {
            this.messageInput.removeEventListener('keypress', this.messageInput._boundKeyHandler);
        }
        
        // Bind new listeners
        this.sendBtn._boundClickHandler = () => this.sendMessage();
        this.sendBtn.addEventListener('click', this.sendBtn._boundClickHandler);
        
        this.messageInput._boundKeyHandler = (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        };
        this.messageInput.addEventListener('keypress', this.messageInput._boundKeyHandler);
        
        // Input validation
        this.messageInput.addEventListener('input', () => this.validateInput());
        
        // New chat button
        if (this.newChatBtn) {
            this.newChatBtn.addEventListener('click', () => this.startNewChat());
        }
        
        // Auto-resize textarea
        this.messageInput.addEventListener('input', () => this.autoResizeTextarea());
        
        // Global click handler
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.suggestion-btn')) {
                this.removeSuggestions();
            }
            
            if (!e.target.closest('.chat-title-input') && !e.target.closest('.title-edit-btn')) {
                this.finishTitleEdit();
            }
        });

        // Save data before page unload
        window.addEventListener('beforeunload', () => {
            this.saveCurrentChatToHistory();
        });

        // Auto-save every 30 seconds
        if (!this.autoSaveInterval) {
            this.autoSaveInterval = setInterval(() => {
                if (this.conversationHistory.length > 0) {
                    this.saveCurrentChatData();
                }
            }, 30000);
        }

        this.addBulkDeleteControls();
    }
    
    addBulkDeleteControls() {
        const sidebar = document.querySelector('.chat-sidebar');
        if (!sidebar || sidebar.querySelector('.bulk-delete-controls')) return;

        const bulkControls = document.createElement('div');
        bulkControls.className = 'bulk-delete-controls';
        bulkControls.innerHTML = `
            <button class="bulk-delete-btn" onclick="window.travelChatbot.showBulkDeleteDialog()" title="Delete multiple chats">
                <i class="fas fa-trash-alt"></i>
                Clear History
            </button>
        `;
        
        const newChatBtn = sidebar.querySelector('#newChatBtn');
        if (newChatBtn && newChatBtn.parentNode) {
            newChatBtn.parentNode.insertBefore(bulkControls, newChatBtn.nextSibling);
        }
    }

    showBulkDeleteDialog() {
        if (this.chatHistoryList.length === 0) {
            alert('No chat history to delete.');
            return;
        }

        const options = [
            'Delete all conversations',
            'Delete conversations older than 7 days',
            'Delete conversations older than 30 days',
            'Cancel'
        ];

        const choice = prompt(`Choose an option:\n${options.map((opt, i) => `${i + 1}. ${opt}`).join('\n')}\n\nEnter number (1-4):`);
        
        if (!choice || choice === '4') return;

        const now = new Date().getTime();
        let toDelete = [];

        switch(choice) {
            case '1':
                if (confirm('Are you sure you want to delete ALL conversations? This cannot be undone.')) {
                    toDelete = [...this.chatHistoryList];
                }
                break;
            case '2':
                const weekAgo = now - (7 * 24 * 60 * 60 * 1000);
                toDelete = this.chatHistoryList.filter(chat => chat.timestamp < weekAgo);
                break;
            case '3':
                const monthAgo = now - (30 * 24 * 60 * 60 * 1000);
                toDelete = this.chatHistoryList.filter(chat => chat.timestamp < monthAgo);
                break;
        }

        if (toDelete.length > 0) {
            if (confirm(`Delete ${toDelete.length} conversation(s)?`)) {
                this.bulkDeleteChats(toDelete.map(chat => chat.id));
            }
        } else {
            alert('No conversations match the selected criteria.');
        }
    }

    bulkDeleteChats(chatIds) {
        this.chatHistoryList = this.chatHistoryList.filter(chat => !chatIds.includes(chat.id));
        localStorage.setItem('chatHistory', JSON.stringify(this.chatHistoryList));
        
        if (chatIds.includes(this.currentChatId)) {
            this.startNewChat();
        } else {
            this.updateChatHistorySidebar();
        }
    }
    
    saveCurrentChatData() {
        if (this.conversationHistory.length > 0) {
            const chatData = {
                conversationHistory: this.conversationHistory,
                messagesHTML: this.messages ? this.messages.innerHTML : '',
                currentChatId: this.currentChatId,
                timestamp: new Date().getTime()
            };
            
            try {
                sessionStorage.setItem('chatData', JSON.stringify(chatData));
            } catch (error) {
                console.error('Error saving chat data:', error);
            }
        }
    }

    saveCurrentChatToHistory() {
        if (this.conversationHistory.length > 0) {
            const chatTitle = this.generateSmartChatTitle();
            const chatSession = {
                id: this.currentChatId || Date.now(),
                title: chatTitle,
                conversationHistory: [...this.conversationHistory],
                messagesHTML: this.messages ? this.messages.innerHTML : '',
                timestamp: new Date().getTime(),
                lastMessage: this.conversationHistory[this.conversationHistory.length - 1]?.message || ''
            };
            
            const existingChats = JSON.parse(localStorage.getItem('chatHistory') || '[]');
            const existingIndex = existingChats.findIndex(chat => chat.id === chatSession.id);
            
            if (existingIndex >= 0) {
                existingChats[existingIndex] = chatSession;
            } else {
                existingChats.unshift(chatSession);
            }
            
            if (existingChats.length > 50) {
                existingChats.splice(50);
            }
            
            localStorage.setItem('chatHistory', JSON.stringify(existingChats));
            this.chatHistoryList = existingChats;
            this.updateChatHistorySidebar();
        }
    }

    generateSmartChatTitle() {
        const firstUserMessage = this.conversationHistory.find(msg => msg.role === 'user')?.message || 'New Chat';
        
        const cleanMessage = firstUserMessage
            .toLowerCase()
            .replace(/[?!.,]/g, '')
            .replace(/\b(?:can you|could you|please|help me|i want to|i need to|show me|find me|tell me about|what is|what are|how to|where is)\b/g, '')
            .trim();

        const words = cleanMessage.split(/\s+/).filter(word => word.length > 2);
        let selectedWords = words.slice(0, 4);
        
        if (selectedWords.length > 0) {
            const shortTitle = selectedWords.map(word => this.capitalizeFirstLetter(word)).join(' ');
            
            if (firstUserMessage.length > 25 && words.length > 4) {
                return shortTitle + '...';
            }
            return shortTitle;
        }
        
        const originalWords = firstUserMessage.split(/\s+/).slice(0, 3);
        const fallbackTitle = originalWords.map(word => this.capitalizeFirstLetter(word)).join(' ');
        
        if (firstUserMessage.split(/\s+/).length > 3) {
            return fallbackTitle + '...';
        }
        
        return fallbackTitle || 'New Chat';
    }

    capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    updateChatHistorySidebar() {
        if (!this.chatList) return;
        
        this.chatList.innerHTML = '';
        
        if (!this.chatHistoryList || this.chatHistoryList.length === 0) {
            const emptyState = document.createElement('div');
            emptyState.className = 'empty-chat-state';
            emptyState.innerHTML = `
                <div style="text-align: center; padding: 20px; color: #6c757d;">
                    <i class="fas fa-comments" style="font-size: 24px; margin-bottom: 8px;"></i>
                    <p>No conversations yet</p>
                    <small>Start chatting to see your history here</small>
                </div>
            `;
            this.chatList.appendChild(emptyState);
            return;
        }

        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);
        const lastWeek = new Date(today);
        lastWeek.setDate(lastWeek.getDate() - 7);

        const groups = {
            today: [],
            yesterday: [],
            thisWeek: [],
            older: []
        };

        this.chatHistoryList.forEach(chat => {
            const chatDate = new Date(chat.timestamp);
            if (chatDate.toDateString() === today.toDateString()) {
                groups.today.push(chat);
            } else if (chatDate.toDateString() === yesterday.toDateString()) {
                groups.yesterday.push(chat);
            } else if (chatDate > lastWeek) {
                groups.thisWeek.push(chat);
            } else {
                groups.older.push(chat);
            }
        });

        this.renderChatGroup('Today', groups.today);
        this.renderChatGroup('Yesterday', groups.yesterday);
        this.renderChatGroup('This Week', groups.thisWeek);
        this.renderChatGroup('Older', groups.older);
    }

    renderChatGroup(groupTitle, chats) {
        if (chats.length === 0) return;

        const groupElement = document.createElement('div');
        groupElement.className = 'chat-group';
        
        const groupHeader = document.createElement('div');
        groupHeader.className = 'chat-group-header';
        groupHeader.textContent = groupTitle;
        groupElement.appendChild(groupHeader);

        chats.forEach(chat => {
            const chatItem = document.createElement('div');
            chatItem.className = 'chat-item';
            chatItem.setAttribute('data-chat-id', chat.id);
            if (chat.id === this.currentChatId) {
                chatItem.classList.add('active');
            }
            
            chatItem.innerHTML = `
                <div class="chat-item-content" onclick="window.travelChatbot.loadChat('${chat.id}')">
                    <div class="chat-title" id="title-${chat.id}">${this.escapeHtml(chat.title)}</div>
                    <input type="text" class="chat-title-input" id="input-${chat.id}" 
                           value="${this.escapeHtml(chat.title)}" 
                           style="display: none;"
                           onblur="window.travelChatbot.saveTitleEdit('${chat.id}')"
                           onkeypress="window.travelChatbot.handleTitleKeyPress(event, '${chat.id}')">
                </div>
                <div class="chat-actions">
                    <button class="chat-action-btn title-edit-btn" onclick="window.travelChatbot.editChatTitle('${chat.id}')" title="Edit title">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="chat-action-btn chat-delete-btn" onclick="window.travelChatbot.deleteChat('${chat.id}')" title="Delete chat">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            
            groupElement.appendChild(chatItem);
        });

        this.chatList.appendChild(groupElement);
    }

    editChatTitle(chatId) {
        this.finishTitleEdit();
        
        this.editingChatId = chatId;
        const titleElement = document.getElementById(`title-${chatId}`);
        const inputElement = document.getElementById(`input-${chatId}`);
        
        if (titleElement && inputElement) {
            titleElement.style.display = 'none';
            inputElement.style.display = 'block';
            inputElement.focus();
            inputElement.select();
        }
    }

    handleTitleKeyPress(event, chatId) {
        if (event.key === 'Enter') {
            this.saveTitleEdit(chatId);
        } else if (event.key === 'Escape') {
            this.cancelTitleEdit(chatId);
        }
    }

    saveTitleEdit(chatId) {
        const inputElement = document.getElementById(`input-${chatId}`);
        const newTitle = inputElement ? inputElement.value.trim() : '';
        
        if (newTitle && newTitle !== '') {
            const chatIndex = this.chatHistoryList.findIndex(chat => chat.id === chatId);
            if (chatIndex >= 0) {
                this.chatHistoryList[chatIndex].title = newTitle;
                localStorage.setItem('chatHistory', JSON.stringify(this.chatHistoryList));
            }
        }
        
        this.finishTitleEdit();
    }

    cancelTitleEdit(chatId) {
        const inputElement = document.getElementById(`input-${chatId}`);
        const titleElement = document.getElementById(`title-${chatId}`);
        
        if (inputElement && titleElement) {
            const originalChat = this.chatHistoryList.find(chat => chat.id === chatId);
            if (originalChat) {
                inputElement.value = originalChat.title;
            }
        }
        
        this.finishTitleEdit();
    }

    finishTitleEdit() {
        if (this.editingChatId) {
            const titleElement = document.getElementById(`title-${this.editingChatId}`);
            const inputElement = document.getElementById(`input-${this.editingChatId}`);
            
            if (titleElement && inputElement) {
                titleElement.textContent = inputElement.value;
                titleElement.style.display = 'block';
                inputElement.style.display = 'none';
            }
            
            this.editingChatId = null;
        }
    }

    loadChat(chatId) {
        const chat = this.chatHistoryList.find(c => c.id === chatId);
        if (!chat) return;

        if (this.conversationHistory.length > 0) {
            this.saveCurrentChatToHistory();
        }

        this.currentChatId = chat.id;
        this.conversationHistory = [...chat.conversationHistory];
        if (this.messages) {
            this.messages.innerHTML = chat.messagesHTML;
        }
        this.hideWelcomeScreen();
        this.scrollToBottom();
        
        this.updateChatHistorySidebar();
        this.removeSuggestions();
    }

    deleteChat(chatId) {
        const chat = this.chatHistoryList.find(c => c.id === chatId);
        const chatTitle = chat ? chat.title : 'this conversation';
        
        if (confirm(`Are you sure you want to delete "${chatTitle}"?`)) {
            this.chatHistoryList = this.chatHistoryList.filter(chat => chat.id !== chatId);
            localStorage.setItem('chatHistory', JSON.stringify(this.chatHistoryList));
            
            if (chatId === this.currentChatId) {
                this.startNewChat();
            } else {
                this.updateChatHistorySidebar();
            }
        }
    }

    validateInput() {
        if (!this.messageInput || !this.sendBtn) return;
        
        const message = this.messageInput.value.trim();
        this.sendBtn.disabled = message.length === 0 || this.isTyping;
    }
    
    autoResizeTextarea() {
        if (!this.messageInput) return;
        
        this.messageInput.style.height = 'auto';
        this.messageInput.style.height = Math.min(this.messageInput.scrollHeight, 120) + 'px';
    }
    
    showWelcomeScreen() {
        if (this.welcomeScreen && this.messagesContainer) {
            this.welcomeScreen.style.display = 'block';
            this.messagesContainer.style.display = 'none';
        }
    }
    
    hideWelcomeScreen() {
        if (this.welcomeScreen && this.messagesContainer) {
            this.welcomeScreen.style.display = 'none';
            this.messagesContainer.style.display = 'block';
        }
    }
    
    startNewChat() {
        if (this.conversationHistory.length > 0) {
            this.saveCurrentChatToHistory();
        }
        
        this.currentChatId = null;
        this.conversationHistory = [];
        if (this.messages) {
            this.messages.innerHTML = '';
        }
        this.showWelcomeScreen();
        if (this.messageInput) {
            this.messageInput.focus();
        }
        
        sessionStorage.removeItem('chatData');
        this.finishTitleEdit();
        this.updateChatHistorySidebar();
    }
    
    async sendMessage(messageText = null) {
        if (!this.initialized || !this.messageInput) {
            console.warn('Chatbot not initialized, cannot send message');
            return;
        }
        
        const message = messageText || this.messageInput.value.trim();
        if (!message || this.isTyping) return;
        
        if (!this.currentChatId) {
            this.currentChatId = Date.now().toString();
        }
        
        if (this.welcomeScreen && this.welcomeScreen.style.display !== 'none') {
            this.hideWelcomeScreen();
        }
        
        this.addMessage(message, 'user');
        this.conversationHistory.push({
            role: 'user',
            message: message,
            timestamp: new Date().toISOString()
        });
        
        if (!messageText && this.messageInput) {
            this.messageInput.value = '';
            this.validateInput();
            this.autoResizeTextarea();
        }
        
        this.removeSuggestions();
        this.showTypingIndicator();
        this.saveCurrentChatData();
        
        try {
            const response = await this.callChatbotAPI(message);
            this.hideTypingIndicator();
            
            if (response && response.success && response.response) {
                this.addMessage(response.response.text, 'assistant', response.response);
                
                this.conversationHistory.push({
                    role: 'assistant',
                    message: response.response.text,
                    data: response.response.data,
                    type: response.response.type,
                    timestamp: new Date().toISOString()
                });
                
                if (response.response.suggestions && response.response.suggestions.length > 0) {
                    this.addSuggestions(response.response.suggestions);
                }
                
                this.saveCurrentChatData();
                
                if (this.conversationHistory.length >= 2) {
                    this.saveCurrentChatToHistory();
                }
                
            } else {
                const errorText = response && response.error ? 
                    `I apologize for the error: ${response.error}` : 
                    'I\'m having trouble processing your request right now. Please try again in a moment.';
                this.addMessage(errorText, 'assistant');
            }
        } catch (error) {
            this.hideTypingIndicator();
            console.error('Chat error:', error);
            
            let errorMessage = 'I\'m experiencing connectivity issues.';
            
            if (error.message.includes('404') || error.message.includes('Not Found')) {
                errorMessage = 'Cannot find the chatbot service. Please check if the backend file exists.';
            } else if (error.message.includes('500') || error.message.includes('Server error')) {
                errorMessage = 'Server error occurred. Please check the server logs.';
            } else if (error.message.includes('Network error') || error.name === 'TypeError') {
                errorMessage = 'Network connection issue. Please check your internet connection and try again.';
            } else if (error.message.includes('timeout')) {
                errorMessage = 'Request timed out. The server may be busy, please try again.';
            } else if (error.message.includes('JSON')) {
                errorMessage = 'Server returned invalid response format. Please check the PHP file for syntax errors.';
            }
            
            this.addMessage(errorMessage, 'assistant');
        }
        
        this.scrollToBottom();
    }
    
    addMessage(text, sender, data = null) {
        if (!this.messages) return;
        
        const messageElement = document.createElement('div');
        messageElement.className = `message ${sender}`;
        
        if (sender === 'user') {
            messageElement.innerHTML = `
                <div class="message-content">
                    <div class="message-header">
                        <div class="message-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="message-text">${this.escapeHtml(text)}</div>
                    </div>
                </div>
            `;
        } else {
            const formattedText = this.formatBotMessage(text);
            messageElement.innerHTML = `
                <div class="message-content">
                    <div class="message-header">
                        <div class="message-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="message-text">${formattedText}</div>
                    </div>
                </div>
            `;
            
            if (data && data.data) {
                this.addDataCards(messageElement, data);
            }
        }
        
        this.messages.appendChild(messageElement);
    }

    addDataCards(messageElement, response) {
        const cardContainer = document.createElement('div');
        
        const hasTours = this.hasToursInResponse(response);
        const hasHotels = this.hasHotelsInResponse(response);
        const tourCount = this.getTourCount(response);
        const hotelCount = this.getHotelCount(response);
        
        // **NEW**: Handle multi-city tour/hotel searches
        if (response.layout_type === 'multi_city_tours') {
            this.createMultiCityLayout(cardContainer, response, 'tour');
        } else if (response.layout_type === 'multi_city_hotels') {
            this.createMultiCityLayout(cardContainer, response, 'hotel');
        }
        // Handle existing layouts
        else if (hasTours && hasHotels && (response.layout_type === 'mixed_content' || response.match_level === 'mixed_search')) {
            cardContainer.className = 'data-cards mixed-content';
            this.createMixedLayout(cardContainer, response, tourCount, hotelCount);
        } else if (hasTours) {
            cardContainer.className = 'data-cards single-section';
            this.createSingleToursSection(cardContainer, response, tourCount);
        } else if (hasHotels) {
            cardContainer.className = 'data-cards single-section';
            this.createSingleHotelsSection(cardContainer, response, hotelCount);
        } else {
            cardContainer.className = 'data-cards';
            this.createDefaultLayout(cardContainer, response);
        }
        
        if (cardContainer.children.length > 0) {
            messageElement.querySelector('.message-content').appendChild(cardContainer);
        }
    }

    // Create single tours section with header (like mixed layout style)
    createSingleToursSection(container, response, tourCount) {
        const maxInitialItems = 6; // Show 6 tours initially
        
        const toursSection = document.createElement('div');
        toursSection.className = 'single-section tours-section';
        
        const toursHeader = document.createElement('h4');
        toursHeader.innerHTML = `<i class="fas fa-map-signs"></i> Tour Packages (${tourCount})`;
        toursSection.appendChild(toursHeader);
        
        const tours = this.extractToursFromResponse(response);
        this.renderItemsWithPagination(toursSection, tours, 'tour', maxInitialItems, true);
        
        container.appendChild(toursSection);
    }

    // Create single hotels section with header (like mixed layout style)
    createSingleHotelsSection(container, response, hotelCount) {
        const maxInitialItems = 6; // Show 6 hotels initially
        
        const hotelsSection = document.createElement('div');
        hotelsSection.className = 'single-section hotels-section';
        
        const hotelsHeader = document.createElement('h4');
        hotelsHeader.innerHTML = `<i class="fas fa-bed"></i> Accommodations (${hotelCount})`;
        hotelsSection.appendChild(hotelsHeader);
        
        const hotels = this.extractHotelsFromResponse(response);
        this.renderItemsWithPagination(hotelsSection, hotels, 'hotel', maxInitialItems, true);
        
        container.appendChild(hotelsSection);
    }

    // Helper methods to check response content
    hasToursInResponse(response) {
        if (response.type === 'tour_search') return true;
        if (response.type === 'destination_info' && response.data?.tours?.length > 0) return true;
        if (Array.isArray(response.data) && response.data.some(item => item.tour_name || item.tourid)) return true;
        return false;
    }

    hasHotelsInResponse(response) {
        if (response.type === 'hotel_search') return true;
        if (response.type === 'destination_info' && response.data?.hotels?.length > 0) return true;
        if (Array.isArray(response.data) && response.data.some(item => item.hotel || item.hotelid)) return true;
        return false;
    }

    getTourCount(response) {
        if (response.type === 'tour_search' && Array.isArray(response.data)) {
            return response.data.length;
        }
        if (response.type === 'destination_info' && response.data?.tours) {
            return response.data.tours.length;
        }
        if (Array.isArray(response.data)) {
            return response.data.filter(item => item.tour_name || item.tourid).length;
        }
        return 0;
    }

    getHotelCount(response) {
        if (response.type === 'hotel_search' && Array.isArray(response.data)) {
            return response.data.length;
        }
        if (response.type === 'destination_info' && response.data?.hotels) {
            return response.data.hotels.length;
        }
        if (Array.isArray(response.data)) {
            return response.data.filter(item => item.hotel || item.hotelid).length;
        }
        return 0;
    }

    // Create multi-city layout (e.g., tours in city 1 vs. tours in city 2)
    createMultiCityLayout(container, response, type) {
        const items = type === 'tour' ? this.extractToursFromResponse(response) : this.extractHotelsFromResponse(response);
        if (items.length === 0) return;

        // Separate items by city_group instead of column_position
        const cities = [...new Set(items.map(item => item.city_group))];
        const city1Items = items.filter(item => item.city_group === cities[0]);
        const city2Items = items.filter(item => item.city_group === cities[1]);

        // Add CSS class for multi-city styling
        container.className = 'multi-city-layout';

        // Create left column (City 1)
        const leftColumnDiv = document.createElement('div');
        leftColumnDiv.className = 'multi-city-column left-column';
        
        if (cities[0]) {
            const leftHeader = document.createElement('h4');
            leftHeader.innerHTML = `<i class="fas fa-map-marker-alt"></i> ${this.escapeHtml(cities[0])} (${city1Items.length})`;
            leftColumnDiv.appendChild(leftHeader);
        }

        city1Items.forEach(item => {
            const card = type === 'tour' ? this.createTourCard(item) : this.createHotelCard(item);
            leftColumnDiv.appendChild(card);
        });
        container.appendChild(leftColumnDiv);

        // Create right column (City 2)  
        const rightColumnDiv = document.createElement('div');
        rightColumnDiv.className = 'multi-city-column right-column';

        if (cities[1]) {
            const rightHeader = document.createElement('h4');
            rightHeader.innerHTML = `<i class="fas fa-map-marker-alt"></i> ${this.escapeHtml(cities[1])} (${city2Items.length})`;
            rightColumnDiv.appendChild(rightHeader);
        }

        city2Items.forEach(item => {
            const card = type === 'tour' ? this.createTourCard(item) : this.createHotelCard(item);
            rightColumnDiv.appendChild(card);
        });
        container.appendChild(rightColumnDiv);
    }

    // Create mixed layout (tours + hotels side by side)
    createMixedLayout(container, response, tourCount, hotelCount) {
        const maxInitialItems = 4; // Show max 4 items initially per section
        
        // Tours section
        if (this.hasToursInResponse(response)) {
            const toursSection = document.createElement('div');
            toursSection.className = 'mixed-section tours-section';
            
            const toursHeader = document.createElement('h4');
            toursHeader.innerHTML = `<i class="fas fa-map-signs"></i> Tour Packages (${tourCount})`;
            toursSection.appendChild(toursHeader);
            
            const tours = this.extractToursFromResponse(response);
            this.renderItemsWithPagination(toursSection, tours, 'tour', maxInitialItems);
            
            container.appendChild(toursSection);
        }
        
        // Hotels section
        if (this.hasHotelsInResponse(response)) {
            const hotelsSection = document.createElement('div');
            hotelsSection.className = 'mixed-section hotels-section';
            
            const hotelsHeader = document.createElement('h4');
            hotelsHeader.innerHTML = `<i class="fas fa-bed"></i> Accommodations (${hotelCount})`;
            hotelsSection.appendChild(hotelsHeader);
            
            const hotels = this.extractHotelsFromResponse(response);
            this.renderItemsWithPagination(hotelsSection, hotels, 'hotel', maxInitialItems);
            
            container.appendChild(hotelsSection);
        }
    }

    // Create default single column layout
    createDefaultLayout(container, response) {
        const maxInitialItems = 3; // Show 3 items initially
        
        if (response.type === 'tour_search' && Array.isArray(response.data)) {
            this.renderItemsWithPagination(container, response.data, 'tour', maxInitialItems);
        }
        else if (response.type === 'hotel_search' && Array.isArray(response.data)) {
            this.renderItemsWithPagination(container, response.data, 'hotel', maxInitialItems);
        }
        else if (response.type === 'destination_info' && response.data) {
            if (response.data.tours && response.data.tours.length > 0) {
                response.data.tours.forEach(tour => {
                    const card = this.createTourCard(tour);
                    container.appendChild(card);
                });
            }
            if (response.data.hotels && response.data.hotels.length > 0) {
                response.data.hotels.forEach(hotel => {
                    const card = this.createHotelCard(hotel);
                    container.appendChild(card);
                });
            }
        }
        else if (Array.isArray(response.data)) {
            response.data.forEach(item => {
                if (item.tour_name || item.tourid) {
                    const card = this.createTourCard(item);
                    container.appendChild(card);
                } else if (item.hotel || item.hotelid) {
                    const card = this.createHotelCard(item);
                    container.appendChild(card);
                }
            });
        }
    }

    // Extract tours from various response formats
    extractToursFromResponse(response) {
        if (response.type === 'tour_search' && Array.isArray(response.data)) {
            return response.data;
        }
        if (response.type === 'destination_info' && response.data?.tours) {
            return response.data.tours;
        }
        if (Array.isArray(response.data)) {
            return response.data.filter(item => item.tour_name || item.tourid);
        }
        return [];
    }

    // Extract hotels from various response formats
    extractHotelsFromResponse(response) {
        if (response.type === 'hotel_search' && Array.isArray(response.data)) {
            return response.data;
        }
        if (response.type === 'destination_info' && response.data?.hotels) {
            return response.data.hotels;
        }
        if (Array.isArray(response.data)) {
            return response.data.filter(item => item.hotel || item.hotelid);
        }
        return [];
    }

    // Render items with pagination/show more functionality
    renderItemsWithPagination(container, items, type, maxInitial, isGrid = false) {
        if (!items || items.length === 0) return;
        
        const containerId = `container_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        
        // Create wrapper for cards
        const cardsWrapper = document.createElement('div');
        cardsWrapper.className = isGrid ? 'cards-grid-wrapper' : 'cards-list-wrapper';
        cardsWrapper.id = containerId;
        
        // Render initial items
        items.slice(0, maxInitial).forEach((item, index) => {
            const card = type === 'tour' ? this.createTourCard(item) : this.createHotelCard(item);
            card.classList.add('visible');
            if (type === 'tour') {
                card.querySelector('.card-image').insertAdjacentHTML('afterbegin', 
                    '<span class="card-category-badge tour">Tour</span>');
            } else {
                card.querySelector('.card-image').insertAdjacentHTML('afterbegin', 
                    '<span class="card-category-badge hotel">Hotel</span>');
            }
            cardsWrapper.appendChild(card);
        });
        
        // Render hidden items
        items.slice(maxInitial).forEach((item, index) => {
            const card = type === 'tour' ? this.createTourCard(item) : this.createHotelCard(item);
            card.classList.add('hidden');
            if (type === 'tour') {
                card.querySelector('.card-image').insertAdjacentHTML('afterbegin', 
                    '<span class="card-category-badge tour">Tour</span>');
            } else {
                card.querySelector('.card-image').insertAdjacentHTML('afterbegin', 
                    '<span class="card-category-badge hotel">Hotel</span>');
            }
            cardsWrapper.appendChild(card);
        });
        
        container.appendChild(cardsWrapper);
        
        // Add show more/less buttons if needed
        if (items.length > maxInitial) {
            const toggleContainer = document.createElement('div');
            toggleContainer.className = 'card-show-toggle';
            
            const showMoreBtn = document.createElement('button');
            showMoreBtn.className = 'show-more-btn';
            showMoreBtn.innerHTML = `<i class="fas fa-chevron-down"></i> Show ${items.length - maxInitial} More ${type === 'tour' ? 'Tours' : 'Hotels'}`;
            
            const showLessBtn = document.createElement('button');
            showLessBtn.className = 'show-less-btn hidden';
            showLessBtn.innerHTML = `<i class="fas fa-chevron-up"></i> Show Less`;
            
            // Show more functionality
            showMoreBtn.addEventListener('click', () => {
                const hiddenCards = cardsWrapper.querySelectorAll('.data-card.hidden');
                hiddenCards.forEach(card => {
                    card.classList.remove('hidden');
                    card.classList.add('visible');
                });
                showMoreBtn.classList.add('hidden');
                showLessBtn.classList.remove('hidden');
                
                // Smooth scroll to show new content
                setTimeout(() => {
                    const lastCard = cardsWrapper.lastElementChild;
                    if (lastCard) {
                        lastCard.scrollIntoView({ behavior: 'smooth', block: 'end' });
                    }
                }, 100);
            });
            
            // Show less functionality
            showLessBtn.addEventListener('click', () => {
                const allCards = cardsWrapper.querySelectorAll('.data-card');
                allCards.forEach((card, index) => {
                    if (index >= maxInitial) {
                        card.classList.add('hidden');
                        card.classList.remove('visible');
                    }
                });
                showLessBtn.classList.add('hidden');
                showMoreBtn.classList.remove('hidden');
                
                // Scroll back to top of cards
                setTimeout(() => {
                    cardsWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);
            });
            
            toggleContainer.appendChild(showMoreBtn);
            toggleContainer.appendChild(showLessBtn);
            container.appendChild(toggleContainer);
        }
    }

        
    generateTourImagePath(cityId, tourId) {
        const cityIdToString = {
            10: 'taybac',
            11: 'hcm', 
            12: 'nhatrang',
            13: 'hue',
            14: 'phuyen', 
            15: 'dalat',
            16: 'phuquoc',
            17: 'hoian',
            18: 'hagiang',
            19: 'danang',
            20: 'cantho',
            21: 'hoinoi'
        };
        
        const cityString = cityIdToString[cityId] || 'hcm';
        
        const imageMap = {
        'taybac' :[1, 2, 3, 4, 93, 94, 95, 96],
        'hcm' :[5, 6, 7, 8, 89, 90, 91, 92],
        'nhatrang' :[85, 86, 87, 88, 9, 10, 11, 12],
        'hue' :[13, 14, 15, 16, 81, 82, 83, 84],
        'phuyen' :[77, 78, 79, 80, 17, 18, 19, 20,],
        'dalat' :[21, 22, 23, 24, 73, 74, 75, 76],        
        'phuquoc' :[25, 26, 27, 28, 69, 70, 71, 72],
        'hoian' :[29, 30, 31, 32, 65, 66, 67, 68],   
        'hagiang' :[61, 62, 63, 64, 33, 34, 35, 36],
        'danang' :[37, 38, 39, 40, 57, 58, 59, 60],
        'cantho' :[53, 54, 55, 56, 41, 42, 43, 44,],
        'hanoi'  :[45, 46, 47, 48, 49, 50, 51, 52]
        };
            
        const imageIds = imageMap[cityString] || [1, 2, 3, 4];
        const imageId = imageIds[(tourId - 1) % imageIds.length];
            
        return `../tourphotoID/${imageId}.jpg`;
    }

    generateHotelImagePath(hotelId) {
        return `../hotelphotoID/${hotelId}.jpg`;
    }

    createTourCard(tour) {
        const card = document.createElement('div');
        card.className = 'data-card tour-card';
        
        const price = parseFloat(tour.price_per_person || 0);
        const duration = parseInt(tour.duration_days || 0);
        
        let tourName = 'Tour Package';
        if (tour.tour_name && tour.tour_name.trim() !== '') {
            tourName = tour.tour_name.trim();
        } else if (tour.name && tour.name.trim() !== '') {
            tourName = tour.name.trim();
        } else if (tour.title && tour.title.trim() !== '') {
            tourName = tour.title.trim();
        }
        
        const cityName = tour.city || tour.city_name || 'Vietnam';
        const season = tour.season || 'All year';
        const tourId = tour.tourid || tour.id || 0;
        const cityId = tour.cityid || this.getCityIdFromName(cityName) || 11;
        
        const imagePath = this.generateTourImagePath(cityId, tourId);
        
        card.innerHTML = `
            <div class="card-content">
                <div class="card-image">
                    <img src="${imagePath}" alt="${this.escapeHtml(tourName)}" onerror="this.src='../images/default-tour.jpg'">
                    <span class="card-badge discount">-15%</span>
                </div>
                <div class="card-info">
                    <div class="card-title" title="${this.escapeHtml(tourName)}">
                        ${this.escapeHtml(tourName)}
                    </div>
                    <div class="card-details">
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span class="detail-label">Location:</span>
                            <span class="detail-value">${this.escapeHtml(cityName)}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span class="detail-label">Duration:</span>
                            <span class="detail-value">${duration} ${duration === 1 ? 'day' : 'days'}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <span class="detail-label">Price:</span>
                            <span class="detail-value">${this.formatPrice(price)} VND</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="card-btn card-btn-primary" onclick="bookTour(${tourId}, ${cityId})" title="Book this tour">
                            <i class="fas fa-calendar-plus"></i>
                            Book
                        </button>
                        <button class="card-btn card-btn-secondary" onclick="viewTourDetails(${tourId}, ${cityId})" title="View tour details">
                            <i class="fas fa-info-circle"></i>
                            Details
                        </button>
                    </div>
                </div>
            </div>
        `;
        return card;
    }

    createHotelCard(hotel) {
        const card = document.createElement('div');
        card.className = 'data-card hotel-card';
        
        const cost = parseFloat(hotel.cost || 0);
        const rating = parseFloat(hotel.ratings || 4);
        
        let hotelName = 'Hotel';
        if (hotel.hotel && hotel.hotel.trim() !== '') {
            hotelName = hotel.hotel.trim();
        } else if (hotel.name && hotel.name.trim() !== '') {
            hotelName = hotel.name.trim();
        } else if (hotel.hotel_name && hotel.hotel_name.trim() !== '') {
            hotelName = hotel.hotel_name.trim();
        } else if (hotel.title && hotel.title.trim() !== '') {
            hotelName = hotel.title.trim();
        }
        
        const cityName = hotel.city || hotel.city_name || 'Vietnam';
        const hotelId = hotel.hotelid || hotel.id || 0;
        
        const imagePath = this.generateHotelImagePath(hotelId);
        
        card.innerHTML = `
            <div class="card-content">
                <div class="card-image">
                    <img src="${imagePath}" alt="${this.escapeHtml(hotelName)}" onerror="this.src='../images/default-hotel.jpg'">
                    <span class="card-badge rating">${rating.toFixed(1)}</span>
                </div>
                <div class="card-info">
                    <div class="card-title" title="${this.escapeHtml(hotelName)}">
                        ${this.escapeHtml(hotelName)}
                    </div>
                    <div class="card-details">
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span class="detail-label">Location:</span>
                            <span class="detail-value">${this.escapeHtml(cityName)}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-star"></i>
                            <span class="detail-label">Rating:</span>
                            <span class="detail-value">${rating.toFixed(1)}/5.0</span>
                        </div>
                        ${cost > 0 ? `
                        <div class="detail-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <span class="detail-label">Price:</span>
                            <span class="detail-value">${this.formatPrice(cost)} VND/night</span>
                        </div>
                        ` : ''}
                    </div>
                    <div class="card-actions">
                        <button class="card-btn card-btn-primary" onclick="bookHotel(${hotelId})" title="Book this hotel">
                            <i class="fas fa-bed"></i>
                            Book
                        </button>
                        <button class="card-btn card-btn-secondary" onclick="viewHotelDetails(${hotelId})" title="View hotel details">
                            <i class="fas fa-info-circle"></i>
                            Details
                        </button>
                    </div>
                </div>
            </div>
        `;
        return card;
    }
        
    getCityIdFromName(cityName) {
        const cityMapping = {
            'Ho Chi Minh': 11, 'Ho Chi Minh City': 11, 'Saigon': 11,
            'Nha Trang': 12,
            'Hue': 13,
            'Phu Yen': 14,
            'Da Lat': 15, 'Dalat': 15,
            'Phu Quoc': 16,
            'Hoi An': 17,
            'Ha Giang': 18,
            'Tay Bac': 10
        };
        
        return cityMapping[cityName] || cityMapping[cityName.toLowerCase()] || 11;
    }
    
    addSuggestions(suggestions) {
        if (!this.messages) return;
        
        this.removeSuggestions();
        
        const suggestionsContainer = document.createElement('div');
        suggestionsContainer.className = 'suggestions-container';
        suggestionsContainer.innerHTML = `
            <div class="suggestion-title">Suggested questions:</div>
            <div class="suggestion-buttons">
                ${suggestions.map(suggestion => 
                    `<button class="suggestion-btn" onclick="window.travelChatbot.sendMessage('${this.escapeHtml(suggestion)}')">${this.escapeHtml(suggestion)}</button>`
                ).join('')}
            </div>
        `;
        
        this.messages.appendChild(suggestionsContainer);
        this.scrollToBottom();
    }
    
    removeSuggestions() {
        if (!this.messages) return;
        
        const existingSuggestions = this.messages.querySelector('.suggestions-container');
        if (existingSuggestions) {
            existingSuggestions.remove();
        }
    }
    
    showTypingIndicator() {
        if (!this.messages) return;
        
        this.isTyping = true;
        this.validateInput();
        
        const typingElement = document.createElement('div');
        typingElement.className = 'message assistant typing-message';
        typingElement.id = 'typingIndicator';
        typingElement.innerHTML = `
            <div class="message-content">
                <div class="message-header">
                    <div class="message-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="typing-indicator">
                        <div class="typing-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <div class="typing-text">AI is analyzing your request...</div>
                    </div>
                </div>
            </div>
        `;
        
        this.messages.appendChild(typingElement);
        this.scrollToBottom();
    }
    
    hideTypingIndicator() {
        this.isTyping = false;
        this.validateInput();
        
        const typingIndicator = document.getElementById('typingIndicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }
    
    formatBotMessage(text) {
        return text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n\n/g, '<br><br>')
            .replace(/\n/g, '<br>')
            .replace(/(\d+)\.\s/g, '<br><strong>$1.</strong> ')
            .replace(/•\s/g, '<br>• ');
    }
    
    formatPrice(price) {
        if (!price || price === 0) return '0';
        return new Intl.NumberFormat('vi-VN').format(price);
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }
    
    scrollToBottom() {
        if (!this.messagesContainer) return;
        
        setTimeout(() => {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }, 100);
    }
    
    async loadChatHistory() {
        try {
            const localHistory = JSON.parse(localStorage.getItem('chatHistory') || '[]');
            this.chatHistoryList = localHistory;
            this.updateChatHistorySidebar();
            
            // Optionally try to load from API
            const response = await fetch(this.apiEndpoint + '?action=get_history');
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.history) {
                    console.log('API history loaded:', data.history.length, 'conversations');
                }
            }
        } catch (error) {
            console.error('Error loading chat history:', error);
            const localHistory = JSON.parse(localStorage.getItem('chatHistory') || '[]');
            this.chatHistoryList = localHistory;
            this.updateChatHistorySidebar();
        }
    }
    
    async callChatbotAPI(message) {
        try {
            const requestData = { 
                message: message,
                conversation_history: this.conversationHistory.slice(-5)
            };
            
            console.log('Sending request to:', this.apiEndpoint);
            
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 30000);
            
            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(requestData),
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('HTTP Error Response:', errorText);
                
                if (response.status === 404) {
                    throw new Error(`File not found (404). Check if ${this.apiEndpoint} exists.`);
                } else if (response.status === 500) {
                    throw new Error(`Server error (500): ${errorText.substring(0, 200)}`);
                } else {
                    throw new Error(`HTTP error ${response.status}: ${errorText.substring(0, 200)}`);
                }
            }
            
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                const text = await response.text();
                console.error('Non-JSON response:', text.substring(0, 500));
                throw new Error("Server returned non-JSON response. Check PHP file for syntax errors.");
            }
            
            const data = await response.json();
            console.log('Backend response:', data);
            return data;
            
        } catch (error) {
            console.error('API call failed:', error);
            
            if (error.name === 'AbortError') {
                throw new Error('Request timed out. Please try again.');
            } else if (error.name === 'TypeError' && error.message.includes('fetch')) {
                throw new Error('Network error. Please check your internet connection.');
            } else {
                throw error;
            }
        }
    }
    
    // Cleanup method
    destroy() {
        if (this.autoSaveInterval) {
            clearInterval(this.autoSaveInterval);
        }
    }
}

// Enhanced Initialization with error handling
document.addEventListener('DOMContentLoaded', function() {
    try {
        // Ensure previous instance is cleaned up
        if (window.travelChatbot && window.travelChatbot.destroy) {
            window.travelChatbot.destroy();
        }
        
        window.travelChatbot = new TravelChatbot();
        
        setTimeout(() => {
            const messageInput = document.getElementById('messageInput');
            if (messageInput) {
                messageInput.focus();
            }
        }, 500);
        
        // Add welcome suggestions after initialization
        setTimeout(() => {
            const chatbot = window.travelChatbot;
            const welcomeScreen = document.getElementById('welcomeScreen');
            
            if (chatbot && welcomeScreen && welcomeScreen.style.display !== 'none') {
                const welcomeSuggestions = [
                    'Show me popular tours in Vietnam',
                    'Find hotels in Ho Chi Minh City', 
                    'Plan a 3-day trip to Da Lat',
                    'What is the best time to visit Ha Giang?'
                ];
                
                const welcomeContent = document.querySelector('.welcome-content');
                if (welcomeContent && !welcomeContent.querySelector('.welcome-suggestions')) {
                    const suggestionsDiv = document.createElement('div');
                    suggestionsDiv.className = 'welcome-suggestions';
                    suggestionsDiv.innerHTML = `
                        <div class="suggestion-title">Try asking:</div>
                        <div class="suggestion-buttons">
                            ${welcomeSuggestions.map(suggestion => 
                                `<button class="suggestion-btn welcome-suggestion" onclick="window.travelChatbot.sendMessage('${suggestion}')">${suggestion}</button>`
                            ).join('')}
                        </div>
                    `;
                    welcomeContent.appendChild(suggestionsDiv);
                }
            }
        }, 1000);
        
    } catch (error) {
        console.error('Failed to initialize chatbot:', error);
        
        // Show error message to user
        const errorDiv = document.createElement('div');
        errorDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ff4757;
            color: white;
            padding: 15px;
            border-radius: 5px;
            z-index: 9999;
            max-width: 300px;
        `;
        errorDiv.innerHTML = `
            <strong>Chatbot Initialization Error</strong><br>
            Please refresh the page. If the problem persists, check the console for details.
        `;
        document.body.appendChild(errorDiv);
        
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.parentNode.removeChild(errorDiv);
            }
        }, 5000);
    }
});