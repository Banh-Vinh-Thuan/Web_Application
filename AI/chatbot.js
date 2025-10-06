// Constants
const CONSTANTS = {
    SESSION_KEY: 'chatData',
    CHAT_HISTORY_KEY: 'chatHistory',
    API_ENDPOINT: './api.php',
    MAX_HISTORY_ITEMS: 50,
    AUTO_SAVE_INTERVAL: 30000,
    REQUEST_TIMEOUT: 30000,
    MAX_CARDS_PER_COLUMN: 3,
    MIN_PER_CITY: 3
};

const CITY_ID_MAP = {
    10: 'taybac', 11: 'hcm', 12: 'nhatrang', 13: 'hue', 14: 'phuyen',
    15: 'dalat', 16: 'phuquoc', 17: 'hoian', 18: 'hagiang',
    19: 'danang', 20: 'cantho', 21: 'hanoi'
};

// Global Functions
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
    const cityString = CITY_ID_MAP[cityId] || 'hcm';
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

// Data Persistence
function saveChatDataBeforeBooking() {
    const chatbot = window.travelChatbot;
    if (!chatbot) return;

    const chatData = {
        conversationHistory: chatbot.conversationHistory,
        messagesHTML: chatbot.messages.innerHTML,
        currentChatId: chatbot.currentChatId,
        timestamp: Date.now()
    };

    try {
        sessionStorage.setItem(CONSTANTS.SESSION_KEY, JSON.stringify(chatData));
    } catch (error) {
        console.error('Error saving chat data:', error);
    }
}

function restoreChatDataAfterBooking() {
    try {
        const savedData = sessionStorage.getItem(CONSTANTS.SESSION_KEY);
        if (!savedData) return;

        const chatData = JSON.parse(savedData);
        const chatbot = window.travelChatbot;

        if (!chatbot || !chatData.timestamp) return;

        const timeDiff = Date.now() - chatData.timestamp;
        const maxAge = 24 * 60 * 60 * 1000; // 24 hours

        if (timeDiff < maxAge) {
            chatbot.conversationHistory = chatData.conversationHistory || [];
            chatbot.currentChatId = chatData.currentChatId || null;

            if (chatData.messagesHTML) {
                chatbot.messages.innerHTML = chatData.messagesHTML;
                chatbot.hideWelcomeScreen();
                chatbot.scrollToBottom();
            }
        } else {
            sessionStorage.removeItem(CONSTANTS.SESSION_KEY);
        }
    } catch (error) {
        console.error('Error restoring chat data:', error);
    }
}

// Main Chatbot Class
class TravelChatbot {
    constructor() {
        this.apiEndpoint = CONSTANTS.API_ENDPOINT;
        this.currentChatId = null;
        this.isTyping = false;
        this.conversationHistory = [];
        this.chatHistoryList = [];
        this.initialized = false;

        this.init();
    }

    async init() {
        try {
            await this.initializeElements();
            this.bindEvents();
            this.loadChatHistory();
            this.checkForRestoredData();
        } catch (error) {
            console.error('Failed to initialize chatbot:', error);
            this.showError('Chatbot initialization failed. Please refresh the page.');
        }
    }

    async initializeElements() {
        await this.waitForElements();

        this.welcomeScreen = document.getElementById('welcomeScreen');
        this.messagesContainer = document.getElementById('messagesContainer');
        this.messages = document.getElementById('messages');
        this.messageInput = document.getElementById('messageInput');
        this.sendBtn = document.getElementById('sendBtn');
        this.chatList = document.getElementById('chatList');
        this.newChatBtn = document.getElementById('newChatBtn');

        this.initialized = true;
    }

    waitForElements(maxWait = 5000) {
        return new Promise((resolve, reject) => {
            const startTime = Date.now();
            const requiredElements = [
                'welcomeScreen', 'messagesContainer', 'messages',
                'messageInput', 'sendBtn', 'chatList', 'newChatBtn'
            ];

            const checkElements = () => {
                const missing = requiredElements.filter(id => !document.getElementById(id));

                if (missing.length === 0) {
                    resolve();
                } else if (Date.now() - startTime > maxWait) {
                    reject(new Error(`Missing elements: ${missing.join(', ')}`));
                } else {
                    setTimeout(checkElements, 100);
                }
            };

            checkElements();
        });
    }

