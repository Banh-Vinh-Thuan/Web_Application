<?php
// traveltip.php - universal travel tip renderer

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get tip ID from query, fallback to "dalat" if missing
$tipId = $_GET['tip'] ?? 'dalat';

// Load JSON data
$rawData = file_get_contents(__DIR__ . '/traveltips.json');
$tips = json_decode($rawData, true);

// Validate tip ID
if (!isset($tips[$tipId])) {
    echo "<h2>Travel tip not found.</h2>";
    exit;
}

$tip = $tips[$tipId];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tip['title']) ?></title>
    <link rel="stylesheet" href="../css/traveltips.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <a href="#" class="logo">
        <img src="/images/logo.png" alt="VietTransit Logo">
        <span>VietTransit</span>
    </a>
    <nav class="navbar">
        <a href="/Login/loggedinhome.php">Home</a>
        <a href="../Login/profile.php">Hello, <?= htmlspecialchars($_SESSION['usersuid'] ?? 'Guest') ?>!</a>
        <a href="../home.php">Logout</a>
    </nav>
    <div class="icons">
        <a href="/Login/profile.php" class="fas fa-heart"></a>
        <a href="/Login/profile.php" class="fas fa-shopping-cart"></a>
        <a href="/Login/profile.php" class="fas fa-user"></a>
    </div>
</header>

<!-- Hero Section -->
<div class="hero-section">
    <div class="hero-content">
        <div class="hero-text">
            <h1 class="hero-title"><?= htmlspecialchars($tip['title']) ?></h1>
            <p class="hero-subtitle">Your complete travel guide</p>
        </div>
        <div class="hero-icon">
            <i class="fas fa-map-marked-alt"></i>
        </div>
    </div>
    <div class="hero-wave">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25" fill="url(#gradient)"></path>
            <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5" fill="url(#gradient)"></path>
            <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" fill="url(#gradient)"></path>
            <defs>
                <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" style="stop-color:#1e3a8a;stop-opacity:1" />
                    <stop offset="100%" style="stop-color:#3b82f6;stop-opacity:1" />
                </linearGradient>
            </defs>
        </svg>
    </div>
</div>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <!-- Progress Bar -->
        <div class="progress-container">
            <div class="progress-bar" id="progressBar"></div>
        </div>

        <!-- Content Sections -->
        <div class="content-wrapper">
            <?php foreach ($tip['sections'] as $index => $section): ?>
                <div class="section-card" data-section="<?= $index ?>">
                    <div class="section-header">
                        <div class="section-number"><?= sprintf('%02d', $index + 1) ?></div>
                        <h2 class="section-title"><?= htmlspecialchars($section['title']) ?></h2>
                    </div>
                    
                    <div class="section-content">
                        <?php foreach ($section['content'] as $para): ?>
                            <?php if (is_string($para)): ?>
                                <p class="content-paragraph"><?= htmlspecialchars($para) ?></p>

                            <?php elseif (is_array($para) && isset($para['list_title'], $para['list']) && is_array($para['list'])): ?>
                                <div class="list-container">
                                    <h4 class="list-title"><?= htmlspecialchars($para['list_title']) ?></h4>
                                    <ul class="styled-list">
                                        <?php foreach ($para['list'] as $item): ?>
                                            <li class="list-item">
                                                <i class="fas fa-check-circle"></i>
                                                <span><?= htmlspecialchars($item) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>

                            <?php elseif (is_array($para)): ?>
                                <ul class="styled-list">
                                    <?php foreach ($para as $item): ?>
                                        <?php if (is_scalar($item)): ?>
                                            <li class="list-item">
                                                <i class="fas fa-check-circle"></i>
                                                <span><?= htmlspecialchars($item) ?></span>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Back to Top Button -->
        <button id="backToTop" class="back-to-top" title="Back to top">
            <i class="fas fa-chevron-up"></i>
        </button>
    </div>
</main>

<?php include __DIR__ . '/../footer.php'; ?>

<script>
// Progress bar functionality
window.addEventListener('scroll', () => {
    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const scrolled = (winScroll / height) * 100;
    document.getElementById('progressBar').style.width = scrolled + '%';
});

// Back to top button
const backToTopBtn = document.getElementById('backToTop');
window.addEventListener('scroll', () => {
    if (window.scrollY > 300) {
        backToTopBtn.classList.add('show');
    } else {
        backToTopBtn.classList.remove('show');
    }
});

backToTopBtn.addEventListener('click', () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

// Section animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
        }
    });
}, observerOptions);

document.querySelectorAll('.section-card').forEach(section => {
    observer.observe(section);
});
</script>
</body>
</html>