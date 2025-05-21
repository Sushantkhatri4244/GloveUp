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
    <title>Classes - GloveUp Boxing Training Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f4f4f4;
            color: #333;
        }
        
        /* Header Styles */
        .main-header {
            background-color: #222;
            color: white;
            text-align: center;
            padding: 4rem 2rem;
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('/api/placeholder/1200/400');
            background-size: cover;
            background-position: center;
        }
        
        .main-header h1 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
            color: white;
        }
        
        .main-header p {
            font-size: 1.25rem;
            color: #f00;
            font-weight: bold;
        }
        
        /* Classes Section */
        .classes-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .section-title p {
            font-size: 1.2rem;
            color: #666;
        }
        
        /* Day Tabs */
        .day-tabs {
            display: flex;
            justify-content: center;
            background: #222;
            border-radius: 30px;
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .day-tab {
            padding: 15px 25px;
            cursor: pointer;
            border: none;
            background: transparent;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
            transition: background-color 0.3s;
        }
        
        .day-tab.active {
            background-color: #f00;
        }
        
        /* Class Cards */
        .class-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 2rem;
        }
        
        .class-card {
            background-color: #222;
            border-radius: 10px;
            padding: 2rem;
            color: white;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .class-card:hover {
            transform: translateY(-5px);
        }
        
        .class-time {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: #ddd;
        }
        
        .class-type {
            color: #f00;
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 1rem;
            text-transform: uppercase;
        }
        
        .trainer-name {
            font-size: 1rem;
            color: #ddd;
            text-transform: uppercase;
        }
        
        /* Join Now Section */
        .join-now {
            text-align: center;
            margin: 3rem 0;
            padding: 3rem;
            background-color: #f00;
            color: white;
            border-radius: 10px;
        }
        
        .join-now h3 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .join-now p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #222;
            color: white;
            text-decoration: none;
            font-weight: bold;
            border-radius: 30px;
            transition: transform 0.3s, background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #000;
            transform: scale(1.05);
        }
        
        /* No Classes Message */
        .no-classes {
            text-align: center;
            padding: 2rem;
            font-size: 1.2rem;
            color: #666;
            grid-column: 1 / -1;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .day-tabs {
                flex-wrap: wrap;
                border-radius: 15px;
            }
            
            .day-tab {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
            
            .class-cards {
                grid-template-columns: 1fr;
            }
        }

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
    
    <!-- Header Section -->
    <header class="main-header">
        <h1>Classes Schedule</h1>
        <p>Train with the best at GloveUp Boxing</p>
    </header>
    
    <!-- Classes Section -->
    <div class="classes-container">
        <div class="section-title">
            <h2>Our Classes</h2>
            <p>Join our expert-led sessions designed for all skill levels</p>
        </div>
        
        <?php
       

        try {
            // Get all days of the week from the database
            $sql_days = "SELECT DISTINCT day_of_week FROM classes ORDER BY 
                        CASE 
                            WHEN day_of_week = 'sunday' THEN 1
                            WHEN day_of_week = 'monday' THEN 2
                            WHEN day_of_week = 'tuesday' THEN 3
                            WHEN day_of_week = 'wednesday' THEN 4
                            WHEN day_of_week = 'thursday' THEN 5
                            WHEN day_of_week = 'friday' THEN 6
                            WHEN day_of_week = 'saturday' THEN 7
                        END";
            
            $stmt_days = $conn->prepare($sql_days);
            $stmt_days->execute();
            
            if ($stmt_days->rowCount() > 0) {
                echo '<div class="day-tabs">';
                $first_day = true;
                $days = $stmt_days->fetchAll(PDO::FETCH_ASSOC);
                
                foreach($days as $day_row) {
                    $day = $day_row["day_of_week"];
                    $active_class = $first_day ? "active" : "";
                    echo '<button class="day-tab ' . $active_class . '" data-day="' . $day . '">' . ucfirst($day) . '</button>';
                    $first_day = false;
                }
                
                echo '</div>';
                
                // For each day, get the classes
                $first_day = true;
                foreach($days as $day_row) {
                    $day = $day_row["day_of_week"];
                    $display_style = $first_day ? "grid" : "none";
                    
                    echo '<div id="' . $day . '-classes" class="class-cards" style="display: ' . $display_style . ';">';
                    
                    // Get classes for this day
                    $sql_classes = "SELECT * FROM classes WHERE day_of_week = :day ORDER BY start_time";
                    $stmt_classes = $conn->prepare($sql_classes);
                    $stmt_classes->bindParam(':day', $day, PDO::PARAM_STR);
                    $stmt_classes->execute();
                    
                    if ($stmt_classes->rowCount() > 0) {
                        $classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);
                        foreach($classes as $class_row) {
                            // Format times to be more readable
                            $start_time = date("h:i A", strtotime($class_row["start_time"]));
                            $end_time = date("h:i A", strtotime($class_row["end_time"]));
                            
                            echo '<div class="class-card">
                                    <div class="class-time">' . $start_time . ' - ' . $end_time . '</div>
                                    <div class="class-type">' . htmlspecialchars($class_row["class_type"]) . '</div>
                                    <div class="trainer-name">' . htmlspecialchars($class_row["trainer_name"]) . '</div>
                                  </div>';
                        }
                    } else {
                        echo '<div class="no-classes">No classes scheduled for this day.</div>';
                    }
                    
                    echo '</div>';
                    $first_day = false;
                }
            } else {
                echo '<p>No classes scheduled. Please check back later.</p>';
            }
        } catch(PDOException $e) {
            echo '<p>An error occurred while loading the class schedule. Please try again later.</p>';
            // In a production environment, you would log this error instead of showing it to users
            // error_log('Database error: ' . $e->getMessage());
        }
        ?>
        
        <!-- Join Now Section -->
        <div class="join-now">
            <h3>Ready to Step in the Ring?</h3>
            <p>Join our classes today and experience the physical and mental benefits of boxing. Our expert trainers are ready to guide you on your journey, whether you're a complete beginner or looking to advance your skills.</p>
            <a href="form.php" class="btn">Join Now</a>
        </div>
    </div>

    <?php
    include_once 'footer.php';
    ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Day tabs functionality
            const dayTabs = document.querySelectorAll('.day-tab');
            const classCards = document.querySelectorAll('.class-cards');
            
            dayTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs and hide all class cards
                    dayTabs.forEach(t => t.classList.remove('active'));
                    classCards.forEach(c => c.style.display = 'none');
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Show the corresponding class cards
                    const day = this.getAttribute('data-day');
                    document.getElementById(`${day}-classes`).style.display = 'grid';
                });
            });
            
            // Set current day as active by default if not already set
            if (!document.querySelector('.day-tab.active')) {
                const currentDay = new Date().toLocaleDateString('en-US', { weekday: 'lowercase' });
                const currentDayTab = document.querySelector(`.day-tab[data-day="${currentDay}"]`);
                
                if (currentDayTab) {
                    currentDayTab.click();
                }
            }
        });
    </script>
</body>
</html>