<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$BASE_URL = "/Login/";
?>


<header>
    <input type="checkbox" name="" id="toggler">
    <label for="toggler" class="fas fa-bars"></label>

    <a href="#" class="logo">
    <img src="/images/logo.png" alt="VietTransit Logo">
    <span>VietTransit</span>
    </a>
    
    <nav class="navbar">
        <a href="../login/loggedinhome.php">Home</a>
        <a href="../login/loggedinhome.php#about">About</a>
        <a href="../login/loggedinhome.php#products">Places</a>
        <a href="../login/loggedinhome.php#review">Review</a>
        <a href="../booking/find_tour.php">Tour</a>
        <a href="../booking/find_hotels.php">Hotel</a>
        <a href="../Flight/flight.php">Flight</a>
        <a href="../AI/AI_planner.php">AI Planner</a>
        <?php
            echo "<a href='../Login/profile.php'>Hello, " . $_SESSION['usersuid'] . "!</a>";
            echo '<a href="../home.php">Logout</a>';
        ?>
    </nav>

    <div class="icons">
        <span data-tooltip="Favourites" data-flow="top"> 
            <a href="<?php echo $BASE_URL; ?>profile.php"><i class="fas fa-heart"></i></a>
        </span>
        <span data-tooltip="Profile" data-flow="top">
            <a href="<?php echo $BASE_URL; ?>profile.php"><i class="fas fa-user"></i></a>
        </span>
    </div>
</header>