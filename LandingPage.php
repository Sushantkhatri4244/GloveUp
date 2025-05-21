<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GloveUp - Boxing Training Center</title>
    <link rel="stylesheet" href="LandingPage.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Auth buttons styles */
        .auth-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .auth-button, .login-btn, .signup-btn, .logout-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .login-btn, .signup-btn {
            background-color: #ff9900;
            color: white;
        }
        
        .logout-btn {
            background-color: orange;
            color: white;
        }
        
        .user-name {
            font-weight: 500;
            margin-right: 10px;
        }
    </style>
</head>
<body>

    <!-- Success message display -->
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            unset($_SESSION['message_type']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">&times;</button>
    </div>
    <?php endif; ?>

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
            <p>"Welcome to GloveUp, where warriors of all levels step in, train hard, and rise stronger. Whether you're a beginner or a seasoned fighter, every punch brings you closer to greatness. Lace up, step in, and let's fight for it!</p>
            <p>GloveUp was founded with a passion for boxing and a commitment to discipline, 
                respect, and growth. In a world where talent often goes unrecognized and
                 dedication is overlooked, we provide a space where every fighter is valued. 
                Whether you're a beginner or a rising champion, GloveUp is where you train, 
                fight, and earn respect—because here, your journey matters.</p>
        </div>
    </section>

    <section class="schedule-section">
        
        <div class="day-tabs">
            <button class="day-tab active" data-day="sunday">Sunday</button>
            <button class="day-tab" data-day="monday">Monday</button>
            <button class="day-tab" data-day="tuesday">Tuesday</button>
            <button class="day-tab" data-day="wednesday">Wednesday</button>
            <button class="day-tab" data-day="thursday">Thursday</button>
            <button class="day-tab" data-day="friday">Friday</button>
        </div>
        
        <div class="class-schedule sunday">
            <div class="schedule-card">
                <div class="schedule-time">6:00AM - 7:00AM</div>
                <div class="schedule-class">BOXING</div>
                <div class="schedule-trainer">SUSHANT KHATRI</div>
            </div>
            
            <div class="schedule-card">
                <div class="schedule-time">7:15AM - 8:15AM</div>
                <div class="schedule-class">BOXING</div>
                <div class="schedule-trainer">SUSHANT KHATRI</div>
            </div>
            
            <div class="schedule-card">
                <div class="schedule-time">08:30AM - 09:30PM</div>
                <div class="schedule-class">BOXING</div>
                <div class="schedule-trainer">SUSHANT KHATRI</div>
            </div>
            
            <div class="schedule-card">
                <div class="schedule-time">4:00PM - 5:00PM</div>
                <div class="schedule-class">BOXING</div>
                <div class="schedule-trainer">SUSHANT KHATRI</div>
            </div>
            
            <div class="schedule-card">
                <div class="schedule-time">5:15PM - 6:15PM</div>
                <div class="schedule-class">BOXING</div>
                <div class="schedule-trainer">SUSHANT KHATRI</div>
            </div>
            
            <div class="schedule-card">
                <div class="schedule-time">6:30PM - 7:30PM</div>
                <div class="schedule-class">BOXING</div>
                <div class="schedule-trainer">SUSHANT KHATRI</div>
            </div>
        </div>
        
        <!-- Similar divs for other days (monday through friday) -->
        <!-- Keeping these unchanged from your original code -->
        <div class="class-schedule monday" style="display: none;">
            <!-- Content for Monday -->
        </div>
        
        <div class="class-schedule tuesday" style="display: none;">
            <!-- Content for Tuesday -->
        </div>
        
        <div class="class-schedule wednesday" style="display: none;">
            <!-- Content for Wednesday -->
        </div>
        
        <div class="class-schedule thursday" style="display: none;">
            <!-- Content for Thursday -->
        </div>
        
        <div class="class-schedule friday" style="display: none;">
            <!-- Content for Friday -->
        </div>
    </section>

    <section class="team-section">
        <h2>The Team</h2>
        <h3>Expert Trainers</h3>
        <div class="trainer-card">
            <img src="/api/placeholder/300/200" alt="Trainer">
            <div class="trainer-name">Sushant Khatri</div>
            <div class="trainer-role">Boxing Coach</div>
        </div>
    </section>

    <?php
    include_once 'footer.php';
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dayTabs = document.querySelectorAll('.day-tab');
            const schedules = document.querySelectorAll('.class-schedule');
            
            dayTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    dayTabs.forEach(t => t.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Hide all schedules
                    schedules.forEach(schedule => {
                        schedule.style.display = 'none';
                    });
                    
                    // Show the selected schedule
                    const day = this.getAttribute('data-day');
                    document.querySelector(`.${day}`).style.display = 'grid';
                });
            });

            // Close alert messages when the close button is clicked
            document.querySelectorAll('.btn-close').forEach(button => {
                button.addEventListener('click', function() {
                    this.parentElement.style.display = 'none';
                });
            });
        });
    </script>
</body>
</html>