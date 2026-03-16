<?php
session_start();
include "db_connect.php";

/* Prevent browser caching */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/* FETCH PRICES */
$prices = ['2_wheeler' => 0, '4_wheeler' => 0 ];

$result = $conn->query("SELECT v_type, price_per_hour FROM pricing");
while ($row = $result->fetch_assoc()) {
    $prices[$row['v_type']] = $row['price_per_hour'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkMate – Smart Parking</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

    <!-- NAVBAR -->
    <header class="navbar">
        <div class="logo">
            <img src="images/logo.png.png" alt="ParkMate logo">
            <span class="logo-text">Park<span>Mate</span></span>
        </div>
        <nav>
    <a href="#home">Home</a>
    <a href="#about">About Us</a>
    <a href="#contact">Contact</a>

    <?php if (isset($_SESSION['u_id'])) { ?>

    <?php if ($_SESSION['role_id'] == 1) { ?>
        <!-- Admin -->
        <a href="admin_dashh.php" title="Admin Dashboard" class="user-icon">👤</a>
    <?php } else { ?>
        <!-- User -->
        <a href="user_dashboard.php" title="User Dashboard" class="user-icon">👤</a>
    <?php } ?>

    <a href="logout.php" class="login-btn">Logout</a>

<?php } else { ?>
    <!-- Not logged in -->
    <a href="login1.php" class="login-btn">Login</a>
<?php } ?>
    <button id="themeToggle" class="theme-toggle" aria-label="Toggle dark mode">🌙</button>
</nav>
    </header>

    <!-- HERO -->
    <section class="hero" id="home">
        <div class="hero-content">
            <p class="tagline">LET’S BID GOODBYE TO ALL THE PARKING PROBLEMS!</p>
            <h1>Using <span>Park</span><em>Mate</em></h1>
            <p class="subtitle">Show My Parking provides you with a smarter way to park when you're on the go.</p>
        </div>
    </section>

    <!-- SIMPLIFY PARKING -->
    <section class="simplify">
    <div class="simplify-text">
        <h2>We Simplify Parking <br> in <span>Park</span><em>Mate</em></h2>
        <p>Easily find, reserve, and manage parking spaces with our intelligent system — built to save time and reduce traffic.</p>
        <a href="timeslot.php" class="btn-primary">Book Now</a>
    </div>

    <!-- Cars only -->
    <div class="cars-container">
        <div class="cars">
            <img src="images/car1.png" class="car car1" alt="Car 1">
            <img src="images/car2.png" class="car car2" alt="Car 2">
            <img src="images/car3.png" class="car car3" alt="Car 3">
        </div>
    </div>
</section>




    <!-- WHY CHOOSE -->
    <section class="why-choose">
        <h2 class="section-title">Why Choose <span>Park</span><em>Mate</em>?</h2>
        <div class="features-grid">
            <div class="feature">
                <h3><span>01</span> Real-Time Parking Availability</h3>
                <p>The system displays real-time info about available and occupied parking slots.</p>
            </div>
            <div class="feature">
                <h3><span>02</span> Smart Slot Booking</h3>
                <p>Users can easily book parking slots; booked slots are marked unavailable.</p>
            </div>
            <div class="feature">
                <h3><span>03</span> Time-Saving & Hassle-Free</h3>
                <p>Reduces the time spent searching for parking and ensures smooth entry.</p>
            </div>
            <div class="feature">
                <h3><span>04</span> User-Friendly Interface</h3>
                <p>Simple interface to easily navigate and book parking slots.</p>
            </div>
            <div class="feature">
                <h3><span>05</span> Reduced Traffic Congestion</h3>
                <p>Guides users to available slots, reducing unnecessary vehicle movement.</p>
            </div>
            <div class="feature">
                <h3><span>06</span> Scalable System</h3>
                <p>The system can be expanded by adding more slots or advanced features.</p>
            </div>
        </div>
    </section>

    <!-- ABOUT -->
    <section id="about" class="about-section">
        <h2 class="section-title">About <span>Park</span><em>Mate</em></h2>
        <div class="about-container">
            <div class="about-card">
                <img src="images/vision_logo.png" alt="Vision">
                <h3>Our Vision</h3>
                <p>To make parking hassle-free & provide safe and secure parking.</p>
            </div>
            <div class="about-card">
                <img src="images/mission-logo.png" alt="Mission">
                <h3>Our Mission</h3>
                <p>Address parking issues effectively while reducing congestion.</p>
            </div>
            <div class="about-card">
                <img src="images/goal_logo.png" alt="Goal">
                <h3>Goal</h3>
                <p>Save time, reduce traffic, improve efficiency.</p>
            </div>
            <div class="about-card">
                <img src="images/value_logo.png" alt="Value">
                <h3>Value</h3>
                <p>Secure, eco-friendly, and user-focused.</p>
            </div>
        </div>
        <div class="about-images">
            <img src="images/parkingarea.png" alt="Parking Image">
            <img src="images/parkingarea3.png" alt="Parking Image">
        </div>
    </section>

    <!-- PRICING -->
    <section class="pricing-section">
    <div class="pricing-cards">

        <div class="card">
            <h3>For 2-Wheel</h3>
            <p class="price">₹<?= $prices['2_wheeler'] ?? 'N/A' ?></p>
            <span class="per-hour">Per Hour</span><br/>
            <button><a href="timeslot.php">Book Now</a></button>
        </div>

        <div class="card">
            <h3>For 4-Wheel</h3>
            <p class="price">₹<?= $prices['4_wheeler'] ?? 'N/A' ?></p>
            <span class="per-hour">Per Hour</span><br/>
            <button><a href="timeslot.php">Book Now</a></button>
        </div>
		</div>
		</section>
 
<!-- FAQ -->
<section id="faq" class="faq-section reveal">
<h2 class="section-title">Frequently Asked Questions</h2>
<div class="faq">
  <details>
    <summary>How do I book a parking slot?</summary>
    <p>Select time slot → choose vehicle → confirm booking.</p>
  </details>
  <details>
    <summary>Is parking secure?</summary>
    <p>Yes, ParkMate provides monitored and secure parking.</p>
  </details>
  <details>
    <summary>Can I cancel my booking?</summary>
    <p>Cancellation depends on parking rules set by admin.</p>
  </details>
</div>
</section>

    <!-- ================= CONTACT ================= -->
    <section id="contact" class="contact-section">
    <h2 class="section-title">Contact Us</h2>
    <div class="contact-box">
        <p><strong>Email:</strong> admin@parkmate.com</p>
        <p><strong>Phone:</strong> +91 9265888587</p>
        <p><strong>Address:</strong> Ahmedabad, India</p>
    </div>
</section>

<footer class="footer">
<p>© 2026 ParkMate | Smart Parking System</p>
</footer>
<script  src="js/index.js"></script>
</body>
</html>