    bindEvents() {
        if (!this.initialized) return;

        this.sendBtn.addEventListener('click', () => this.sendMessage());
        this.messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });
        this.messageInput.addEventListener('input', () => {
            this.validateInput();
            this.autoResizeTextarea();
        });
        this.newChatBtn.addEventListener('click', () => this.startNewChat());

        // Auto-save interval
        setInterval(() => {
            if (this.conversationHistory.length > 0) {
                this.saveCurrentChatData();
            }
        }, CONSTANTS.AUTO_SAVE_INTERVAL);
    }

    checkForRestoredData() {
        setTimeout(() => {
            const savedData = sessionStorage.getItem(CONSTANTS.SESSION_KEY);
            if (savedData) {
                restoreChatDataAfterBooking();
            } else {
                this.showWelcomeScreen();
            }
        }, 100);
    }

    async sendMessage(messageText = null) {
        const message = messageText || this.messageInput.value.trim();
        if (!message || this.isTyping) return;

        if (!this.currentChatId) {
            this.currentChatId = Date.now().toString();
        }

        this.hideWelcomeScreen();
        this.addMessage(message, 'user');
        this.conversationHistory.push({
            role: 'user',
            message: message,
            timestamp: new Date().toISOString()
        });

        if (!messageText) {
            this.messageInput.value = '';
            this.validateInput();
            this.autoResizeTextarea();
        }

        this.showTypingIndicator();

        try {
            const response = await this.callChatbotAPI(message);
            this.hideTypingIndicator();

            if (response?.success && response.response) {
                this.addMessage(response.response.text, 'assistant', response.response);
                this.conversationHistory.push({
                    role: 'assistant',
                    message: response.response.text,
                    data: response.response.data,
                    type: response.response.type,
                    timestamp: new Date().toISOString()
                });

                if (this.conversationHistory.length >= 2) {
                    this.saveCurrentChatToHistory();
                }
            } else {
                this.addMessage(
                    response?.error || 'Unable to process your request. Please try again.',
                    'assistant'
                );
            }
        } catch (error) {
            this.hideTypingIndicator();
            console.error('Chat API error:', error);
            this.addMessage('Unable to process your request. Please try again.', 'assistant');
        }

        this.scrollToBottom();
    }

    addMessage(text, sender, data = null) {
        const messageElement = document.createElement('div');
        messageElement.className = `message ${sender}`;

        if (sender === 'user') {
            messageElement.innerHTML = `
                <div class="message-content">
                    <div class="message-header">
                        <div class="message-avatar"><i class="fas fa-user"></i></div>
                        <div class="message-text">${this.escapeHtml(text)}</div>
                    </div>
                </div>
            `;
        } else {
            const formattedText = this.formatBotMessage(text);
            messageElement.innerHTML = `
                <div class="message-content">
                    <div class="message-header">
                        <div class="message-avatar"><i class="fas fa-robot"></i></div>
                        <div class="message-text">${formattedText}</div>
                    </div>
                </div>
            `;

            if (data?.data) {
                this.addDataCards(messageElement, data);
            }
        }

        this.messages.appendChild(messageElement);
    }

    addDataCards(messageElement, response) {
        const cardContainer = document.createElement('div');
        cardContainer.className = 'data-cards';

        const tours = this.extractToursFromResponse(response);
        const hotels = this.extractHotelsFromResponse(response);

        const layout = this.determineCardLayout(tours, hotels, response);
        this.renderCardsWithLayout(cardContainer, layout);

        if (cardContainer.children.length > 0) {
            messageElement.querySelector('.message-content').appendChild(cardContainer);
        }
    }

    determineCardLayout(tours, hotels, response) {
        const hasTours = tours.length > 0;
        const hasHotels = hotels.length > 0;
        const hasFilters = this.detectFilterConditions(response);

        if (hasTours && hasHotels) {
            return hasFilters
                ? this.createFilteredMixedLayout(tours, hotels)
                : {
                    type: 'mixed-content',
                    leftColumn: { type: 'tour', data: tours.slice(0, 3) },
                    rightColumn: { type: 'hotel', data: hotels.slice(0, 3) }
                };
        }

        if (hasTours) {
            return hasFilters
                ? this.createFilteredSingleTypeLayout(tours, 'tour')
                : {
                    type: 'tours-split',
                    leftColumn: { type: 'tour', data: tours.slice(0, 3) },
                    rightColumn: { type: 'tour', data: tours.slice(3, 6) }
                };
        }

        if (hasHotels) {
            return hasFilters
                ? this.createFilteredSingleTypeLayout(hotels, 'hotel')
                : {
                    type: 'hotels-split',
                    leftColumn: { type: 'hotel', data: hotels.slice(0, 3) },
                    rightColumn: { type: 'hotel', data: hotels.slice(3, 6) }
                };
        }

        return { type: 'single-column', leftColumn: { type: 'tour', data: [] } };
    }

    createFilteredMixedLayout(tours, hotels) {
        const total = tours.length + hotels.length;
        const leftCount = Math.min(CONSTANTS.MAX_CARDS_PER_COLUMN, Math.ceil(total / 2));
        const rightCount = Math.min(CONSTANTS.MAX_CARDS_PER_COLUMN, total - leftCount);

        let leftData = tours.slice(0, leftCount);
        let rightData = [];

        if (tours.length > leftCount) {
            rightData = tours.slice(leftCount, leftCount + rightCount);
        } else {
            const remaining = leftCount - tours.length;
            leftData = [...tours, ...hotels.slice(0, remaining)];
            rightData = hotels.slice(remaining, remaining + rightCount);
        }

        return {
            type: 'filtered-mixed',
            leftColumn: { type: 'mixed', data: leftData },
            rightColumn: { type: 'mixed', data: rightData }
        };
    }

    createFilteredSingleTypeLayout(items, type) {
        const total = items.length;
        const leftCount = Math.min(CONSTANTS.MAX_CARDS_PER_COLUMN, Math.ceil(total / 2));

        return {
            type: `filtered-${type}`,
            leftColumn: { type, data: items.slice(0, leftCount) },
            rightColumn: { type, data: items.slice(leftCount, leftCount + CONSTANTS.MAX_CARDS_PER_COLUMN) }
        };
    }

    renderCardsWithLayout(container, layout) {
        container.className = 'data-cards two-column-layout';

        if (layout.leftColumn?.data.length > 0) {
            container.appendChild(this.createColumnElement('left', layout.leftColumn, layout.type));
        }

        if (layout.rightColumn?.data.length > 0) {
            container.appendChild(this.createColumnElement('right', layout.rightColumn, layout.type));
        }
    }

    createColumnElement(side, columnData, layoutType) {
        const column = document.createElement('div');
        column.className = `card-column ${side}-column ${columnData.type}-column`;

        const cardsWrapper = document.createElement('div');
        cardsWrapper.className = 'column-cards';

        columnData.data.forEach(item => {
            const card = columnData.type === 'tour'
                ? this.createTourCard(item)
                : this.createHotelCard(item);
            cardsWrapper.appendChild(card);
        });

        column.appendChild(cardsWrapper);
        return column;
    }

    createTourCard(tour) {
        const tourId = tour.tourid || 0;
        const cityId = tour.cityid || 11;
        const duration = parseInt(tour.duration_days || 0);

        const card = document.createElement('div');
        card.className = 'data-card tour-card';
        card.innerHTML = `
            <div class="card-content">
                <div class="card-image">
                    <img src="${this.generateTourImagePath(cityId, tourId)}" alt="${this.escapeHtml(tour.tour_name)}">
                    <span class="card-badge discount">-15%</span>
                </div>
                <div class="card-info">
                    <div class="card-title">${this.escapeHtml(tour.tour_name)}</div>
                    <div class="card-details">
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span class="detail-value">${this.escapeHtml(tour.city_name || 'Vietnam')}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span class="detail-value">${duration} ${duration === 1 ? 'day' : 'days'}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <span class="detail-value">${this.formatPrice(tour.price_per_person || 0)} VND</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="card-btn card-btn-primary" onclick="bookTour(${tourId}, ${cityId})">
                            <i class="fas fa-calendar-plus"></i> Book
                        </button>
                        <button class="card-btn card-btn-secondary" onclick="viewTourDetails(${tourId}, ${cityId})">
                            <i class="fas fa-info-circle"></i> Details
                        </button>
                    </div>
                </div>
            </div>
        `;
        return card;
    }

    createHotelCard(hotel) {
        const hotelId = hotel.hotelid || 0;
        const rating = parseFloat(hotel.ratings || 4).toFixed(1);
        const cost = parseFloat(hotel.cost || 0);

        const card = document.createElement('div');
        card.className = 'data-card hotel-card';
        card.innerHTML = `
            <div class="card-content">
                <div class="card-image">
                    <img src="${this.generateHotelImagePath(hotelId)}" alt="${this.escapeHtml(hotel.hotel || hotel.hotel_name)}">
                    <span class="card-badge rating">${rating}</span>
                </div>
                <div class="card-info">
                    <div class="card-title">${this.escapeHtml(hotel.hotel || hotel.hotel_name)}</div>
                    <div class="card-details">
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span class="detail-value">${this.escapeHtml(hotel.city_name || 'Vietnam')}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-bed"></i>
                            <span class="detail-value">${rating}/5.0</span>
                        </div>
                        ${cost > 0 ? `
                        <div class="detail-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <span class="detail-value">${this.formatPrice(cost)} VND/night</span>
                        </div>
                        ` : ''}
                    </div>
                    <div class="card-actions">
                        <button class="card-btn card-btn-primary" onclick="bookHotel(${hotelId})">
                            <i class="fas fa-bed"></i> Book
                        </button>
                        <button class="card-btn card-btn-secondary" onclick="viewHotelDetails(${hotelId})">
                            <i class="fas fa-info-circle"></i> Details
                        </button>
                    </div>
                </div>
            </div>
        `;
        return card;
    }

    // Helper Methods
    generateTourImagePath(cityId, tourId) {
        const imageMap = {
            'taybac': [1, 2, 3, 4], 'hcm': [5, 6, 7, 8], 'nhatrang': [9, 10, 11, 12],
            'hue': [13, 14, 15, 16], 'phuyen': [17, 18, 19, 20], 'dalat': [21, 22, 23, 24],
            'phuquoc': [25, 26, 27, 28], 'hoian': [29, 30, 31, 32], 'hagiang': [33, 34, 35, 36],
            'danang': [37, 38, 39, 40], 'cantho': [41, 42, 43, 44], 'hanoi': [45, 46, 47, 48]
        };
        
        const cityString = CITY_ID_MAP[cityId] || 'hcm';
        const imageIds = imageMap[cityString] || [1, 2, 3, 4];
        const imageId = imageIds[(tourId - 1) % imageIds.length];
        
        return `../tourphotoID/${imageId}.jpg`;
    }

    generateHotelImagePath(hotelId) {
        return `../hotelphotoID/${hotelId}.jpg`;
    }

    extractToursFromResponse(response) {
        if (response.data?.tours) return response.data.tours;
        if (Array.isArray(response.data)) {
            return response.data.filter(item => item.tour_name || item.tourid);
        }
        return [];
    }

    extractHotelsFromResponse(response) {
        if (response.data?.hotels) return response.data.hotels;
        if (Array.isArray(response.data)) {
            return response.data.filter(item => item.hotel || item.hotelid);
        }
        return [];
    }

    detectFilterConditions(response) {
        if (response?.filters_applied) return true;
        
        const recentMessages = this.conversationHistory.slice(-2);
        const userMessage = recentMessages.find(msg => msg.role === 'user')?.message?.toLowerCase() || '';
        
        const filterKeywords = [
            'price', 'budget', 'cost', 'under', 'over', 'day', 'days', 'star', 'rating'
        ];
        
        return filterKeywords.some(keyword => userMessage.includes(keyword));
    }

    // UI Methods
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

    showTypingIndicator() {
        this.isTyping = true;
        this.validateInput();

        const typingElement = document.createElement('div');
        typingElement.className = 'message assistant typing-message';
        typingElement.id = 'typingIndicator';
        typingElement.innerHTML = `
            <div class="message-content">
                <div class="message-header">
                    <div class="message-avatar"><i class="fas fa-robot"></i></div>
                    <div class="typing-indicator">
                        <div class="typing-dots">
                            <span></span><span></span><span></span>
                        </div>
                        <div class="typing-text">AI is analyzing...</div>
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
        document.getElementById('typingIndicator')?.remove();
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

    scrollToBottom() {
        if (!this.messagesContainer) return;
        setTimeout(() => {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }, 100);
    }

    startNewChat() {
        if (this.conversationHistory.length > 0) {
            this.saveCurrentChatToHistory();
        }

        this.currentChatId = null;
        this.conversationHistory = [];
        this.messages.innerHTML = '';
        this.showWelcomeScreen();
        this.messageInput.value = '';
        this.messageInput.focus();
        sessionStorage.removeItem(CONSTANTS.SESSION_KEY);
    }

    // Chat History Methods
    saveCurrentChatData() {
        if (this.conversationHistory.length === 0) return;

        const chatData = {
            conversationHistory: this.conversationHistory,
            messagesHTML: this.messages.innerHTML,
            currentChatId: this.currentChatId,
            timestamp: Date.now()
        };

        try {
            sessionStorage.setItem(CONSTANTS.SESSION_KEY, JSON.stringify(chatData));
        } catch (error) {
            console.error('Error saving chat data:', error);
        }
    }

    saveCurrentChatToHistory() {
        if (this.conversationHistory.length < 2) return;

        const userMessage = this.conversationHistory.find(msg => msg.role === 'user')?.message || '';
        const botResponse = this.conversationHistory.find(msg => msg.role === 'assistant');

        if (!userMessage) return;

        const chatData = {
            id: this.currentChatId || Date.now().toString(),
            title: this.generateChatTitle(userMessage),
            user_message: userMessage,
            bot_response: botResponse ? JSON.stringify({
                text: botResponse.message,
                data: botResponse.data,
                type: botResponse.type
            }) : '',
            created_at: new Date().toISOString()
        };

        const existingIndex = this.chatHistoryList.findIndex(chat => chat.id === chatData.id);
        
        if (existingIndex >= 0) {
            this.chatHistoryList[existingIndex] = chatData;
        } else {
            this.chatHistoryList.unshift(chatData);
        }

        if (this.chatHistoryList.length > CONSTANTS.MAX_HISTORY_ITEMS) {
            this.chatHistoryList = this.chatHistoryList.slice(0, CONSTANTS.MAX_HISTORY_ITEMS);
        }

        localStorage.setItem(CONSTANTS.CHAT_HISTORY_KEY, JSON.stringify(this.chatHistoryList));
        this.saveChatToServer(chatData);
        this.updateChatHistorySidebar();
    }

    async saveChatToServer(chatData) {
        try {
            await fetch('./save_chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: 1,
                    title: chatData.title,
                    user_message: chatData.user_message,
                    bot_response: chatData.bot_response
                })
            });
        } catch (error) {
            console.error('Error saving to server:', error);
        }
    }

    generateChatTitle(message) {
        const cleanMessage = message.trim().replace(/[?!.,]/g, '');
        const fillerWords = ['can you', 'could you', 'please', 'show me', 'find me'];
        
        let lowerMessage = cleanMessage.toLowerCase();
        fillerWords.forEach(filler => {
            lowerMessage = lowerMessage.replace(filler, '');
        });

        const words = lowerMessage.split(' ').filter(w => w.length > 0).slice(0, 5);
        
        if (words.length === 0) return 'New Chat';

        let title = words.map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
        
        if (cleanMessage.split(' ').length > words.length) {
            title += '...';
        }

        return title.substring(0, 50);
    }

    async loadChatHistory() {
        try {
            const localHistory = JSON.parse(localStorage.getItem(CONSTANTS.CHAT_HISTORY_KEY) || '[]');
            this.chatHistoryList = localHistory;
            this.updateChatHistorySidebar();

            const response = await fetch('./get_chat_history.php');
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.history) {
                    this.chatHistoryList = data.history;
                    localStorage.setItem(CONSTANTS.CHAT_HISTORY_KEY, JSON.stringify(this.chatHistoryList));
                    this.updateChatHistorySidebar();
                }
            }
        } catch (error) {
            console.error('Error loading chat history:', error);
        }
    }

    updateChatHistorySidebar() {
        if (!this.chatList) return;

        this.chatList.innerHTML = '';

        if (this.chatHistoryList.length === 0) {
            this.chatList.innerHTML = '<div class="no-history"><p>No chat history</p></div>';
            return;
        }

        this.chatHistoryList.forEach(chat => {
            const chatItem = this.createChatHistoryItem(chat);
            this.chatList.appendChild(chatItem);
        });
    }

    createChatHistoryItem(chat) {
        const chatItem = document.createElement('div');
        chatItem.className = 'chat-history-item';
        chatItem.innerHTML = `
            <div class="chat-item-main" onclick="window.travelChatbot.loadChatHistoryItem('${chat.id}')">
                <div class="chat-title">${this.escapeHtml(chat.title || 'Untitled')}</div>
            </div>
        `;
        return chatItem;
    }

    async loadChatHistoryItem(chatId) {
        const chat = this.chatHistoryList.find(c => c.id === chatId);
        if (!chat) return;

        this.currentChatId = chatId;
        this.conversationHistory = [];
        this.messages.innerHTML = '';
        this.hideWelcomeScreen();

        if (chat.user_message) {
            this.addMessage(chat.user_message, 'user');
            this.conversationHistory.push({
                role: 'user',
                message: chat.user_message,
                timestamp: chat.created_at
            });
        }

        if (chat.bot_response) {
            const botData = JSON.parse(chat.bot_response);
            this.addMessage(botData.text, 'assistant', botData);
            this.conversationHistory.push({
                role: 'assistant',
                message: botData.text,
                data: botData.data,
                type: botData.type,
                timestamp: chat.created_at
            });
        }

        this.scrollToBottom();
    }

    // API Methods
    async callChatbotAPI(message) {
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), CONSTANTS.REQUEST_TIMEOUT);

            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    message: message,
                    conversation_history: this.conversationHistory.slice(-5)
                }),
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const contentType = response.headers.get("content-type");
            if (!contentType?.includes("application/json")) {
                throw new Error("Invalid response format");
            }

            return await response.json();

        } catch (error) {
            console.error('API call failed:', error);
            throw error;
        }
    }

    // Utility Methods
    formatPrice(price) {
        if (!price || price === 0) return '0';
        return new Intl.NumberFormat('vi-VN').format(price);
    }

    formatBotMessage(text) {
        return text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n\n/g, '<br><br>')
            .replace(/\n/g, '<br>');
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.style.cssText = `
            position: fixed; top: 20px; right: 20px; background: #ff4757;
            color: white; padding: 15px; border-radius: 5px; z-index: 9999;
        `;
        errorDiv.textContent = message;
        document.body.appendChild(errorDiv);
        setTimeout(() => errorDiv.remove(), 5000);
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    try {
        window.travelChatbot = new TravelChatbot();
    } catch (error) {
        console.error('Failed to initialize chatbot:', error);
    }
});