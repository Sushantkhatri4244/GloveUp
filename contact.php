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
    <title>Contact Us - GloveUp Boxing Training Center</title>
    <link rel="stylesheet" href="LandingPage.css" />
    <link rel="stylesheet" href="contact.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

    <div class="contact-heading">
        <h1>Get In Touch</h1>
        <p>We're very approachable and would love to speak to you. Feel free to call, send us an email or simply complete the inquiry form.</p>
    </div>

    <div class="contact-container">
        <div class="contact-info">
            <h2>Contact Information</h2>
            
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="contact-text">
                    <h3>Our Location</h3>
                    <p>Bishnudol, Lalitpur, Nepal</p>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="contact-text">
                    <h3>Phone Number</h3>
                    <p>+977-9818809717</p>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="contact-text">
                    <h3>Email Address</h3>
                    <p>gloveupboxingnp@gmail.com</p>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="contact-text">
                    <h3>Operating Hours</h3>
                    <p>Sunday - Friday: 6:00 AM - 7:00 PM</p>
                </div>
            </div>

            <div class="contact-social">
                <h3>Connect With Us</h3>
                <div class="social-icons">
                    <a href="https://facebook.com" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="https://youtube.com" target="_blank"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>

        <div class="contact-form">
            <h2>Send Us A Message</h2>
            <form id="contactForm">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <select id="subject" name="subject">
                        <option value="general">General Inquiry</option>
                        <option value="membership">Membership Information</option>
                        <option value="classes">Class Schedule</option>
                        <option value="private">Private Training</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="message">Your Message</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                
                <button type="submit" class="submit-btn">Send Message</button>
            </form>
        </div>
    </div>

    <div class="location-map">
        <h2>Find Us Here</h2>
        <iframe 
            src="https://maps.google.com/maps?q=27.639649,85.364320&z=15&output=embed" 
            allowfullscreen="" 
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>

    <?php
    include_once 'footer.php';
    ?>

    <script>
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for your message! We will get back to you soon.');
            this.reset();
        });
    </script>
</body>
</html>