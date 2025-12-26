<?php
session_start();

// Database connection (adjust with your credentials)
include '../dbconnect.php';

// Query for user distribution (Admins and Regular Users)
$admin_count = $conn->query("SELECT COUNT(*) as count FROM admin_login")->fetch_assoc()['count'];
$user_count = $conn->query("SELECT COUNT(*) as count FROM login")->fetch_assoc()['count'];

// Query for booking distribution
$hotel_bookings = $conn->query("SELECT COUNT(*) as count FROM hotel_bookings")->fetch_assoc()['count'];
$tour_bookings = $conn->query("SELECT COUNT(*) as count FROM tour_bookings")->fetch_assoc()['count'];

// Additional queries for better analytics
$total_bookings = $hotel_bookings + $tour_bookings;
$total_users = $admin_count + $user_count;

// Get recent bookings (last 10 bookings) - using booking_id instead of id
$latest_hotel_id = $conn->query("SELECT MAX(booking_id) as max_id FROM hotel_bookings")->fetch_assoc()['max_id'] ?? 0;
$latest_tour_id = $conn->query("SELECT MAX(booking_id) as max_id FROM tour_bookings")->fetch_assoc()['max_id'] ?? 0;

// Get latest 10 bookings for each type
$recent_hotel_threshold = max(1, $latest_hotel_id - 10);
$recent_tour_threshold = max(1, $latest_tour_id - 10);

$recent_hotel = $conn->query("SELECT COUNT(*) as count FROM hotel_bookings WHERE booking_id >= $recent_hotel_threshold")->fetch_assoc()['count'] ?? 0;
$recent_tour = $conn->query("SELECT COUNT(*) as count FROM tour_bookings WHERE booking_id >= $recent_tour_threshold")->fetch_assoc()['count'] ?? 0;

// Get booking status distribution for hotel bookings based on payment_status
$hotel_confirmed = $conn->query("SELECT COUNT(*) as count FROM hotel_bookings WHERE payment_status = 'completed' OR payment_status = 'success'")->fetch_assoc()['count'] ?? 0;
$hotel_pending = $conn->query("SELECT COUNT(*) as count FROM hotel_bookings WHERE payment_status = 'pending'")->fetch_assoc()['count'] ?? 0;
$hotel_cancelled = $conn->query("SELECT COUNT(*) as count FROM hotel_bookings WHERE payment_status = 'cancelled' OR payment_status = 'failed'")->fetch_assoc()['count'] ?? 0;

// Get booking status distribution for tour bookings based on payment_status
$tour_confirmed = $conn->query("SELECT COUNT(*) as count FROM tour_bookings WHERE payment_status = 'completed' OR payment_status = 'success'")->fetch_assoc()['count'] ?? 0;
$tour_pending = $conn->query("SELECT COUNT(*) as count FROM tour_bookings WHERE payment_status = 'pending'")->fetch_assoc()['count'] ?? 0;
$tour_cancelled = $conn->query("SELECT COUNT(*) as count FROM tour_bookings WHERE payment_status = 'cancelled' OR payment_status = 'failed'")->fetch_assoc()['count'] ?? 0;

// Calculate efficiency metrics
$total_confirmed = $hotel_confirmed + $tour_confirmed;
$total_pending = $hotel_pending + $tour_pending;
$total_cancelled = $hotel_cancelled + $tour_cancelled;

$confirmation_rate = $total_bookings > 0 ? round(($total_confirmed / $total_bookings) * 100, 1) : 0;
$cancellation_rate = $total_bookings > 0 ? round(($total_cancelled / $total_bookings) * 100, 1) : 0;

