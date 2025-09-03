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
            <div class="user-info" onclick="toggleUserMenu()">
                <div class="user-avatar">
                    <?php 
                    // Get user info from session with fallbacks
                    $userName = isset($_SESSION['username']) ? $_SESSION['username'] : 
                               (isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 
                               (isset($_SESSION['name']) ? $_SESSION['name'] : 'Guest'));
                    $userEmail = isset($_SESSION['email']) ? $_SESSION['email'] : 'Free';
                    
                    // Display first letter of username
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

    <!-- Loading Modal -->
    <div id="loadingModal" class="loading-modal">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <p>AI is thinking...</p>
        </div>
    </div>

    <script>
        // Add logout function
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
    </script>
    <script src="ai-planner.js"></script>
</body>
</html>