<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Travel Planner - VietTransit</title>
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../AI/AI_planner.css">
</head>
<body>
    <?php include __DIR__ . '/../header.php'; ?>

    <main class="ai-container">
        <section class="ai-hero">
            <div class="hero-content">
                <div class="hero-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <h1 class="hero-title">
                    AI Travel Planner
                    <span class="title-accent">🧳✨</span>
                </h1>
                <p class="hero-description">
                    Enter your travel requirements in natural language. We will create a suitable plan for you.
                </p>
            </div>
        </section>

        <section class="ai-main">
            <div class="content-wrapper">
                <aside class="ai-sidebar">
                    <div class="sidebar-section">
                        <h3><i class="fas fa-history"></i> Recent Searches</h3>
                        <div class="search-history" id="searchHistory">
                            </div>
                    </div>

                    <div class="sidebar-section">
                        <h3><i class="fas fa-lightbulb"></i> Try These</h3>
                        <div class="suggestions">
                            <button class="suggestion-btn" data-query="I want to go to the beach for 3 days with a budget of 5 million VND, suggest Vung Tau">
                                Beach trip for 3 days - 5M VND
                            </button>
                            <button class="suggestion-btn" data-query="Couple trip to Phu Quoc for 2 days, romantic and luxurious, budget 15 million">
                                Romantic Phu Quoc - 2 days
                            </button>
                            <button class="suggestion-btn" data-query="Solo backpacking in Ha Giang for 5 days, budget-friendly around 4 million">
                                Solo Ha Giang - 5 days
                            </button>
                            <button class="suggestion-btn" data-query="Family trip to Hoi An, 3 days, cultural activities, budget 8 million">
                                Cultural Hoi An - 3 days
                            </button>
                        </div>
                    </div>
                </aside>

                <div class="ai-chat-area">
                    <div class="chat-messages" id="chatMessages">
                        <div class="welcome-message">
                            <div class="message-avatar">
                                <i class="fas fa-robot"></i>
                            </div>
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
                            </div>
                        </div>
                    </div>

                    <div class="chat-input-area">
                        <div class="input-wrapper">
                            <textarea
                                id="chatInput"
                                placeholder="Example: I have 10 million VND, want to go to Da Lat for 4 days with my family. Please suggest me."
                                rows="3"
                            ></textarea>
                            <button id="sendButton" class="send-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div class="input-actions">
                            <button class="action-btn" id="clearChat">
                                <i class="fas fa-trash"></i>
                                Clear Chat
                            </button>
                            <button class="action-btn" id="voiceInput" disabled>
                                <i class="fas fa-microphone"></i>
                                Voice Input
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="ai-results" id="resultsSection" style="display: none;">
            <div class="results-header">
                <h2><i class="fas fa-map-marked-alt"></i> Your Travel Plan</h2>
                <div class="results-actions">
                    <button class="export-btn" id="exportExcel">
                        <i class="fas fa-file-excel"></i>
                        Download Excel
                    </button>
                    <button class="export-btn" id="shareLink" disabled>
                        <i class="fas fa-share"></i>
                        Share Plan
                    </button>
                </div>
            </div>

            <div class="travel-plan-table">
                <table>
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Location</th>
                            <th>Activity</th>
                            <th>Estimated Cost (VND)</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody id="travelPlanTableBody">
                        </tbody>
                </table>
            </div>

            <div class="plan-summary">
                <div class="summary-card">
                    <h3><i class="fas fa-calculator"></i> Cost Breakdown</h3>
                    <div class="cost-details" id="costBreakdown">
                        </div>
                </div>

                <div class="summary-card">
                    <h3><i class="fas fa-info-circle"></i> Travel Tips</h3>
                    <div class="tips-content" id="travelTips">
                        </div>
                </div>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/../footer.php'; ?>

    <script src="../AI/ai-planner.js"></script>
</body>
</html>