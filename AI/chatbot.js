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

        // Use the layout type provided by the backend response
        const layoutType = response.layout_type || 'default';

        // The data to be rendered is in response.data
        const renderData = response.data || {};

        this.renderCardsWithLayout(cardContainer, layoutType, renderData);

        if (cardContainer.children.length > 0) {
            const messageContent = messageElement.querySelector('.message-content');
            if (messageContent) {
                messageContent.appendChild(cardContainer);
            }
        }
    }

    determineCardLayout(tours, hotels, response) {
        const hasTours = tours.length > 0;
        const hasHotels = hotels.length > 0;

        // Case 1: Mixed results (tours and hotels)
        if (hasTours && hasHotels) {
            return {
                type: 'mixed-content',
                leftColumn: { type: 'tour', data: tours },
                rightColumn: { type: 'hotel', data: hotels }
            };
        }

        // FIX: Case 2: Only tours were found -> use single section layout
        if (hasTours) {
            return {
                type: 'single-section',
                sectionType: 'tour',
                data: tours
            };
        }

        // FIX: Case 3: Only hotels were found -> use single section layout
        if (hasHotels) {
            return {
                type: 'single-section',
                sectionType: 'hotel',
                data: hotels
            };
        }

        // Case 4: No results
        return { type: 'empty' };
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

    renderCardsWithLayout(container, layoutType, data) {
        container.innerHTML = '';
        
        const tours = data.tours || [];
        const hotels = data.hotels || [];
        const cities = data.cities || [];
        const isMultiCity = data.multi_city || false;
        const hasConditions = data.has_conditions || false;
        
        // CASE 5-6: Mixed content (tour + hotel)
        if (layoutType === 'mixed_content' && tours.length > 0 && hotels.length > 0) {
            container.className = 'data-cards two-column-layout';
            
            const tourCity = data.tour_city || tours[0].city_name || 'Vietnam';
            const hotelCity = data.hotel_city || hotels[0].city_name || 'Vietnam';
            
            // Left column: Tours with header
            const tourColumn = this.createCityColumn(tours, tourCity, 'tour', hasConditions);
            container.appendChild(tourColumn);
            
            // Right column: Hotels with header
            const hotelColumn = this.createCityColumn(hotels, hotelCity, 'hotel', hasConditions);
            container.appendChild(hotelColumn);
            
            return;
        }
        
        // CASE 3 & 7: Multi-city tours - FIXED TO MATCH HOTEL LAYOUT
        if (layoutType === 'multi_city_tours' && isMultiCity && cities.length >= 2) {
            container.className = 'data-cards two-column-layout';
            
            // Get separate arrays for each city
            const city1Tours = data.city1_tours || [];
            const city2Tours = data.city2_tours || [];
            
            // Validation
            if (!hasConditions) {
                if (city1Tours.length < 3 || city2Tours.length < 3) {
                    console.warn('Not enough tours for multi-city, using fallback');
                    this.renderSingleCityLayout(container, tours, 'tour', cities[0]);
                    return;
                }
            }
            
            // FIXED: Create columns with individual headers like hotel layout
            // Left column: City 1 Tours with header
            const leftColumn = this.createCityColumn(city1Tours, cities[0], 'tour', hasConditions);
            container.appendChild(leftColumn);
            
            // Right column: City 2 Tours with header
            const rightColumn = this.createCityColumn(city2Tours, cities[1], 'tour', hasConditions);
            container.appendChild(rightColumn);
            
            return;
        }
        
        // CASE 4: Multi-city hotels - KEEP SAME STRUCTURE
        if (layoutType === 'multi_city_hotels' && isMultiCity && cities.length >= 2) {
            container.className = 'data-cards two-column-layout';
            
            // Get separate arrays for each city
            const city1Hotels = data.city1_hotels || [];
            const city2Hotels = data.city2_hotels || [];
            
            // Validation
            if (!hasConditions) {
                if (city1Hotels.length < 3 || city2Hotels.length < 3) {
                    console.warn('Not enough hotels for multi-city, using fallback');
                    this.renderSingleCityLayout(container, hotels, 'hotel', cities[0]);
                    return;
                }
            }
            
            // Left column: City 1 Hotels with header
            const leftColumn = this.createCityColumn(city1Hotels, cities[0], 'hotel', hasConditions);
            container.appendChild(leftColumn);
            
            // Right column: City 2 Hotels with header
            const rightColumn = this.createCityColumn(city2Hotels, cities[1], 'hotel', hasConditions);
            container.appendChild(rightColumn);
            
            return;
        }
        
        // CASE 1 & 8: Single city tours
        if (layoutType === 'single_tours' && tours.length > 0) {
            const cityName = tours[0].city_name || 'Vietnam';
            
            if (!hasConditions && tours.length >= 6) {
                this.renderTwoColumnSplit(container, tours.slice(0, 6), cityName, 'tour');
            } else {
                this.renderTwoColumnSplit(container, tours, cityName, 'tour');
            }
            return;
        }
        
        // CASE 2: Single city hotels
        if (layoutType === 'single_hotels' && hotels.length > 0) {
            const cityName = hotels[0].city_name || 'Vietnam';
            
            if (!hasConditions && hotels.length >= 6) {
                this.renderTwoColumnSplit(container, hotels.slice(0, 6), cityName, 'hotel');
            } else {
                this.renderTwoColumnSplit(container, hotels, cityName, 'hotel');
            }
            return;
        }
    }

    createColumnElement(side, columnData, layoutType) {
        const column = document.createElement('div');
        column.className = `card-column ${side}-column ${columnData.type}-column`;

        // Add column header nếu cần
        const header = this.createColumnHeader(columnData.type);
        if (header) {
            column.appendChild(header);
        }

        // Create cards wrapper
        const cardsWrapper = document.createElement('div');
        cardsWrapper.className = 'column-cards';

        // Add cards
        columnData.data.forEach(item => {
            const card = columnData.type === 'tour' || item.tour_name
                ? this.createTourCard(item)
                : this.createHotelCard(item);
            cardsWrapper.appendChild(card);
        });

        column.appendChild(cardsWrapper);
        return column;
    }

    createColumnHeader(type) {
        if (type === 'tour') {
            const header = document.createElement('div');
            header.className = 'column-header';
            header.innerHTML = '<i class="fas fa-map-signs"></i> Tours';
            return header;
        } else if (type === 'hotel') {
            const header = document.createElement('div');
            header.className = 'column-header';
            header.innerHTML = '<i class="fas fa-bed"></i> Hotels';
            return header;
        }
        return null;
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
                    <img src="${this.generateTourImagePath(cityId, tourId)}" 
                        alt="${this.escapeHtml(tour.tour_name)}" 
                        loading="lazy">
                    <span class="card-badge discount">-15%</span>
                </div>
                <div class="card-info">
                    <div class="card-title">${this.escapeHtml(tour.tour_name)}</div>
                    <div class="card-details">
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>${this.escapeHtml(tour.city_name || 'Vietnam')}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span>${duration} ${duration === 1 ? 'day' : 'days'}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>${this.formatPrice(tour.price_per_person || 0)} VND</span>
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
                    <img src="${this.generateHotelImagePath(hotelId)}" 
                        alt="${this.escapeHtml(hotel.hotel || hotel.hotel_name)}" 
                        loading="lazy">
                    <span class="card-badge rating">${rating}</span>
                </div>
                <div class="card-info">
                    <div class="card-title">${this.escapeHtml(hotel.hotel || hotel.hotel_name)}</div>
                    <div class="card-details">
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>${this.escapeHtml(hotel.city_name || 'Vietnam')}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-star"></i>
                            <span>${rating}/5.0</span>
                        </div>
                        ${cost > 0 ? `
                        <div class="detail-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>${this.formatPrice(cost)} VND/night</span>
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
    groupToursByCity(tours, cities) {
        const grouped = {};
        cities.forEach(city => grouped[city] = []);
        
        tours.forEach(tour => {
            const cityGroup = tour.city_group || tour.city_name;
            if (grouped[cityGroup]) {
                grouped[cityGroup].push(tour);
            }
        });
        
        return grouped;
    }

    // Helper: Group hotels by city
    groupHotelsByCity(hotels, cities) {
        const grouped = {};
        cities.forEach(city => grouped[city] = []);
        
        hotels.forEach(hotel => {
            const cityGroup = hotel.city_group || hotel.city_name;
            if (grouped[cityGroup]) {
                grouped[cityGroup].push(hotel);
            }
        });
        
        return grouped;
    }


    renderCardsWithLayout(container, layoutType, data) {
        container.innerHTML = '';
        
        const tours = data.tours || [];
        const hotels = data.hotels || [];
        const cities = data.cities || [];
        const isMultiCity = data.multi_city || false;
        const hasConditions = data.has_conditions || false;
        
        // CASE 5-6: Mixed content (tour + hotel)
        if (layoutType === 'mixed_content' && tours.length > 0 && hotels.length > 0) {
            container.className = 'data-cards two-column-layout';
            
            const tourCity = data.tour_city || tours[0].city_name || 'Vietnam';
            const hotelCity = data.hotel_city || hotels[0].city_name || 'Vietnam';
            
            // Left column: Tours with header
            const tourColumn = this.createCityColumn(tours, tourCity, 'tour', hasConditions);
            container.appendChild(tourColumn);
            
            // Right column: Hotels with header
            const hotelColumn = this.createCityColumn(hotels, hotelCity, 'hotel', hasConditions);
            container.appendChild(hotelColumn);
            
            return;
        }
        
        // CASE 3 & 7: Multi-city tours - FIXED TO MATCH HOTEL LAYOUT
        if (layoutType === 'multi_city_tours' && isMultiCity && cities.length >= 2) {
            container.className = 'data-cards two-column-layout';
            
            // Get separate arrays for each city
            const city1Tours = data.city1_tours || [];
            const city2Tours = data.city2_tours || [];
            
            // Validation
            if (!hasConditions) {
                if (city1Tours.length < 3 || city2Tours.length < 3) {
                    console.warn('Not enough tours for multi-city, using fallback');
                    this.renderSingleCityLayout(container, tours, 'tour', cities[0]);
                    return;
                }
            }
            
            // FIXED: Create columns with individual headers like hotel layout
            // Left column: City 1 Tours with header
            const leftColumn = this.createCityColumn(city1Tours, cities[0], 'tour', hasConditions);
            container.appendChild(leftColumn);
            
            // Right column: City 2 Tours with header
            const rightColumn = this.createCityColumn(city2Tours, cities[1], 'tour', hasConditions);
            container.appendChild(rightColumn);
            
            return;
        }
        
        // CASE 4: Multi-city hotels - KEEP SAME STRUCTURE
        if (layoutType === 'multi_city_hotels' && isMultiCity && cities.length >= 2) {
            container.className = 'data-cards two-column-layout';
            
            // Get separate arrays for each city
            const city1Hotels = data.city1_hotels || [];
            const city2Hotels = data.city2_hotels || [];
            
            // Validation
            if (!hasConditions) {
                if (city1Hotels.length < 3 || city2Hotels.length < 3) {
                    console.warn('Not enough hotels for multi-city, using fallback');
                    this.renderSingleCityLayout(container, hotels, 'hotel', cities[0]);
                    return;
                }
            }
            
            // Left column: City 1 Hotels with header
            const leftColumn = this.createCityColumn(city1Hotels, cities[0], 'hotel', hasConditions);
            container.appendChild(leftColumn);
            
            // Right column: City 2 Hotels with header
            const rightColumn = this.createCityColumn(city2Hotels, cities[1], 'hotel', hasConditions);
            container.appendChild(rightColumn);
            
            return;
        }
        
        // CASE 1 & 8: Single city tours
        if (layoutType === 'single_tours' && tours.length > 0) {
            const cityName = tours[0].city_name || 'Vietnam';
            
            if (!hasConditions && tours.length >= 6) {
                this.renderTwoColumnSplit(container, tours.slice(0, 6), cityName, 'tour');
            } else {
                this.renderTwoColumnSplit(container, tours, cityName, 'tour');
            }
            return;
        }
        
        // CASE 2: Single city hotels
        if (layoutType === 'single_hotels' && hotels.length > 0) {
            const cityName = hotels[0].city_name || 'Vietnam';
            
            if (!hasConditions && hotels.length >= 6) {
                this.renderTwoColumnSplit(container, hotels.slice(0, 6), cityName, 'hotel');
            } else {
                this.renderTwoColumnSplit(container, hotels, cityName, 'hotel');
            }
            return;
        }
    }

    // UPDATED: createCityColumn to support both single and multi-city layouts
    createCityColumn(items, cityName, itemType, hasConditions) {
        const column = document.createElement('div');
        column.className = `card-column ${itemType}-column`;
        
        // Header with icon - SAME FOR BOTH TOURS AND HOTELS
        const header = document.createElement('div');
        header.className = 'column-header';
        const icon = itemType === 'tour' ? 'fa-compass' : 'fa-hotel';
        const label = itemType === 'tour' ? 'Tour' : 'Hotel';
        header.innerHTML = `<i class="fas ${icon}"></i> ${label} in ${this.escapeHtml(cityName)}`;
        column.appendChild(header);
        
        // Cards wrapper
        const cardsWrapper = document.createElement('div');
        cardsWrapper.className = 'column-cards';
        
        // CRITICAL: For basic multi-city queries, show exactly 3 cards
        // For conditional queries, show all found items
        const limit = hasConditions ? items.length : 3;
        
        items.slice(0, limit).forEach(item => {
            const card = itemType === 'tour' ? this.createTourCard(item) : this.createHotelCard(item);
            cardsWrapper.appendChild(card);
        });
        
        column.appendChild(cardsWrapper);
        
        return column;
    }

    renderTwoColumnSplit(container, items, cityName, itemType) {
        container.className = 'data-cards two-column-layout has-shared-header';
        
        // Create SINGLE shared header spanning both columns
        const sharedHeader = document.createElement('div');
        sharedHeader.className = `shared-column-header ${itemType}-header`;
        const icon = itemType === 'tour' ? 'fa-compass' : 'fa-hotel';
        const label = itemType === 'tour' ? 'Tour' : 'Hotel';
        sharedHeader.innerHTML = `<i class="fas ${icon}"></i> ${label} in ${this.escapeHtml(cityName)}`;
        container.appendChild(sharedHeader);
        
        const halfPoint = Math.ceil(items.length / 2);
        const leftItems = items.slice(0, halfPoint);
        const rightItems = items.slice(halfPoint);
        
        // Left column (no header)
        const leftCol = this.createCityColumnNoHeader(leftItems, itemType);
        container.appendChild(leftCol);
        
        // Right column (no header, if items exist)
        if (rightItems.length > 0) {
            const rightCol = this.createCityColumnNoHeader(rightItems, itemType);
            container.appendChild(rightCol);
        }
    }

    createCityColumnNoHeader(items, itemType) {
        const column = document.createElement('div');
        column.className = `card-column ${itemType}-column no-header`;
        
        const cardsWrapper = document.createElement('div');
        cardsWrapper.className = 'column-cards';
        
        items.forEach(item => {
            const card = itemType === 'tour' ? this.createTourCard(item) : this.createHotelCard(item);
            cardsWrapper.appendChild(card);
        });
        
        column.appendChild(cardsWrapper);
        return column;
    }

    renderSingleCityLayout(container, items, itemType, cityName) {
        container.className = 'data-cards two-column-layout';
        
        const halfPoint = Math.ceil(items.length / 2);
        const leftItems = items.slice(0, Math.min(3, halfPoint));
        const rightItems = items.slice(3, 6);
        
        const leftCol = this.createCityColumn(leftItems, cityName, itemType, false);
        container.appendChild(leftCol);
        
        if (rightItems.length > 0) {
            const rightCol = this.createCityColumn(rightItems, cityName, itemType, false);
            container.appendChild(rightCol);
        }
    }

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
        chatItem.dataset.chatId = chat.id;
        
        chatItem.innerHTML = `
            <div class="chat-item-main">
                <div class="chat-title" onclick="window.travelChatbot.loadChatHistoryItem('${chat.id}')">${this.escapeHtml(chat.title || 'Untitled')}</div>
                <div class="chat-item-actions">
                    <button class="chat-action-btn edit-btn" onclick="window.travelChatbot.editChatTitle('${chat.id}')" title="Rename">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button class="chat-action-btn delete-btn" onclick="window.travelChatbot.deleteChatHistoryItem('${chat.id}')" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        return chatItem;
    }

    editChatTitle(chatId) {
        const chatItem = document.querySelector(`.chat-history-item[data-chat-id="${chatId}"]`);
        if (!chatItem) return;
        
        const titleElement = chatItem.querySelector('.chat-title');
        const currentTitle = titleElement.textContent;
        
        // Tạo input để edit
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'chat-title-input';
        input.value = currentTitle;
        
        // Replace title với input
        titleElement.replaceWith(input);
        input.focus();
        input.select();
        
        // Xử lý save
        const saveEdit = () => {
            const newTitle = input.value.trim();
            if (newTitle && newTitle !== currentTitle) {
                // Update trong array
                const chat = this.chatHistoryList.find(c => c.id === chatId);
                if (chat) {
                    chat.title = newTitle;
                    localStorage.setItem(CONSTANTS.CHAT_HISTORY_KEY, JSON.stringify(this.chatHistoryList));
                    this.updateChatToServer(chatId, { title: newTitle });
                }
            }
            
            // Restore lại title element
            const newTitleElement = document.createElement('div');
            newTitleElement.className = 'chat-title';
            newTitleElement.textContent = newTitle || currentTitle;
            newTitleElement.onclick = () => this.loadChatHistoryItem(chatId);
            input.replaceWith(newTitleElement);
        };
        
        // Events
        input.addEventListener('blur', saveEdit);
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveEdit();
            }
        });
    }
  
    deleteChatHistoryItem(chatId) {
        if (!confirm('Delete this chat?')) return;
        
        // Xóa khỏi array
        this.chatHistoryList = this.chatHistoryList.filter(chat => chat.id !== chatId);
        
        // Lưu lại localStorage
        localStorage.setItem(CONSTANTS.CHAT_HISTORY_KEY, JSON.stringify(this.chatHistoryList));
        
        // Xóa trên server
        this.deleteChatFromServer(chatId);
        
        // Update UI
        this.updateChatHistorySidebar();
        
        // Nếu đang xem chat này thì clear
        if (this.currentChatId === chatId) {
            this.startNewChat();
        }
    }

    async updateChatToServer(chatId, updates) {
        try {
            await fetch('./update_chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    chat_id: chatId,
                    ...updates
                })
            });
        } catch (error) {
            console.error('Error updating chat on server:', error);
        }
    }

    async deleteChatFromServer(chatId) {
        try {
            await fetch('./delete_chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ chat_id: chatId })
            });
        } catch (error) {
            console.error('Error deleting chat from server:', error);
        }
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

window.editChatTitle = (chatId) => window.travelChatbot?.editChatTitle(chatId);
window.deleteChatHistoryItem = (chatId) => window.travelChatbot?.deleteChatHistoryItem(chatId);