// Recent activity rate (percentage of recent bookings)
$growth_rate = $total_bookings > 0 ? round((($recent_hotel + $recent_tour) / $total_bookings) * 100, 1) : 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/admindashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <title>Admin Dashboard - Travel Management System</title>
</head>
<body>
    <!-- Enhanced Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-plane-departure"></i>
                <h1>Travel Command Center</h1>
            </div>
            <nav class="navigation">
                <ul class="nav-menu">
                    <li><a href="adminviewcity.php"><i class="fas fa-city"></i> Cities</a></li>
                    <li><a href="adminviewusers.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="adminusers.php"><i class="fas fa-user-shield"></i> Admins</a></li>
                    <li><a href="adminlogout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <!-- Enhanced Sidebar -->
        <aside class="sidebar">
            <div class="admin-profile">
                <div class="avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h3>Welcome Back</h3>
                <p>
                    <?php 
                    if (isset($_SESSION['AdminLoginId'])) {
                        echo htmlspecialchars($_SESSION['AdminLoginId']);
                    } else {
                        echo 'Guest';
                    }
                    ?>
                </p>
            </div>
            
            <nav class="sidebar-nav">
                <ul class="sidebar-menu">
                    <li class="active">
                        <a href="#dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard Overview</a>
                    </li>
                    <li>
                        <a href="adminviewtourbooking.php"><i class="fas fa-map-marked-alt"></i> Tour Bookings</a>
                    </li>
                    <li>
                        <a href="adminviewhotelbooking.php"><i class="fas fa-hotel"></i> Hotel Bookings</a>
                    </li>
                    <li>
                        <a href="adminviewtour.php"><i class="fas fa-route"></i> Tour Management</a>
                    </li>
                    <li>
                        <a href="adminviewhotel.php"><i class="fas fa-building"></i> Hotel Management</a>
                    </li>
                    <li>
                        <a href="adminviewusers.php"><i class="fas fa-users-cog"></i> User Management</a>
                    </li>
                    <li>
                        <a href="adminviewcity.php"><i class="fas fa-city"></i> City Management</a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="adminlogout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </aside>

        <!-- Enhanced Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h2><i class="fas fa-chart-line"></i> Executive Dashboard</h2>
                <p>Real-time insights and comprehensive analytics for your travel management platform</p>
            </div>

            <!-- Enhanced Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card loading">
                    <div class="stat-icon hotel">
                        <i class="fas fa-hotel"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($hotel_bookings); ?></h3>
                        <p>Hotel Reservations</p>
                        <span class="stat-change positive">
                            <i class="fas fa-arrow-up"></i> +<?php echo $recent_hotel; ?> this week
                        </span>
                    </div>
                </div>
                
                <div class="stat-card loading">
                    <div class="stat-icon tour">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($tour_bookings); ?></h3>
                        <p>Tour Adventures</p>
                        <span class="stat-change positive">
                            <i class="fas fa-arrow-up"></i> +<?php echo $recent_tour; ?> this week
                        </span>
                    </div>
                </div>
                
                <div class="stat-card loading">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($total_users); ?></h3>
                        <p>Active Community</p>
                        <span class="stat-change neutral">
                            <i class="fas fa-user"></i> <?php echo number_format($user_count); ?> travelers
                        </span>
                    </div>
                </div>
                
                <div class="stat-card loading">
                    <div class="stat-icon revenue">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $confirmation_rate; ?>%</h3>
                        <p>Success Rate</p>
                        <span class="stat-change <?php echo $confirmation_rate >= 80 ? 'positive' : ($confirmation_rate >= 60 ? 'neutral' : 'negative'); ?>">
                            <i class="fas fa-chart-line"></i> Performance metric
                        </span>
                    </div>
                </div>
            </div>

            <!-- Enhanced Charts Section -->
            <div class="charts-grid">
                <div class="chart-container loading">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-pie"></i> Payment Status Analytics</h3>
                        <p>Real-time payment processing status across all bookings</p>
                    </div>
                    <canvas id="bookingStatusChart"></canvas>
                    <div class="chart-summary">
                        <div class="summary-item">
                            <span class="summary-dot confirmed"></span>
                            <span>Confirmed: <?php echo $total_confirmed; ?> bookings</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-dot pending"></span>
                            <span>Pending: <?php echo $total_pending; ?> bookings</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-dot cancelled"></span>
                            <span>Cancelled: <?php echo $total_cancelled; ?> bookings</span>
                        </div>
                    </div>
                </div>

                <div class="chart-container loading">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-bar"></i> Service Performance Metrics</h3>
                        <p>Comparative analysis of hotel vs tour booking performance</p>
                    </div>
                    <canvas id="serviceComparisonChart"></canvas>
                    <div class="performance-indicators">
                        <div class="indicator">
                            <i class="fas fa-hotel"></i>
                            <span>Hotels: <?php echo round(($hotel_bookings / max($total_bookings, 1)) * 100, 1); ?>% market share</span>
                        </div>
                        <div class="indicator">
                            <i class="fas fa-map-marked-alt"></i>
                            <span>Tours: <?php echo round(($tour_bookings / max($total_bookings, 1)) * 100, 1); ?>% market share</span>
                        </div>
                    </div>
                </div>

                <div class="chart-container loading">
                    <div class="chart-header">
                        <h3><i class="fas fa-users-cog"></i> User Base Distribution</h3>
                        <p>Administrative vs customer user ratio analysis</p>
                    </div>
                    <canvas id="userDistributionChart"></canvas>
                    <div class="user-metrics">
                        <div class="metric">
                            <span class="metric-number"><?php echo number_format($user_count); ?></span>
                            <span class="metric-label">Customers</span>
                        </div>
                        <div class="metric">
                            <span class="metric-number"><?php echo number_format($admin_count); ?></span>
                            <span class="metric-label">Administrators</span>
                        </div>
                    </div>
                </div>

                <div class="chart-container recent-activity loading">
                    <div class="chart-header">
                        <h3><i class="fas fa-clock"></i> Executive Summary</h3>
                        <p>Key performance indicators and operational metrics</p>
                    </div>
                    <div class="activity-stats">
                        <div class="activity-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Successfully Confirmed</span>
                            <div class="activity-number"><?php echo number_format($total_confirmed); ?></div>
                            <div class="activity-subtitle"><?php echo $confirmation_rate; ?>% success rate</div>
                        </div>
                        <div class="activity-item">
                            <i class="fas fa-hourglass-half"></i>
                            <span>Awaiting Processing</span>
                            <div class="activity-number"><?php echo number_format($total_pending); ?></div>
                            <div class="activity-subtitle">Requires attention</div>
                        </div>
                        <div class="activity-item">
                            <i class="fas fa-times-circle"></i>
                            <span>Cancelled Orders</span>
                            <div class="activity-number"><?php echo number_format($total_cancelled); ?></div>
                            <div class="activity-subtitle"><?php echo $cancellation_rate; ?>% cancellation rate</div>
                        </div>
                        <div class="activity-item">
                            <i class="fas fa-chart-line"></i>
                            <span>Growth Indicator</span>
                            <div class="activity-number"><?php echo $growth_rate; ?>%</div>
                            <div class="activity-subtitle">Recent activity trend</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Travel Management System | Professional Edition | Designed for Excellence</p>
    </footer>

    <script>
        // Enhanced Chart.js Configuration with Professional Styling
        Chart.defaults.font.family = 'Inter, sans-serif';
        Chart.defaults.font.size = 14;
        Chart.defaults.color = '#64748b';

        // Professional Color Palette
        const colors = {
            primary: '#2563eb',
            success: '#10b981',
            warning: '#f59e0b',
            danger: '#ef4444',
            info: '#06b6d4',
            gradient: {
                blue: ['#3b82f6', '#1e40af'],
                green: ['#10b981', '#059669'],
                orange: ['#f59e0b', '#d97706'],
                red: ['#ef4444', '#dc2626']
            }
        };

        // Payment Status Overview Chart (Enhanced Doughnut)
        const statusCtx = document.getElementById('bookingStatusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Confirmed', 'Pending', 'Cancelled'],
                datasets: [{
                    data: [<?php echo $total_confirmed; ?>, <?php echo $total_pending; ?>, <?php echo $total_cancelled; ?>],
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 3,
                    hoverBorderWidth: 5,
                    hoverBackgroundColor: [
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(239, 68, 68, 1)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 25,
                            usePointStyle: true,
                            font: {
                                size: 14,
                                weight: '600'
                            },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const meta = chart.getDatasetMeta(0);
                                        const style = meta.controller.getStyle(i);
                                        const value = data.datasets[0].data[i];
                                        const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        
                                        return {
                                            text: `${label}: ${value} (${percentage}%)`,
                                            fillStyle: style.backgroundColor,
                                            strokeStyle: style.borderColor,
                                            lineWidth: style.borderWidth,
                                            pointStyle: 'circle',
                                            hidden: isNaN(data.datasets[0].data[i]) || meta.data[i].hidden,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.9)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#2563eb',
                        borderWidth: 2,
                        cornerRadius: 12,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return `${context.label}: ${context.parsed} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    duration: 2500,
                    easing: 'easeOutQuart'
                }
            }
        });

        // Service Performance Comparison Chart (Enhanced Bar)
        const serviceCtx = document.getElementById('serviceComparisonChart').getContext('2d');
        new Chart(serviceCtx, {
            type: 'bar',
            data: {
                labels: ['Hotel Services', 'Tour Experiences'],
                datasets: [
                    {
                        label: 'Confirmed',
                        data: [<?php echo $hotel_confirmed; ?>, <?php echo $tour_confirmed; ?>],
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: '#10b981',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    },
                    {
                        label: 'Pending',
                        data: [<?php echo $hotel_pending; ?>, <?php echo $tour_pending; ?>],
                        backgroundColor: 'rgba(245, 158, 11, 0.8)',
                        borderColor: '#f59e0b',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    },
                    {
                        label: 'Cancelled',
                        data: [<?php echo $hotel_cancelled; ?>, <?php echo $tour_cancelled; ?>],
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderColor: '#ef4444',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 14,
                                weight: '600'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.9)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#2563eb',
                        borderWidth: 2,
                        cornerRadius: 12,
                        titleFont: {
                            size: 16,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 14
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(37, 99, 235, 0.1)',
                            lineWidth: 1
                        },
                        ticks: {
                            font: {
                                weight: '500',
                                size: 13
                            },
                            color: '#64748b'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                weight: '600',
                                size: 13
                            },
                            color: '#334155'
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });

        // User Distribution Chart (Enhanced Doughnut)
        const userCtx = document.getElementById('userDistributionChart').getContext('2d');
        new Chart(userCtx, {
            type: 'doughnut',
            data: {
                labels: ['Customers', 'Administrators'],
                datasets: [{
                    data: [<?php echo $user_count; ?>, <?php echo $admin_count; ?>],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(29, 78, 216, 0.8)'
                    ],
                    borderColor: ['#3b82f6', '#1d4ed8'],
                    borderWidth: 3,
                    hoverBorderWidth: 5,
                    hoverBackgroundColor: [
                        'rgba(59, 130, 246, 1)',
                        'rgba(29, 78, 216, 1)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 25,
                            usePointStyle: true,
                            font: {
                                size: 14,
                                weight: '600'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.9)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#2563eb',
                        borderWidth: 2,
                        cornerRadius: 12,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return `${context.label}: ${context.parsed} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    duration: 2500,
                    easing: 'easeOutQuart'
                }
            }
        });

        // Enhanced Loading Animation and Effects
        document.addEventListener('DOMContentLoaded', function() {
            // Staggered loading animation
            const elements = document.querySelectorAll('.loading');
            elements.forEach((element, index) => {
                setTimeout(() => {
                    element.style.opacity = '0';
                    element.style.transform = 'translateY(30px)';
                    element.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
                    
                    setTimeout(() => {
                        element.style.opacity = '1';
                        element.style.transform = 'translateY(0)';
                        element.classList.remove('loading');
                    }, 100);
                }, index * 150);
            });

            // Enhanced hover effects
            document.querySelectorAll('.stat-card, .chart-container').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });

            // Real-time clock for admin
            function updateClock() {
                const now = new Date();
                const timeString = now.toLocaleTimeString();
                const dateString = now.toLocaleDateString();
                
                // You can add a clock element to display current time
                console.log(`Dashboard loaded at: ${timeString} on ${dateString}`);
            }
            
            updateClock();
            setInterval(updateClock, 1000);
        });

        // Enhanced number formatting animation
        function animateNumbers() {
            const numbers = document.querySelectorAll('.stat-info h3, .activity-number');
            numbers.forEach(num => {
                const finalValue = parseInt(num.textContent.replace(/,/g, ''));
                let currentValue = 0;
                const increment = finalValue / 50;
                const timer = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= finalValue) {
                        currentValue = finalValue;
                        clearInterval(timer);
                    }
                    num.textContent = Math.floor(currentValue).toLocaleString();
                }, 50);
            });
        }

        // Trigger number animation after page load
        setTimeout(() => {
            animateNumbers();
        }, 1000);
    </script>
</body>
</html>