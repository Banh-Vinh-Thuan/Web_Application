
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
        this.apiEndpoint = './rag_chatbot_backend_refactored.php';
        this.currentChatId = null;
        this.isTyping = false;
        this.conversationHistory = [];
        this.chatHistoryList = [];
        this.editingChatId = null;
        this.initialized = false;
        this.lastSuccessfulResponse = null;
        
        this.initializeElements();
        this.bindEvents();
        this.loadChatHistory();
        this.checkForRestoredData();
    }
    
    initializeElements() {
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
        
        if (this.sendBtn._boundClickHandler) {
            this.sendBtn.removeEventListener('click', this.sendBtn._boundClickHandler);
        }
        if (this.messageInput._boundKeyHandler) {
            this.messageInput.removeEventListener('keypress', this.messageInput._boundKeyHandler);
        }
        
        this.sendBtn._boundClickHandler = () => this.sendMessage();
        this.sendBtn.addEventListener('click', this.sendBtn._boundClickHandler);
        
        this.messageInput._boundKeyHandler = (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        };
        this.messageInput.addEventListener('keypress', this.messageInput._boundKeyHandler);
        
        this.messageInput.addEventListener('input', () => this.validateInput());
        
        if (this.newChatBtn) {
            this.newChatBtn.addEventListener('click', () => this.startNewChat());
        }
        
        this.messageInput.addEventListener('input', () => this.autoResizeTextarea());
        
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.suggestion-btn')) {
                this.removeSuggestions();
            }
            
            if (!e.target.closest('.chat-title-input') && !e.target.closest('.title-edit-btn')) {
                this.finishTitleEdit();
            }
        });

        window.addEventListener('beforeunload', () => {
            this.saveCurrentChatToHistory();
        });

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
        if (this.chatList.querySelector('.bulk-delete-controls')) {
            return;
        }
        
        const bulkControls = document.createElement('div');
        bulkControls.className = 'bulk-delete-controls';
        bulkControls.innerHTML = `
            <div class="bulk-actions">
                <button class="bulk-btn clear-all-btn" onclick="window.travelChatbot.clearAllHistory()">
                    <i class="fas fa-trash-alt"></i> Clear All History
                </button>
            </div>
        `;
        
        this.chatList.insertBefore(bulkControls, this.chatList.firstChild);
    }

    async clearAllHistory() {
        if (!confirm('Are you sure you want to delete ALL chat history? This cannot be undone.')) {
            return;
        }
        
        try {
            this.chatHistoryList = [];
            localStorage.removeItem('chatHistory');
            
            const response = await fetch('./clear_all_chats.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    user_id: 1
                })
            });
            
            this.updateChatHistorySidebar();
            this.startNewChat();
            
            console.log('All chat history cleared');
            
        } catch (error) {
            console.error('Error clearing chat history:', error);
            alert('Failed to clear history. Please try again.');
        }
    }

    // Core Card Layout Logic - NEW IMPLEMENTATION
    addDataCards(messageElement, response) {
        const cardContainer = document.createElement('div');
        cardContainer.className = 'data-cards';

        const tours = this.extractToursFromResponse(response);
        const hotels = this.extractHotelsFromResponse(response);

        console.log('Data cards input:', {
            total_tours: tours.length,
            total_hotels: hotels.length,
            response_type: response.type
        });

        // Apply filters before rendering
        const filteredTours = this.applyFiltersToData(tours, response);
        const filteredHotels = this.applyFiltersToData(hotels, response);

        // Get unique cities
        const tourCities = [...new Set(filteredTours.map(t => t.city_group || t.city || t.city_name))];
        const hotelCities = [...new Set(filteredHotels.map(h => h.city_group || h.city || h.city_name))];

        console.log('Cities detected:', {
            tour_cities: tourCities,
            hotel_cities: hotelCities
        });

        // Determine layout based on content - PASS RESPONSE OBJECT
        const layout = this.determineCardLayout(filteredTours, filteredHotels, tourCities, hotelCities, response);

        console.log('Layout determined:', layout);

        // Render cards based on layout
        this.renderCardsWithLayout(cardContainer, layout, filteredTours, filteredHotels);

        if (cardContainer.children.length > 0) {
            messageElement.querySelector('.message-content').appendChild(cardContainer);
        }
    }
    
    determineCardLayout(tours, hotels, tourCities, hotelCities, response) {
        const hasTours = tours.length > 0;
        const hasHotels = hotels.length > 0;
        
        // Check if this query has filtering conditions (price, duration, rating)
        const hasFilterConditions = this.detectFilterConditions(response);
        
        console.log('Layout analysis:', {
            hasTours,
            hasHotels,
            tourCount: tours.length,
            hotelCount: hotels.length,
            hasFilterConditions
        });

        // Case 1: Mixed content (tours and hotels) - Tours always on left
        if (hasTours && hasHotels) {
            if (hasFilterConditions) {
                // Apply conditional distribution when filtering
                return this.createFilteredMixedLayout(tours, hotels);
            } else {
                // Default: 3-3 distribution
                return {
                    type: 'mixed-content',
                    leftColumn: { type: 'tour', data: tours.slice(0, 3), city: tourCities[0] || 'Various' },
                    rightColumn: { type: 'hotel', data: hotels.slice(0, 3), city: hotelCities[0] || 'Various' }
                };
            }
        }

        // Case 2: Only tours
        if (hasTours && !hasHotels) {
            if (hasFilterConditions) {
                // Apply conditional distribution when filtering
                return this.createFilteredSingleTypeLayout(tours, 'tour');
            } else {
                // Default: Split 6 tours into 3-3
                const totalTours = tours.slice(0, 6);
                return {
                    type: 'tours-split',
                    leftColumn: { type: 'tour', data: totalTours.slice(0, 3), city: 'Tours' },
                    rightColumn: { type: 'tour', data: totalTours.slice(3, 6), city: 'Tours' }
                };
            }
        }

        // Case 3: Only hotels
        if (hasHotels && !hasTours) {
            if (hasFilterConditions) {
                // Apply conditional distribution when filtering
                return this.createFilteredSingleTypeLayout(hotels, 'hotel');
            } else {
                // Default: Split 6 hotels into 3-3
                const totalHotels = hotels.slice(0, 6);
                return {
                    type: 'hotels-split',
                    leftColumn: { type: 'hotel', data: totalHotels.slice(0, 3), city: 'Hotels' },
                    rightColumn: { type: 'hotel', data: totalHotels.slice(3, 6), city: 'Hotels' }
                };
            }
        }

        // Default fallback - single column
        return {
            type: 'single-column',
            leftColumn: { type: hasTours ? 'tour' : 'hotel', data: (hasTours ? tours : hotels).slice(0, 6) }
        };
    }

    detectFilterConditions(response) {
        // Check if response indicates filtering was applied
        if (response && response.filters_applied) {
            return true;
        }
        
        // Check conversation history for filter keywords
        const recentMessages = this.conversationHistory.slice(-2);
        const userMessage = recentMessages.find(msg => msg.role === 'user')?.message?.toLowerCase() || '';
        
        // Filter condition keywords
        const filterKeywords = [
            // Price filters
            'price', 'budget', 'cost', 'cheap', 'expensive', 'under', 'over', 'below', 'above',
            'million', 'triệu', 'vnd', 'dollar',
            
            // Duration filters  
            'day', 'days', 'ngày', '1 day', '2 day', '3 day', '4 day', '5 day', '6 day', '7 day',
            
            // Rating filters
            'star', 'rating', 'rated', 'sao', '1 star', '2 star', '3 star', '4 star', '5 star',
            
            // Quality filters
            'luxury', 'premium', 'budget', 'basic', 'high-end', 'low-cost'
        ];
        
        const hasFilterKeywords = filterKeywords.some(keyword => userMessage.includes(keyword));
        
        console.log('Filter detection:', {
            userMessage: userMessage.substring(0, 100),
            hasFilterKeywords,
            matchedKeywords: filterKeywords.filter(k => userMessage.includes(k))
        });
        
        return hasFilterKeywords;
    }

    createFilteredMixedLayout(tours, hotels) {
        const totalItems = tours.length + hotels.length;
        
        if (totalItems === 1) {
            // 1 item total: put in left column
            const item = tours.length > 0 ? tours[0] : hotels[0];
            const type = tours.length > 0 ? 'tour' : 'hotel';
            return {
                type: 'filtered-mixed-single',
                leftColumn: { type: type, data: [item], city: 'Results' },
                rightColumn: { type: type, data: [], city: 'Results' }
            };
        } else if (totalItems === 2) {
            // 2 items total: 1 left, 1 right
            if (tours.length === 1 && hotels.length === 1) {
                return {
                    type: 'filtered-mixed-balanced',
                    leftColumn: { type: 'tour', data: [tours[0]], city: 'Tours' },
                    rightColumn: { type: 'hotel', data: [hotels[0]], city: 'Hotels' }
                };
            } else if (tours.length === 2) {
                return {
                    type: 'filtered-mixed-tours',
                    leftColumn: { type: 'tour', data: [tours[0]], city: 'Tours' },
                    rightColumn: { type: 'tour', data: [tours[1]], city: 'Tours' }
                };
            } else {
                return {
                    type: 'filtered-mixed-hotels',
                    leftColumn: { type: 'hotel', data: [hotels[0]], city: 'Hotels' },
                    rightColumn: { type: 'hotel', data: [hotels[1]], city: 'Hotels' }
                };
            }
        } else if (totalItems === 3) {
            // 3 items total: 2 left, 1 right
            if (tours.length >= 2) {
                // Prioritize tours on left
                const rightItem = tours.length > 2 ? tours[2] : (hotels.length > 0 ? hotels[0] : null);
                const rightType = tours.length > 2 ? 'tour' : 'hotel';
                
                return {
                    type: 'filtered-mixed-3items',
                    leftColumn: { type: 'tour', data: tours.slice(0, 2), city: 'Tours' },
                    rightColumn: { type: rightType, data: rightItem ? [rightItem] : [], city: rightType === 'tour' ? 'Tours' : 'Hotels' }
                };
            } else {
                // More hotels than tours
                return {
                    type: 'filtered-mixed-3items',
                    leftColumn: { type: 'hotel', data: hotels.slice(0, 2), city: 'Hotels' },
                    rightColumn: { type: tours.length > 0 ? 'tour' : 'hotel', data: tours.length > 0 ? [tours[0]] : [hotels[2]], city: tours.length > 0 ? 'Tours' : 'Hotels' }
                };
            }
        } else {
            // 4+ items: distribute as evenly as possible, max 3 per side
            const leftCount = Math.min(3, Math.ceil(totalItems / 2));
            const rightCount = Math.min(3, totalItems - leftCount);
            
            // Prioritize tours on left side
            let leftData = [];
            let rightData = [];
            let leftType = 'tour';
            let rightType = 'hotel';
            
            if (tours.length >= leftCount) {
                leftData = tours.slice(0, leftCount);
                if (tours.length > leftCount) {
                    rightData = tours.slice(leftCount, leftCount + rightCount);
                    rightType = 'tour';
                } else {
                    rightData = hotels.slice(0, rightCount);
                    rightType = 'hotel';
                }
            } else {
                leftData = [...tours, ...hotels.slice(0, leftCount - tours.length)];
                rightData = hotels.slice(leftCount - tours.length, leftCount - tours.length + rightCount);
                leftType = 'mixed';
                rightType = 'hotel';
            }
            
            return {
                type: 'filtered-mixed-multiple',
                leftColumn: { type: leftType, data: leftData, city: 'Results' },
                rightColumn: { type: rightType, data: rightData, city: 'Results' }
            };
        }
    }

    createFilteredSingleTypeLayout(items, type) {
        const totalItems = items.length;
        
        if (totalItems === 1) {
            // 1 item: put in left column
            return {
                type: `filtered-${type}-single`,
                leftColumn: { type: type, data: [items[0]], city: type === 'tour' ? 'Tours' : 'Hotels' },
                rightColumn: { type: type, data: [], city: type === 'tour' ? 'Tours' : 'Hotels' }
            };
        } else if (totalItems === 2) {
            // 2 items: 1 left, 1 right
            return {
                type: `filtered-${type}-double`,
                leftColumn: { type: type, data: [items[0]], city: type === 'tour' ? 'Tours' : 'Hotels' },
                rightColumn: { type: type, data: [items[1]], city: type === 'tour' ? 'Tours' : 'Hotels' }
            };
        } else if (totalItems === 3) {
            // 3 items: 2 left, 1 right
            return {
                type: `filtered-${type}-triple`,
                leftColumn: { type: type, data: items.slice(0, 2), city: type === 'tour' ? 'Tours' : 'Hotels' },
                rightColumn: { type: type, data: [items[2]], city: type === 'tour' ? 'Tours' : 'Hotels' }
            };
        } else {
            // 4+ items: distribute evenly, max 3 per side
            const leftCount = Math.min(3, Math.ceil(totalItems / 2));
            const rightCount = Math.min(3, totalItems - leftCount);
            
            return {
                type: `filtered-${type}-multiple`,
                leftColumn: { type: type, data: items.slice(0, leftCount), city: type === 'tour' ? 'Tours' : 'Hotels' },
                rightColumn: { type: type, data: items.slice(leftCount, leftCount + rightCount), city: type === 'tour' ? 'Tours' : 'Hotels' }
            };
        }
    }

    cityMatches(itemCity, queryCity) {
        if (!itemCity || !queryCity) return false;
        
        const itemCityLower = itemCity.toString().toLowerCase().trim();
        const queryCityLower = queryCity.toString().toLowerCase().trim();
        
        // Direct match
        if (itemCityLower === queryCityLower) return true;
        
        // Substring match
        if (itemCityLower.includes(queryCityLower) || queryCityLower.includes(itemCityLower)) {
            return true;
        }
        
        // City aliases mapping
        const cityMappings = {
            'ho chi minh': ['saigon', 'hcm', 'ho chi minh city', 'hcmc'],
            'hanoi': ['ha noi', 'hà nội'],
            'da nang': ['danang', 'đà nẵng'], 
            'hoi an': ['hoian', 'hội an'],
            'hue': ['huế'],
            'da lat': ['dalat', 'đà lạt'],
            'phu quoc': ['phuquoc', 'phú quốc'],
            'can tho': ['cantho', 'cần thơ'],
            'ha giang': ['hagiang', 'hà giang'],
            'phu yen': ['phuyen', 'phú yên'],
            'tay bac': ['taybac', 'tây bắc', 'northwest'],
            'nha trang': ['nhatrang']
        };
        
        for (const [standard, variations] of Object.entries(cityMappings)) {
            if (queryCityLower.includes(standard)) {
                for (const variation of variations) {
                    if (itemCityLower.includes(variation)) {
                        return true;
                    }
                }
            }
            
            // Check reverse mapping
            for (const variation of variations) {
                if (queryCityLower.includes(variation) && itemCityLower.includes(standard)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    renderCardsWithLayout(container, layout, tours, hotels) {
        container.className = 'data-cards two-column-layout';

        // Create left column
        if (layout.leftColumn && layout.leftColumn.data.length > 0) {
            const leftColumn = this.createColumnElement('left', layout.leftColumn, layout.type);
            container.appendChild(leftColumn);
        }

        // Create right column
        if (layout.rightColumn && layout.rightColumn.data.length > 0) {
            const rightColumn = this.createColumnElement('right', layout.rightColumn, layout.type);
            container.appendChild(rightColumn);
        }
    }

    createColumnElement(side, columnData, layoutType) {
        const column = document.createElement('div');
        column.className = `card-column ${side}-column ${columnData.type}-column`;

        // Create header
        const header = this.createColumnHeader(columnData, layoutType);
        if (header) {
            column.appendChild(header);
        }

        // Create cards wrapper
        const cardsWrapper = document.createElement('div');
        cardsWrapper.className = 'column-cards';

        // Add cards
        columnData.data.forEach(item => {
            const card = columnData.type === 'tour' ? this.createTourCard(item) : this.createHotelCard(item);
            cardsWrapper.appendChild(card);
        });

        column.appendChild(cardsWrapper);
        return column;
    }

    createColumnHeader(columnData, layoutType) {
        // Don't show headers for simple splits without filtering
        const simpleLayouts = ['tours-split', 'hotels-split'];
        if (simpleLayouts.includes(layoutType)) {
            return null;
        }
        
        // Don't show headers for single item results
        if (layoutType.includes('single') && columnData.data.length <= 1) {
            return null;
        }
        
        const header = document.createElement('h4');
        header.className = 'column-header';

        let icon, text;
        if (columnData.type === 'tour' || columnData.type === 'mixed') {
            icon = 'fa-map-signs';
            if (layoutType.startsWith('filtered-')) {
                text = `Found ${columnData.data.length} ${columnData.data.length === 1 ? 'tour' : 'tours'}`;
            } else {
                text = `Tours (${columnData.data.length})`;
            }
        } else {
            icon = 'fa-bed';
            if (layoutType.startsWith('filtered-')) {
                text = `Found ${columnData.data.length} ${columnData.data.length === 1 ? 'hotel' : 'hotels'}`;
            } else {
                text = `Hotels (${columnData.data.length})`;
            }
        }

        header.innerHTML = `<i class="fas ${icon}"></i> ${text}`;
        return header;
    }

    // Helper functions
    applyFiltersToData(data, response) {
        let filtered = [...data];

        // If this is multi-city data, preserve the city grouping
        if (response.data && response.data.multi_city) {
            // Data is already properly grouped on the backend
            return filtered.slice(0, 6);
        }

        // For non-multi-city, limit to maximum 6 total items
        return filtered.slice(0, 6);
    }

    extractToursFromResponse(response) {
        if (response.data && Array.isArray(response.data.tours)) {
            return response.data.tours;
        }
        if (response.type === 'destination_info' && response.data?.tours) {
            return response.data.tours;
        }
        if (Array.isArray(response.data)) {
            return response.data.filter(item => item.tour_name || item.tourid);
        }
        return [];
    }

    extractHotelsFromResponse(response) {
        if (response.data && Array.isArray(response.data.hotels)) {
            return response.data.hotels;
        }
        if (response.type === 'destination_info' && response.data?.hotels) {
            return response.data.hotels;
        }
        if (Array.isArray(response.data)) {
            return response.data.filter(item => item.hotel || item.hotelid);
        }
        return [];
    }

    // Card creation functions (preserved from original)
    generateTourImagePath(cityId, tourId) {
        const cityIdToString = {
            10: 'taybac', 11: 'hcm', 12: 'nhatrang', 13: 'hue', 14: 'phuyen', 
            15: 'dalat',  16: 'phuquoc', 17: 'hoian', 18: 'hagiang', 19: 'danang',
            20: 'cantho', 21: 'hanoi'
        };
        
        const cityString = cityIdToString[cityId] || 'hcm';
        
        const imageMap = {
            'taybac': [1, 2, 3, 4, 93, 94, 95, 96],
            'hcm': [5, 6, 7, 8, 89, 90, 91, 92],
            'nhatrang': [85, 86, 87, 88, 9, 10, 11, 12],
            'hue': [13, 14, 15, 16, 81, 82, 83, 84],
            'phuyen': [77, 78, 79, 80, 17, 18, 19, 20],
            'dalat': [21, 22, 23, 24, 73, 74, 75, 76],        
            'phuquoc': [25, 26, 27, 28, 69, 70, 71, 72],
            'hoian': [29, 30, 31, 32, 65, 66, 67, 68],   
            'hagiang': [61, 62, 63, 64, 33, 34, 35, 36],
            'danang': [37, 38, 39, 40, 57, 58, 59, 60],
            'cantho': [53, 54, 55, 56, 41, 42, 43, 44],
            'hanoi': [45, 46, 47, 48, 49, 50, 51, 52]
        };
            
        const imageIds = imageMap[cityString] || [1, 2, 3, 4];
        const imageId = imageIds[(tourId - 1) % imageIds.length];
            
        return `../tourphotoID/${imageId}.jpg`;
    }

    generateHotelImagePath(hotelId) {
        return `../hotelphotoID/${hotelId}.jpg`;
    }

    createTourCard(tour) {
        const tourId = tour.tourid || tour.id || 0;
        const cityName = tour.city || tour.city_name || 'Vietnam';
        const cityId = tour.cityid || this.getCityIdFromName(cityName) || 11;
        const duration = parseInt(tour.duration_days || 0);
        const tourName = tour.tour_name || 'Tour Package';

        const card = document.createElement('div');
        card.className = 'data-card tour-card';

        card.innerHTML = `
            <div class="card-content">
                <div class="card-image">
                    <img src="${this.generateTourImagePath(cityId, tourId)}" alt="${this.escapeHtml(tourName)}" onerror="this.src='../images/default-tour.jpg'">
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
                            <span class="detail-value">${this.formatPrice(tour.price_per_person || 0)} VND</span>
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
        const hotelId = hotel.hotelid || hotel.id || 0;
        const hotelName = hotel.hotel || hotel.hotel_name || hotel.name || 'Hotel';
        const rating = parseFloat(hotel.ratings || 4).toFixed(1);
        const cost = parseFloat(hotel.cost || 0);

        const card = document.createElement('div');
        card.className = 'data-card hotel-card';

        let priceDetail = '';
        if (cost > 0) {
            priceDetail = `
                <div class="detail-item">
                    <i class="fas fa-money-bill-wave"></i>
                    <span class="detail-label">Price:</span>
                    <span class="detail-value">${this.formatPrice(cost)} VND/night</span>
                </div>
            `;
        }

        card.innerHTML = `
            <div class="card-content">
                <div class="card-image">
                    <img src="${this.generateHotelImagePath(hotelId)}" alt="${this.escapeHtml(hotelName)}" onerror="this.src='../images/default-hotel.jpg'">
                    <span class="card-badge rating">${rating}</span>
                </div>
                <div class="card-info">
                    <div class="card-title" title="${this.escapeHtml(hotelName)}">
                        ${this.escapeHtml(hotelName)}
                    </div>
                    <div class="card-details">
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span class="detail-label">Location:</span>
                            <span class="detail-value">${this.escapeHtml(hotel.city || hotel.city_name || 'Vietnam')}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-bed"></i>
                            <span class="detail-label">Rating:</span>
                            <span class="detail-value">${rating}/5.0</span>
                        </div>
                        ${priceDetail}
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
            'Tay Bac': 10,
            'Da Nang': 19,
            'Can Tho': 20,
            'Hanoi': 21
        };
        
        return cityMapping[cityName] || cityMapping[cityName.toLowerCase()] || 11;
    }

    // Rest of the class methods remain the same as original...
    formatChatTime(timestamp) {
        if (!timestamp) return '';
        
        const date = new Date(timestamp);
        const now = new Date();
        const diffTime = now - date;
        const diffMinutes = Math.floor(diffTime / (1000 * 60));
        const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffMinutes < 1) {
            return 'Just now';
        } else if (diffMinutes < 60) {
            return `${diffMinutes}m ago`;
        } else if (diffHours < 24) {
            return `${diffHours}h ago`;
        } else if (diffDays < 7) {
            return `${diffDays}d ago`;
        } else {
            return date.toLocaleDateString();
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
        if (this.conversationHistory.length < 2) return;
        
        const userMessage = this.conversationHistory.find(msg => msg.role === 'user')?.message || '';
        const botResponse = this.conversationHistory.find(msg => msg.role === 'assistant') || null;
        
        if (!userMessage) return;
        
        const title = this.generateChatTitle(userMessage);
        const chatId = this.currentChatId || Date.now().toString();
        
        const now = new Date();
        const timestamp = now.toISOString();
        
        const chatData = {
            id: chatId,
            chat_id: chatId,
            title: title,
            user_message: userMessage,
            bot_response: botResponse ? JSON.stringify({
                text: botResponse.message,
                data: botResponse.data,
                type: botResponse.type
            }) : '',
            created_at: timestamp,
            timestamp: timestamp
        };
        
        const existingIndex = this.chatHistoryList.findIndex(chat => 
            (chat.id && chat.id == chatId) || (chat.chat_id && chat.chat_id == chatId)
        );
        
        if (existingIndex >= 0) {
            this.chatHistoryList[existingIndex] = {
                ...this.chatHistoryList[existingIndex],
                ...chatData
            };
        } else {
            this.chatHistoryList.unshift(chatData);
        }
        
        if (this.chatHistoryList.length > 50) {
            this.chatHistoryList = this.chatHistoryList.slice(0, 50);
        }
        
        localStorage.setItem('chatHistory', JSON.stringify(this.chatHistoryList));
        this.saveChatToServer(chatData);
        this.updateChatHistorySidebar();
        
        console.log('Chat saved to history:', title);
    }

    async saveChatToServer(chatData) {
        try {
            const response = await fetch('./save_chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    user_id: 1,
                    title: chatData.title,
                    user_message: chatData.user_message,
                    bot_response: chatData.bot_response
                })
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    console.log('Chat saved to server successfully');
                }
            }
        } catch (error) {
            console.error('Error saving chat to server:', error);
        }
    }

    generateChatTitle(message) {
        let cleanMessage = message.trim();
        cleanMessage = cleanMessage.replace(/[?!.,]/, '');
        
        const fillerWords = [
            'can you', 'could you', 'please', 'help me', 'i want to', 'i need to',
            'show me', 'find me', 'tell me about', 'what is', 'what are', 'how to', 
            'where is', 'give me', 'let me know', 'i would like', 'looking for'
        ];
        
        let lowerMessage = cleanMessage.toLowerCase();
        fillerWords.forEach(filler => {
            lowerMessage = lowerMessage.replace(new RegExp('\\b' + filler + '\\b', 'g'), '');
        });
        
        const words = lowerMessage.split(' ').filter(word => word.length > 0);
        const selectedWords = words.slice(0, 4);
        
        if (selectedWords.length === 0) {
            const originalWords = cleanMessage.split(' ').filter(word => word.length > 0);
            selectedWords.push(...originalWords.slice(0, 3));
        }
        
        if (selectedWords.length === 0) {
            return 'New Chat';
        }
        
        let title = selectedWords.map(word => 
            word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
        ).join(' ');
        
        if (words.length > selectedWords.length || cleanMessage.split(' ').length > selectedWords.length) {
            title += '...';
        }
        
        if (title.length > 50) {
            title = title.substring(0, 47) + '...';
        }
        
        return title;
    }

    updateChatHistorySidebar() {
        if (!this.chatList) return;
        
        console.log('Updating chat history sidebar with', this.chatHistoryList.length, 'items');
        
        this.chatList.innerHTML = '';
        
        if (this.chatHistoryList.length >= 2) {
            const bulkControls = document.createElement('div');
            bulkControls.className = 'bulk-delete-controls show';
            bulkControls.innerHTML = `
                <div class="bulk-actions">
                    <button class="bulk-btn clear-all-btn" onclick="window.travelChatbot.clearAllHistory()">
                        <i class="fas fa-trash-alt"></i> Clear All History
                    </button>
                </div>
            `;
            this.chatList.appendChild(bulkControls);
        }
        
        if (this.chatHistoryList.length === 0) {
            const noHistoryDiv = document.createElement('div');
            noHistoryDiv.className = 'no-history';
            noHistoryDiv.innerHTML = `
                <p>No chat history yet</p>
                <small>Your conversations will appear here</small>
            `;
            this.chatList.appendChild(noHistoryDiv);
            return;
        }
        
        const sortedHistory = [...this.chatHistoryList].sort((a, b) => {
            const timeA = new Date(a.created_at || a.timestamp || 0).getTime();
            const timeB = new Date(b.created_at || b.timestamp || 0).getTime();
            
            if (timeA === timeB) {
                const idA = parseInt(a.id || a.chat_id || 0);
                const idB = parseInt(b.id || b.chat_id || 0);
                return idB - idA;
            }
            
            return timeB - timeA;
        });
        
        const groupedHistory = this.groupHistoryByDate(sortedHistory);
        const groupOrder = ['Today', 'Yesterday', 'This Week', 'This Month', 'Older'];
        
        groupOrder.forEach(groupTitle => {
            if (groupedHistory[groupTitle] && groupedHistory[groupTitle].length > 0) {
                const dateHeader = document.createElement('div');
                dateHeader.className = 'chat-date-header';
                dateHeader.textContent = groupTitle;
                this.chatList.appendChild(dateHeader);
                
                groupedHistory[groupTitle].forEach(chat => {
                    const chatItem = this.createChatHistoryItem(chat);
                    this.chatList.appendChild(chatItem);
                });
            }
        });
    }

    groupHistoryByDate(history) {
        const groups = {};
        const now = new Date();
        
        history.forEach(chat => {
            const chatDate = new Date(chat.created_at || chat.timestamp);
            const diffTime = now - chatDate;
            const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
            
            let groupKey;
            if (diffDays === 0) {
                groupKey = 'Today';
            } else if (diffDays === 1) {
                groupKey = 'Yesterday';
            } else if (diffDays < 7) {
                groupKey = 'This Week';
            } else if (diffDays < 30) {
                groupKey = 'This Month';
            } else {
                groupKey = 'Older';
            }
            
            if (!groups[groupKey]) {
                groups[groupKey] = [];
            }
            groups[groupKey].push(chat);
        });
        
        return groups;
    }

    createChatHistoryItem(chat) {
        const chatItem = document.createElement('div');
        chatItem.className = 'chat-history-item';
        chatItem.setAttribute('data-chat-id', chat.id || chat.chat_id);
        
        const isCurrentChat = this.currentChatId && (chat.id == this.currentChatId || chat.chat_id == this.currentChatId);
        if (isCurrentChat) {
            chatItem.classList.add('active');
        }
        
        chatItem.innerHTML = `
            <div class="chat-item-main" onclick="window.travelChatbot.loadChatHistoryItem('${chat.id || chat.chat_id}')">
                <div class="chat-title" title="${this.escapeHtml(chat.title || 'Untitled Chat')}">
                    ${this.escapeHtml(chat.title || 'Untitled Chat')}
                </div>
                <div class="chat-item-actions" onclick="event.stopPropagation()">
                    <button class="chat-action-btn edit-btn" onclick="window.travelChatbot.editChatTitle('${chat.id || chat.chat_id}')" title="Rename">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="chat-action-btn delete-btn" onclick="window.travelChatbot.deleteChatHistoryItem('${chat.id || chat.chat_id}')" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        return chatItem;
    }

    async loadChatHistoryItem(chatId) {
        try {
            console.log('Loading chat history item:', chatId);
            
            if (this.conversationHistory.length > 0) {
                this.saveCurrentChatToHistory();
            }
            
            let chat = this.chatHistoryList.find(c => (c.id == chatId || c.chat_id == chatId));
            
            if (!chat) {
                const response = await fetch(`./get_chat_history.php?chat_id=${chatId}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.chat) {
                        chat = data.chat;
                    }
                }
            }
            
            if (!chat) {
                console.error('Chat not found:', chatId);
                return;
            }
            
            this.currentChatId = chatId;
            this.conversationHistory = [];
            this.messages.innerHTML = '';
            this.hideWelcomeScreen();
            
            if (chat.user_message) {
                this.addMessage(chat.user_message, 'user');
                this.conversationHistory.push({
                    role: 'user',
                    message: chat.user_message,
                    timestamp: chat.created_at || new Date().toISOString()
                });
            }
            
            if (chat.bot_response) {
                let botResponseData = null;
                try {
                    botResponseData = JSON.parse(chat.bot_response);
                } catch (e) {
                    botResponseData = { text: chat.bot_response };
                }
                
                this.addMessage(botResponseData.text || chat.bot_response, 'assistant', botResponseData);
                this.conversationHistory.push({
                    role: 'assistant',
                    message: botResponseData.text || chat.bot_response,
                    data: botResponseData.data,
                    type: botResponseData.type,
                    timestamp: chat.created_at || new Date().toISOString()
                });
            }
            
            this.updateChatHistorySidebar();
            this.scrollToBottom();
            
            console.log('Chat history item loaded successfully');
            
        } catch (error) {
            console.error('Error loading chat history item:', error);
            alert('Failed to load chat. Please try again.');
        }
    }

    editChatTitle(chatId) {
        const chatItem = document.querySelector(`[data-chat-id="${chatId}"]`);
        if (!chatItem) return;
        
        const titleElement = chatItem.querySelector('.chat-title');
        if (!titleElement) return;
        
        const currentTitle = titleElement.textContent.trim();
        
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'chat-title-input';
        input.value = currentTitle;
        
        titleElement.innerHTML = '';
        titleElement.appendChild(input);
        input.focus();
        input.select();
        
        this.editingChatId = chatId;
        this.originalTitle = currentTitle;
        
        const finishEdit = (save = true) => {
            const newTitle = input.value.trim();
            
            if (save && newTitle && newTitle !== currentTitle && newTitle.length > 0) {
                this.updateChatTitle(chatId, newTitle);
                titleElement.textContent = newTitle;
            } else {
                titleElement.textContent = currentTitle;
            }
            
            this.editingChatId = null;
            this.originalTitle = null;
        };
        
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                finishEdit(true);
            } else if (e.key === 'Escape') {
                e.preventDefault();
                finishEdit(false);
            }
        });
        
        input.addEventListener('blur', () => finishEdit(true));
    }

    async updateChatTitle(chatId, newTitle) {
        try {
            const chatIndex = this.chatHistoryList.findIndex(chat => 
                chat.id == chatId || chat.chat_id == chatId
            );
            
            if (chatIndex >= 0) {
                this.chatHistoryList[chatIndex].title = newTitle;
                localStorage.setItem('chatHistory', JSON.stringify(this.chatHistoryList));
                console.log('Title updated in localStorage immediately:', newTitle);
                
                this.updateChatHistorySidebar();
            }
            
            setTimeout(async () => {
                try {
                    const response = await fetch('./update_chat_title.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            chat_id: chatId,
                            title: newTitle
                        })
                    });
                    
                    if (response.ok) {
                        console.log('Chat title updated on server successfully');
                    }
                } catch (serverError) {
                    console.warn('Failed to update title on server, but local update successful:', serverError);
                }
            }, 100);
            
        } catch (error) {
            console.error('Error updating chat title:', error);
        }
    }

    finishTitleEdit() {
        if (this.editingChatId) {
            const chatItem = document.querySelector(`[data-chat-id="${this.editingChatId}"]`);
            if (chatItem) {
                const input = chatItem.querySelector('.chat-title-input');
                if (input) {
                    const titleElement = input.parentElement;
                    titleElement.textContent = input.value.trim() || 'Untitled Chat';
                }
            }
            this.editingChatId = null;
        }
    }

    async deleteChatHistoryItem(chatId) {
        const chatToDelete = this.chatHistoryList.find(chat => 
            chat.id == chatId || chat.chat_id == chatId
        );
        
        const chatTitle = chatToDelete ? chatToDelete.title : 'this chat';
        
        if (!confirm(`Are you sure you want to delete "${chatTitle}"? This cannot be undone.`)) {
            return;
        }
        
        try {
            this.chatHistoryList = this.chatHistoryList.filter(chat => 
                chat.id != chatId && chat.chat_id != chatId
            );
            localStorage.setItem('chatHistory', JSON.stringify(this.chatHistoryList));
            console.log('Chat deleted from localStorage immediately');
            
            this.updateChatHistorySidebar();
            
            if (this.currentChatId == chatId) {
                this.startNewChat();
            }
            
            setTimeout(async () => {
                try {
                    const response = await fetch('./delete_chat.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            chat_id: chatId
                        })
                    });
                    
                    if (response.ok) {
                        console.log('Chat deleted from server successfully');
                    }
                } catch (serverError) {
                    console.warn('Failed to delete from server, but local deletion successful:', serverError);
                }
            }, 100);
            
            console.log('Chat deleted successfully:', chatTitle);
            
        } catch (error) {
            console.error('Error deleting chat:', error);
            alert('Failed to delete chat. Please try again.');
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
            const hasRealConversation = this.conversationHistory.some(msg => 
                msg.role === 'assistant' && msg.message && 
                !msg.message.toLowerCase().includes('i apologize') &&
                msg.message.trim().length > 50
            );
            
            if (hasRealConversation) {
                this.saveCurrentChatToHistory();
                console.log('Current chat saved to history before starting new chat');
            }
        }
        
        this.currentChatId = null;
        this.conversationHistory = [];
        this.lastSuccessfulResponse = null;
        
        if (this.messages) {
            this.messages.innerHTML = '';
        }
        
        this.showWelcomeScreen();
        
        if (this.messageInput) {
            this.messageInput.value = '';
            this.messageInput.focus();
        }
        
        sessionStorage.removeItem('chatData');
        this.finishTitleEdit();
        this.removeSuggestions();
        this.updateChatHistorySidebar();
        
        console.log('New chat started successfully');
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
                this.lastSuccessfulResponse = response.response;
                
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
                let errorText;
                if (response && response.error) {
                    if (response.error.includes('processing') || response.error.includes('server')) {
                        errorText = 'The system is temporarily unavailable. Please try again in a moment.';
                    } else {
                        errorText = response.error;
                    }
                } else {
                    errorText = 'Unable to process your request right now. Please try rephrasing your question or try again later.';
                }
                this.addMessage(errorText, 'assistant');
                
                console.error('Backend response error:', response);
            }
        } catch (error) {
            this.hideTypingIndicator();
            console.error('Chat API error:', error);
            
            let errorMessage;
            
            if (error.message && error.message.includes('JSON')) {
                errorMessage = 'Server configuration issue detected. Please check the backend setup.';
            } else if (error.message && (error.message.includes('404') || error.message.includes('Not Found'))) {
                errorMessage = 'Chat service is not available. Please verify the backend is running.';
            } else if (error.message && error.message.includes('timeout')) {
                errorMessage = 'Request timed out. The server may be busy, please try again.';
            } else if (error.message && error.message.includes('Network')) {
                errorMessage = 'Connection problem detected. Please check your internet connection.';
            } else {
                errorMessage = 'Unable to process your request. Please try again.';
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
            .replace(/â€¢\s/g, '<br>â€¢ ');
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
            console.log('Loading chat history...');
            
            const localHistory = JSON.parse(localStorage.getItem('chatHistory') || '[]');
            this.chatHistoryList = localHistory;
            this.updateChatHistorySidebar();
            
            const response = await fetch('./get_chat_history.php', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success && data.history) {
                    this.chatHistoryList = data.history;
                    localStorage.setItem('chatHistory', JSON.stringify(this.chatHistoryList));
                    this.updateChatHistorySidebar();
                    console.log('Chat history loaded from server:', this.chatHistoryList.length);
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
                    if (errorText.includes('PHP') || errorText.includes('Fatal error') || errorText.includes('Parse error')) {
                        throw new Error('Server configuration error detected. Please check the backend setup.');
                    } else {
                        throw new Error('Server is temporarily unavailable. Please try again in a moment.');
                    }
                } else {
                    throw new Error(`Service unavailable (${response.status}). Please try again later.`);
                }
            }
            
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                const text = await response.text();
                console.error('Non-JSON response:', text.substring(0, 500));
                
                if (text.includes('PHP') || text.includes('Fatal error') || text.includes('Parse error') || text.includes('Warning')) {
                    throw new Error("Backend configuration issue detected. Please check the PHP backend for syntax errors.");
                } else {
                    throw new Error("Server returned unexpected response format. Please contact support.");
                }
            }
            
            const data = await response.json();
            console.log('Backend response:', data);
            
            if (data && data.success === false && data.error) {
                console.warn('Backend returned error:', data.error);
                return data;
            }
            
            return data;
            
        } catch (error) {
            console.error('API call failed:', error);
            
            if (error.name === 'AbortError') {
                throw new Error('Request timed out. The server may be busy, please try again.');
            } else if (error.name === 'TypeError' && error.message.includes('fetch')) {
                throw new Error('Network connection problem. Please check your internet connection.');
            } else {
                throw error;
            }
        }
    } 

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