<?php
include '../dbconnect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - VietTransit</title>
    <link rel="stylesheet" type="text/css" href="../css/adminlogin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Background decoration -->
    <div class="bg-decoration">
        <div class="circle circle-1"></div>
        <div class="circle circle-2"></div>
        <div class="circle circle-3"></div>
        <div class="wave-bg"></div>
    </div>

    <!-- Back button -->
    <a href="../home.php" class="back-button">
        <i class="bi bi-arrow-left"></i>
        <span>Back to Home</span>
    </a>

    <!-- Main login container -->
    <div class="login-container">
        <div class="login-form">
            <!-- Header section -->
            <div class="form-header">
                <div class="admin-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h2>Admin Portal</h2>
                <p>Secure access to administration panel</p>
            </div>

            <!-- Login form -->
            <form method="POST" class="login-form-content">
                <?php if(isset($error_message)): ?>
                    <div class="error-message">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span><?php echo $error_message; ?></span>
                    </div>
                <?php endif; ?>

                <div class="input-group">
                    <div class="input-field">
                        <i class="bi bi-person-circle"></i>
                        <input type="text" placeholder="Admin Username" name="AdminName" required>
                        <div class="input-border"></div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="input-field">
                        <i class="bi bi-shield-lock"></i>
                        <input type="password" placeholder="Password" name="AdminPassword" required>
                        <div class="input-border"></div>
                    </div>
                </div>

                <button type="submit" name="Signin" class="signin-btn">
                    <span>Sign In</span>
                    <i class="bi bi-arrow-right"></i>
                </button>

                <div class="form-footer">
                    <a href="#" class="link forgot-link">
                        <i class="bi bi-question-circle"></i>
                        Forgot Password?
                    </a>
                    <a href="#" class="link create-link">
                        <i class="bi bi-person-plus"></i>
                        Create Account
                    </a>
                </div>
            </form>
        </div>

        <!-- Security notice -->
        <div class="security-notice">
            <i class="bi bi-shield-lock-fill"></i>
            <p>This is a secure admin area. Unauthorized access is prohibited.</p>
        </div>
    </div>

    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Authenticating...</p>
        </div>
    </div>

    <script>
        // Form submission with loading effect
        document.querySelector('form').addEventListener('submit', function(e) {
            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.classList.add('show');
        });

        // Input field focus effects
        document.querySelectorAll('.input-field input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentElement.classList.remove('focused');
                }
            });
        });

        // Floating animation for background circles
        function animateCircles() {
            const circles = document.querySelectorAll('.circle');
            circles.forEach((circle, index) => {
                const duration = 3000 + (index * 1000);
                circle.style.animationDuration = duration + 'ms';
            });
        }
        animateCircles();
    </script>
</body>
</html>

<?php
if(isset($_POST['Signin'])){
    $adminName = mysqli_real_escape_string($conn, $_POST['AdminName']);
    $adminPassword = mysqli_real_escape_string($conn, $_POST['AdminPassword']);
    
    $query = "SELECT * FROM `admin_login` WHERE `Admin_Name` = '$adminName' AND `Admin_Password` = '$adminPassword'";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) == 1){
        session_start();
        $_SESSION['AdminLoginId'] = $_POST['AdminName'];
        echo "<script>
            document.getElementById('loadingOverlay').classList.add('show');
            setTimeout(function() {
                window.location.href = 'admindashboard.php';
            }, 1500);
        </script>";
    } else {
        $error_message = "Invalid username or password. Please try again.";
        echo "<script>
            document.getElementById('loadingOverlay').classList.remove('show');
            document.querySelector('.login-form').classList.add('shake');
            setTimeout(function() {
                document.querySelector('.login-form').classList.remove('shake');
            }, 600);
        </script>";
    }
}
?>