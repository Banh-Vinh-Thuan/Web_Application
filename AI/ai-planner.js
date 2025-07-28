document.addEventListener('DOMContentLoaded', () => {
    // DOM Elements
    const chatInput = document.getElementById('chatInput');
    const sendButton = document.getElementById('sendButton');
    const clearChatButton = document.getElementById('clearChat');
    const chatMessages = document.getElementById('chatMessages');
    const resultsSection = document.getElementById('resultsSection');
    const planTableBody = document.getElementById('travelPlanTableBody');
    const costBreakdownDiv = document.getElementById('costBreakdown');
    const travelTipsDiv = document.getElementById('travelTips');
    const exportExcelButton = document.getElementById('exportExcel');
    const suggestionButtons = document.querySelectorAll('.suggestion-btn');

    let lastGeneratedPlan = null;

    // --- EVENT LISTENERS ---
    sendButton.addEventListener('click', handleSendMessage);
    chatInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleSendMessage();
        }
    });
    clearChatButton.addEventListener('click', () => {
        chatMessages.innerHTML = '';
        resultsSection.style.display = 'none';
        lastGeneratedPlan = null;
        addWelcomeMessage();
    });
    suggestionButtons.forEach(button => {
        button.addEventListener('click', () => {
            const query = button.getAttribute('data-query');
            chatInput.value = query;
            handleSendMessage();
        });
    });
    exportExcelButton.addEventListener('click', handleExportExcel);

    // --- FUNCTIONS ---
    async function handleSendMessage() {
        const query = chatInput.value.trim();
        if (!query) return;

        appendMessage(query, 'user');
        chatInput.value = '';
        showLoadingIndicator();

        try {
            const response = await fetch('api/planner_controller.php?action=generate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ query: query })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            removeLoadingIndicator();

            if (data.error) {
                appendMessage(data.error, 'bot', 'error');
            } else {
                lastGeneratedPlan = data;
                displayPlanResults(data);
                appendMessage("Here is your personalized travel plan. Check the details below!", 'bot');
            }
        } catch (error) {
            console.error('Error fetching travel plan:', error);
            removeLoadingIndicator();
            appendMessage('Oops, something went wrong with the AI planner. Please try again.', 'bot', 'error');
        }
    }

    async function handleExportExcel() {
        if (!lastGeneratedPlan) {
            alert("Please generate a plan first before exporting.");
            return;
        }

        try {
            const response = await fetch('api/planner_controller.php?action=export', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(lastGeneratedPlan)
            });

            if (!response.ok) throw new Error('Failed to download file.');

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = 'VietTransit_Travel_Plan.csv';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } catch (error) {
            console.error('Error exporting to Excel:', error);
            alert('Could not export the plan. Please try again.');
        }
    }

    function appendMessage(text, sender, type = 'normal') {
        const messageWrapper = document.createElement('div');
        messageWrapper.className = `message-wrapper ${sender}-message`;
        if (type === 'loading') {
            messageWrapper.id = 'loading-indicator';
        }

        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';
        avatar.innerHTML = `<i class="fas fa-${sender === 'user' ? 'user' : 'robot'}"></i>`;

        const content = document.createElement('div');
        content.className = 'message-content';
        if (type === 'error') {
            content.classList.add('error-message');
        }
        content.innerText = text;

        messageWrapper.appendChild(avatar);
        messageWrapper.appendChild(content);
        chatMessages.appendChild(messageWrapper);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function displayPlanResults(data) {
        planTableBody.innerHTML = '';
        costBreakdownDiv.innerHTML = '';
        travelTipsDiv.innerHTML = '';

        data.plan.forEach(item => {
            const row = planTableBody.insertRow();
            row.innerHTML = `
                <td>${item.day}</td>
                <td>${item.location}</td>
                <td>${item.activity}</td>
                <td>${item.cost.toLocaleString('vi-VN')}</td>
                <td>${item.notes}</td>
            `;
        });

        let costHtml = '<ul>';
        for (const [key, value] of Object.entries(data.summary.cost_breakdown || {})) {
            costHtml += `<li><strong>${key.replace('_', ' ')}:</strong> ${value.toLocaleString('vi-VN')} VND</li>`;
        }
        costHtml += `</ul><hr><p><strong>Total Estimated Cost: ${data.summary.total_cost.toLocaleString('vi-VN')} VND</strong></p>`;
        costBreakdownDiv.innerHTML = costHtml;

        travelTipsDiv.innerHTML = data.tips;

        resultsSection.style.display = 'block';
    }

    function showLoadingIndicator() {
        appendMessage('AI is crafting your trip...', 'bot', 'loading');
    }

    function removeLoadingIndicator() {
        const loadingIndicator = document.getElementById('loading-indicator');
        if (loadingIndicator) {
            loadingIndicator.remove();
        }
    }

    function addWelcomeMessage() {
        const welcomeDiv = document.createElement('div');
        welcomeDiv.className = 'welcome-message';
        welcomeDiv.innerHTML = `
            <div class="message-avatar"><i class="fas fa-robot"></i></div>
            <div class="message-content">
                <h3>Welcome to AI Travel Planner!</h3>
                <p>I'm here to help you plan your perfect trip to Vietnam. Just tell me:</p>
                <ul>
                    <li>🏝️ Where do you want to go?</li>
                    <li>📅 How many days?</li>
                    <li>💰 What's your budget?</li>
                    <li>👥 Who's traveling with you?</li>
                </ul>
                <p>Example: <em>"I have 10 million VND, want to go to Da Lat for 4 days with my family. Please suggest me."</em></p>
            </div>`;
        chatMessages.appendChild(welcomeDiv);
    }
});