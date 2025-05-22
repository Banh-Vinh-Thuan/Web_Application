<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../css/admindashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <title>Admin Panel</title>
</head>
<body>
    <header class="header">
        <div class="logo">
            <h1>Admin Panel</h1>
        </div>
        <nav class="navigation">
            <ul class="nav-menu">
                <li><a href="adminviewcity.php">View Cities</a></li>
                <li><a href="adminviewusers.php">View Users</a></li>
                <li><a href="adminusers.php">View Admins</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <img src="silhouette.png" alt="Silhouette Image"><br><br>
            </div>
            <h2 style="color: cornsilk;">Welcome, <?php echo $_SESSION['AdminLoginId'];?></h2>
            <ul class="sidebar-menu">
                <li><a href="#"><i class="fa fa-home"></i> Dashboard</a></li>
                <li><a href="adminviewtourbooking.php"><i class="fa fa-pencil"></i> Tour Booking Management</a></li>
                <li><a href="adminviewhotelbooking.php"><i class="fa fa-pencil"></i> Hotel Booking Management</a></li>
                <li><a href="adminviewtour.php"><i class="fa fa-edit"></i> Tour Management</a></li>
                <li><a href="adminviewhotel.php"><i class="fa fa-users"></i> Hotel Management</a></li>
                <li><a href="adminviewusers.php"><i class="fa fa-comments"></i> User Management</a></li>
                <li><a href="adminviewcity.php"><i class="fa fa-pencil"></i> City Management</a></li>
            </ul>
            <div class="sidebar-footer" style="color: #333;">
                <p style="color:cornsilk;">Logged in as Admin</p>
                <a href="adminlogout.php" style="color: cornsilk;"><i class="fa fa-sign-out"></i> Logout</a>
            </div>
        </div>

        <div class="content">
            <div class="dashboard-overview">
                <h2>Dashboard Overview</h2>
                <p>Welcome to Admin Dashboard. Here, you can manage journeys, users, and settings of the website.</p>
            </div>

            <br>
            <div class="dashboard-container">
                <div class="dashboard-widget pie-chart-container">
                    <h2>User Distribution</h2>
                    <canvas id="userPieChart" class="pie-chart" width="400" height="400"></canvas>
                </div>

                <div class="dashboard-widget userGrowth-chart">
                    <h2>User Growth</h2>
                    <canvas id="userLineChart" width="400" height="400"></canvas>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Admin Panel</p>
    </footer>

    <script>
        var userPieData = {
            labels: ['Admins', 'Regular Users', 'Guests'],
            datasets: [{
                data: [30, 60, 10],
                backgroundColor: ['#36a2eb', '#ffcd56', '#ff6384']
            }]
        };

        var userLineData = {
            labels: ['2019', '2020', '2021', '2022', '2023','2024','2025'],
            datasets: [{
                label: 'Number of Users',
                data: [200, 400, 600, 800, 1000, 1200, 1500],
                borderColor: '#ff6384',
                fill: false
            }]
        };

        var userPieChart = new Chart(document.getElementById('userPieChart'), {
            type: 'pie',
            data: userPieData
        });

        var userLineChart = new Chart(document.getElementById('userLineChart'), {
            type: 'line',
            data: userLineData
        });
    </script>
</body>
</html>
