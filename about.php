<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
    include_once 'header.php';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GloveUp - Boxing Training Center</title>
    <link rel="stylesheet" href="about.css" />
</head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<body>

    <header class="header">
        <h1>Boxing Training Center</h1>
        <p>Learn Boxing in Nepal</p>
    </header>

    <section class="about-section">
        <div class="about-image">
            <img src="/api/placeholder/300/400" alt="Boxer in gloves">
        </div>
        <div class="about-content">
            <h2>About us</h2>
            <h3>Welcome to GloveUp</h3>
            <p>"Welcome to GloveUp, where warriors of all levels step in, train hard, and rise stronger. Whether you're a beginner or a seasoned fighter, every punch brings you closer to greatness. Lace up, step in, and let’s fight for it!</p>
            <p>GloveUp was founded with a passion for boxing and a commitment to discipline, 
                respect, and growth. In a world where talent often goes unrecognized and
                 dedication is overlooked, we provide a space where every fighter is valued. 
                Whether you're a beginner or a rising champion, GloveUp is where you train, 
                fight, and earn respect—because here, your journey matters.</p>
        </div>
    </section>
    <?php
    include_once 'footer.php';
    ?>
</body>
</html>