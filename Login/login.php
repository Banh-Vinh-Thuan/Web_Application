<?php
    session_start();
?>

<?php
        if (isset($_GET["error"])) {
            if ($_GET["error"] == "emptyinput"){
                echo "<div class='error-message'><i class='fas fa-exclamation-circle'></i> Fill in all fields!</div>";
            }
            else if($_GET["error"] == "invaliduid"){
                echo "<div class='error-message'><i class='fas fa-exclamation-circle'></i> Choose a proper username</div>";
            }
            else if($_GET["error"] == "invaliduemail"){
                echo "<div class='error-message'><i class='fas fa-exclamation-circle'></i> Choose a proper email</div>";
            }
            else if($_GET["error"] == "passwordsdontmatch"){
                echo "<div class='error-message'><i class='fas fa-exclamation-circle'></i> Passwords don't match!</div>";
            }
            else if($_GET["error"] == "stmtfailed"){
                echo "<div class='error-message'><i class='fas fa-exclamation-circle'></i> Something went wrong! Try again!</div>";
            }
            else if($_GET["error"] == "usernametaken"){
                echo "<div class='error-message'><i class='fas fa-exclamation-circle'></i> Try another username</div>";
            }
            else if ($_GET["error"] == "none"){
                echo "<div class='success-message'><i class='fas fa-check-circle'></i> Successfully Signed Up!</div>";
            }
        }
        ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Professional Login Portal</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.2/css/bootstrap.min.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css'>
    <link rel="stylesheet" href="../css/login.css" />
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Background Elements -->
    <div class="background-decoration">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
        <div class="floating-shape shape-4"></div>
    </div>

    <!-- Ocean Animation -->
    <div class="ocean">
        <div class="wave"></div>
        <div class="wave"></div>
    </div>

    <section class="auth-section">
        <div class="auth-container" id="container">
            <!-- Sign Up Form -->
            <div class="form-container sign-up-container">
                <form action="signup-inc.php" method="POST" class="auth-form">
                    <div class="form-header">
                        <h2><i class="fas fa-user-plus"></i> Create Account</h2>
                        <p>Join us today and start your journey</p>
                    </div>
                    
                    <div class="social-container">
                        <a href="https://Github.com/AtharvaKulkarniIT" target="_blank" class="social-btn" title="Visit GitHub">
                            <i class="fab fa-github"></i>
                        </a>
                    </div>
                    
                    <div class="divider">
                        <span>Or register with email</span>
                    </div>

                    <div class="input-group">
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" placeholder="Email Address" required />
                        </div>
                    </div>

                    <div class="input-group">
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="uid" placeholder="Username" required />
                        </div>
                    </div>

                    <div class="input-group">
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="pwd" placeholder="Password" required />
                        </div>
                    </div>

                    <div class="input-group">
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="pwdconfirm" placeholder="Confirm Password" required />
                        </div>
                    </div>

                    <button type="submit" name="submit" class="auth-btn primary-btn">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>
            </div>

            <!-- Sign In Form -->
            <div class="form-container sign-in-container">
                <form action="login-inc.php" method="POST" class="auth-form">
                    <div class="form-header">
                        <h2><i class="fas fa-sign-in-alt"></i> Welcome Back</h2>
                        <p>Sign in to access your account</p>
                    </div>
                    
                    <div class="social-container">
                        <a href="https://github.com/socolate12345/Travel-Booking-Website" target="_blank" class="social-btn" title="Visit GitHub">
                            <i class="fab fa-github"></i>
                        </a>
                    </div>
                    
                    <div class="divider">
                        <span>Or sign in with credentials</span>
                    </div>

                    <div class="input-group">
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="uid" placeholder="Username or Email" required />
                        </div>
                    </div>

                    <div class="input-group">
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="pwd" placeholder="Password" required />
                        </div>
                    </div>

                    <div class="form-options">
                        <a href="#" class="forgot-link">
                            <i class="fas fa-key"></i> Forgot your password?
                        </a>
                    </div>

                    <button type="submit" name="login" class="auth-btn primary-btn">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>
            </div>

            <!-- Overlay Panels -->
            <div class="overlay-container">
                <div class="overlay">
                    <div class="overlay-panel overlay-left">
                        <div class="overlay-content">
                            <h2><i class="fas fa-arrow-left"></i> Already a Member?</h2>
                            <p>Sign in to access your personal dashboard and continue your journey with us.</p>
                            <button class="auth-btn ghost-btn" id="signIn">
                                <i class="fas fa-sign-in-alt"></i> Sign In
                            </button>
                        </div>
                    </div>
                    <div class="overlay-panel overlay-right">
                        <div class="overlay-content">
                            <h2><i class="fas fa-rocket"></i> New Here?</h2>
                            <p>Create an account and discover all the amazing features we have to offer!</p>
                            <button class="auth-btn ghost-btn" id="signUp">
                                <i class="fas fa-user-plus"></i> Sign Up
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/js/bootstrap.min.js'></script>
    <script src="../js/login.js"></script>
</body>
</html>