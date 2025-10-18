<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Travel Assistant</title>
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="AI_planner.css">
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button id="mobileMenuToggle" class="mobile-menu-toggle" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <a href="../login/Loggedinhome.php" id="sidebarToggle" class="sidebar-toggle" title="Go to home">
                <i class="fas fa-home"></i>
            </a>
            <button id="newChatBtn" class="new-chat-btn">
                <i class="fas fa-plus"></i>
                <span>New chat</span>
            </button>
        </div>
        
        <div class="sidebar-content">
            <!-- Search Chat History -->
            <div class="chat-search-container">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="chatSearchInput" placeholder="Search conversations..." class="chat-search-input">
                    <button id="clearSearch" class="clear-search-btn" style="display: none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div class="chat-history">
                <div class="chat-section">
                    <div class="section-title">Conversations</div>
                    <div id="chatList" class="chat-list">
                        <!-- Chat items will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
        
        <div class="sidebar-footer">
            <!-- Dark Mode Toggle -->
            <div class="theme-toggle-container">
                <button id="themeToggle" class="theme-toggle-btn" title="Toggle dark mode">
                    <i class="fas fa-moon"></i>
                    <span>Dark Mode</span>
                </button>
            </div>
            
            <div class="user-info" onclick="toggleUserMenu()">
                <div class="user-avatar">
                    <?php 
                    $userName = isset($_SESSION['username']) ? $_SESSION['username'] : 
                               (isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 
                               (isset($_SESSION['name']) ? $_SESSION['name'] : 'Guest'));
                    $userEmail = isset($_SESSION['email']) ? $_SESSION['email'] : 'Free';
                    echo strtoupper(substr($userName, 0, 1));
                    ?>
                </div>
                <div class="user-details">
                    <div class="username"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="user-email"><?php echo htmlspecialchars($userEmail); ?></div>
                </div>
                <div class="user-menu-toggle">
                    <i class="fas fa-ellipsis-h"></i>
                </div>
                <div id="userMenu" class="user-menu">
                    <div class="menu-item">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </div>
                    <div class="menu-item" onclick="logout()">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div id="mainContent" class="main-content">
        <div class="chat-container">
            <!-- Welcome Screen -->
            <div id="welcomeScreen" class="welcome-screen">
                <div class="welcome-content">
                    <div class="ai-logo">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h1>AI Travel Assistant</h1>
                    <p>Hello <?php echo htmlspecialchars($userName); ?>! I'm your intelligent travel planning assistant powered by advanced AI.</p>
                    <p class="capability-text">Ask me about destinations, hotels, tours, transportation, or any travel-related questions!</p>
                </div>
            </div>
            
            <!-- Messages Container -->
            <div id="messagesContainer" class="messages-container">
                <div id="messages" class="messages">
                    <!-- Messages will be populated by JavaScript -->
                </div>
            </div>
        </div>
        
        <!-- Input Area -->
        <div class="input-container">
            <div class="input-wrapper">
                <div class="input-box">
                    <textarea id="messageInput" class="message-input" placeholder="Ask me anything about travel..." rows="1"></textarea>
                    <button id="sendBtn" class="send-btn" disabled title="Send message">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
            <div class="input-footer">
                <span>AI Travel Assistant powered by Large Language Model. Responses are based on our travel database.</span>
            </div>
        </div>
    </div>

    <!-- Enhanced Loading Modal with Skeleton -->
    <div id="loadingModal" class="loading-modal">
        <div class="loading-content">
            <div class="skeleton-container">
                <div class="skeleton-avatar"></div>
                <div class="skeleton-text">
                    <div class="skeleton-line"></div>
                    <div class="skeleton-line"></div>
                    <div class="skeleton-line short"></div>
                </div>
            </div>
            <p class="loading-text">AI is thinking...</p>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toastNotification" class="toast-notification">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage">Message copied!</span>
    </div>

    <!-- Export Chat Modal -->
    <div id="exportModal" class="export-modal">
        <div class="export-content">
            <div class="export-header">
                <h3>Export Conversation</h3>
                <button class="close-modal-btn" onclick="closeExportModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="export-options">
                <button class="export-option-btn" onclick="exportChat('txt')">
                    <i class="fas fa-file-alt"></i>
                    <span>Export as Text</span>
                </button>
                <button class="export-option-btn" onclick="exportChat('json')">
                    <i class="fas fa-file-code"></i>
                    <span>Export as JSON</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Theme Toggle
        function initTheme() {
            const theme = localStorage.getItem('theme') || 'light';
            document.body.classList.toggle('dark-mode', theme === 'dark');
            updateThemeIcon(theme);
        }

        function updateThemeIcon(theme) {
            const icon = document.querySelector('#themeToggle i');
            const text = document.querySelector('#themeToggle span');
            if (theme === 'dark') {
                icon.className = 'fas fa-sun';
                text.textContent = 'Light Mode';
            } else {
                icon.className = 'fas fa-moon';
                text.textContent = 'Dark Mode';
            }
        }

        document.getElementById('themeToggle')?.addEventListener('click', function() {
            const isDark = document.body.classList.toggle('dark-mode');
            const theme = isDark ? 'dark' : 'light';
            localStorage.setItem('theme', theme);
            updateThemeIcon(theme);
        });

        // Mobile Menu Toggle
        document.getElementById('mobileMenuToggle')?.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
            this.classList.toggle('active');
        });

        // Chat Search Functionality
        const chatSearchInput = document.getElementById('chatSearchInput');
        const clearSearchBtn = document.getElementById('clearSearch');

        chatSearchInput?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            clearSearchBtn.style.display = searchTerm ? 'block' : 'none';
            filterChatHistory(searchTerm);
        });

        clearSearchBtn?.addEventListener('click', function() {
            chatSearchInput.value = '';
            this.style.display = 'none';
            filterChatHistory('');
        });

        function filterChatHistory(searchTerm) {
            const chatItems = document.querySelectorAll('.chat-history-item');
            chatItems.forEach(item => {
                const title = item.querySelector('.chat-title')?.textContent.toLowerCase() || '';
                item.style.display = title.includes(searchTerm) ? 'block' : 'none';
            });
        }

        // Toast Notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toastNotification');
            const toastMessage = document.getElementById('toastMessage');
            const icon = toast.querySelector('i');
            
            toastMessage.textContent = message;
            toast.className = `toast-notification ${type}`;
            
            if (type === 'success') {
                icon.className = 'fas fa-check-circle';
            } else if (type === 'error') {
                icon.className = 'fas fa-exclamation-circle';
            }
            
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        // Copy Message Function
        function copyMessage(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Message copied to clipboard!');
            }).catch(() => {
                showToast('Failed to copy message', 'error');
            });
        }

        // Export Chat Modal
        function openExportModal() {
            document.getElementById('exportModal').style.display = 'flex';
        }

        function closeExportModal() {
            document.getElementById('exportModal').style.display = 'none';
        }

        function exportChat(format) {
            const messages = document.querySelectorAll('.message');
            let content = '';
            
            if (format === 'txt') {
                messages.forEach(msg => {
                    const role = msg.classList.contains('user') ? 'You' : 'AI';
                    const text = msg.querySelector('.message-text')?.textContent || '';
                    content += `${role}: ${text}\n\n`;
                });
                downloadFile(content, 'chat-export.txt', 'text/plain');
            } else if (format === 'json') {
                const chatData = Array.from(messages).map(msg => ({
                    role: msg.classList.contains('user') ? 'user' : 'assistant',
                    content: msg.querySelector('.message-text')?.textContent || ''
                }));
                content = JSON.stringify(chatData, null, 2);
                downloadFile(content, 'chat-export.json', 'application/json');
            }
            
            closeExportModal();
            showToast('Chat exported successfully!');
        }

        function downloadFile(content, filename, mimeType) {
            const blob = new Blob([content], { type: mimeType });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        // Logout Function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../Login/login.php';
            }
        }
        
        function toggleUserMenu() {
            const userMenu = document.getElementById('userMenu');
            userMenu.classList.toggle('show');
        }
        
        document.addEventListener('click', function(event) {
            const userInfo = document.querySelector('.user-info');
            const userMenu = document.getElementById('userMenu');
            
            if (userInfo && !userInfo.contains(event.target)) {
                userMenu.classList.remove('show');
            }
        });

        // Initialize theme on load
        initTheme();
    </script>
    <script src="chatbot.js"></script>
</body>
</html>