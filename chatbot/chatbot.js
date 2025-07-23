document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Element References ---
    const chatWindow = document.getElementById('chatbot-window');
    const fab = document.getElementById('chatbot-fab');
    const closeBtn = document.getElementById('chatbot-close-btn');
    const messagesContainer = document.getElementById('chatbot-messages');
    const input = document.getElementById('chatbot-input');
    const sendBtn = document.getElementById('chatbot-send-btn');

    // --- Data Store ---
    let toursData = {};
    let hotelsData = {};
    // This mapping is from your loggedinhome.php file to link city names to their IDs for hotel searches.
    const cityIdMap = {
        'tay bac': 10, 'ho chi minh': 11, 'nha trang': 12, 'hue': 13,
        'phu yen': 14, 'da lat': 15, 'phu quoc': 16, 'hoi an': 17, 'ha giang': 18,
        'da nang': 19 // FIXED: Added "da nang" to the list of known cities.
    };

    // --- State Management ---
    let conversationState = 'idle'; // Can be 'idle', 'awaiting_tour_destination', 'awaiting_hotel_destination'

    // --- Event Listeners ---
    fab.addEventListener('click', () => toggleChat(true));
    closeBtn.addEventListener('click', () => toggleChat(false));
    sendBtn.addEventListener('click', handleUserInput);
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') handleUserInput();
    });

    /**
     * Toggles the chat window visibility.
     * @param {boolean} show - True to show, false to hide.
     */
    function toggleChat(show) {
        if (show) {
            chatWindow.classList.remove('hidden');
            fab.classList.add('hidden');
        } else {
            chatWindow.classList.add('hidden');
            fab.classList.remove('hidden');
        }
    }

    /**
     * Handles the user's message submission.
     */
    function handleUserInput() {
        const query = input.value.trim();
        if (!query) return;

        addMessage(query, 'user');
        input.value = '';
        
        // Show typing indicator
        addMessage('...', 'bot', true);

        // Process the query after a short delay to simulate thinking
        setTimeout(() => {
            processQuery(query.toLowerCase());
        }, 1000);
    }

    /**
     * Adds a message to the chat interface.
     * @param {string} text - The message content (can include HTML).
     * @param {string} sender - 'user' or 'bot'.
     * @param {boolean} isTyping - If true, adds a typing indicator style.
     */
    function addMessage(text, sender, isTyping = false) {
        // Remove previous typing indicator if it exists
        const existingTyping = messagesContainer.querySelector('.typing-indicator');
        if (existingTyping) {
            existingTyping.remove();
        }

        const messageDiv = document.createElement('div');
        messageDiv.className = `chatbot-message ${sender}-message`;
        messageDiv.innerHTML = text; // Use innerHTML to render links and formatting

        if (isTyping) {
            messageDiv.classList.add('typing-indicator');
            // Simple dot animation
            messageDiv.innerHTML = '<span>.</span><span>.</span><span>.</span>';
        }
        
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    /**
     * The core logic for processing the user's query.
     * @param {string} query - The user's lowercase input.
     */
    function processQuery(query) {
        // First, check conversation state
        if (conversationState === 'awaiting_tour_destination') {
            findTours(query);
            conversationState = 'idle';
            return;
        }
        if (conversationState === 'awaiting_hotel_destination') {
            findHotels(query);
            conversationState = 'idle';
            return;
        }

        // Attempt to extract a destination early
        const destination = extractDestination(query, [...Object.keys(toursData), ...Object.keys(cityIdMap)]);

        // If idle, check for keywords or a standalone destination
        if (query.includes('tour') || query.includes('journey')) {
            if (destination) {
                findTours(destination);
            } else {
                addMessage('Of course! Where would you like to go?', 'bot');
                conversationState = 'awaiting_tour_destination';
            }
        } else if (query.includes('hotel') || query.includes('stay') || query.includes('room')) {
             if (destination) {
                findHotels(destination);
             } else {
                addMessage('I can certainly help with hotels. Which city are you interested in?', 'bot');
                conversationState = 'awaiting_hotel_destination';
             }
        } else if (query.includes('hello') || query.includes('hi')) {
            addMessage('Hello! How can I assist you with tours or hotels today?', 'bot');
        } else if (destination) { // If a destination is found without other keywords, default to tours
            findTours(destination); // Automatically suggest tours for the detected destination
        }
        else {
            addMessage("I'm sorry, I can only assist with finding tours and hotels. Please ask me something like 'Find a tour to Da Lat' or 'Show me hotels in Hue'.", 'bot');
        }
    }

    /**
     * Finds and displays tours for a given destination.
     * @param {string} destination - The name of the destination city.
     */
    function findTours(destination) {
        const cityKey = destination.toLowerCase().replace(' ', ''); // e.g., "da nang" -> "danang"
        const cityData = toursData[cityKey] || toursData[destination];

        if (cityData && cityData.tours) {
            let response = `Excellent choice! Here are some of our popular tours in ${capitalize(destination)}:<br><br>`;
            response += '<div class="results-container">';
            cityData.tours.slice(0, 3).forEach(tour => { // Show top 3
                response += `
                    <div class="result-card">
                        <strong>${tour.title}</strong><br>
                        Price: ${tour.price.toLocaleString('vi-VN')} ₫<br>
                        <a href="${tour.link}" target="_blank">View Details</a>
                    </div>
                `;
            });
            response += '</div>';
            response += `<br>You can see all tours for this destination <a href="/Journey/viewjourney.php?id=${cityKey || destination}" target="_blank">here</a>.`;
            addMessage(response, 'bot');
        } else {
            addMessage(`I'm sorry, I couldn't find any tours for "${capitalize(destination)}". Please try another city.`, 'bot');
        }
    }
    
    /**
     * Finds and displays hotels for a given destination.
     * @param {string} destination - The name of the destination city.
     */
    function findHotels(destination) {
        const cityId = cityIdMap[destination.toLowerCase()];
        if (cityId) {
            const link = `/hotelinfo/view_hotels.php?city_id=${cityId}`;
            let response = `Great! I've found our list of hotels in ${capitalize(destination)}. You can view and filter them here:<br><br>`;
            response += `<a href="${link}" class="results-link-button" target="_blank">View Hotels in ${capitalize(destination)}</a>`;
            addMessage(response, 'bot');
        } else {
            addMessage(`I'm sorry, I don't have hotel information for "${capitalize(destination)}". Please choose from our popular destinations.`, 'bot');
        }
    }


    /**
     * A helper to find a known destination from a user's query string.
     * @param {string} query - The user's input.
     * @param {string[]} knownDestinations - An array of possible destinations.
     * @returns {string|null} The found destination or null.
     */
    function extractDestination(query, knownDestinations) {
        for (const dest of knownDestinations) {
            if (query.includes(dest)) {
                return dest;
            }
        }
        return null;
    }

    /**
     * Capitalizes the first letter of a string.
     * @param {string} s - The string to capitalize.
     * @returns {string}
     */
    function capitalize(s) {
        return s.charAt(0).toUpperCase() + s.slice(1);
    }


    /**
     * Fetches the necessary JSON data when the chatbot initializes.
     */
    async function initializeData() {
        try {
            const [toursResponse, hotelsResponse] = await Promise.all([
                fetch('/Journey/tour.json'),
                fetch('/hotelinfo/hoteladdress.json')
            ]);
            toursData = await toursResponse.json();
            hotelsData = await hotelsResponse.json();
            // Send initial greeting after data is loaded
            setTimeout(() => {
                 addMessage("Hello! I'm your VietTransit assistant. I can help you find tours or hotels. How can I assist you today?", 'bot');
            }, 500);
        } catch (error) {
            console.error('Error loading chatbot data:', error);
            addMessage('I seem to be having trouble accessing my information right now. Please try again in a moment.', 'bot');
        }
    }

    // --- Initialization ---
    initializeData();
});