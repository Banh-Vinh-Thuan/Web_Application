<?php
    $BASE_URL = "/Login/"; // hoặc "/your-subdirectory/" nếu web đặt trong thư mục con
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
        <a href="#about">About</a>
        <a href="#products">Places</a>
        <a href="#review">Review</a>
        <a href="../booking/find_tour.php">Tour</a>
        <a href="../booking/find_hotels.php">Hotel</a>
        <?php
            echo "<a href='profile.php'>Hello, " . $_SESSION['usersuid'] . "!</a>";
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