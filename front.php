<?php 
include_once 'config/roleGate.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>JAM Lying-In Clinic</title>
  <link rel="stylesheet" href="front.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

  <!-- Navigation Bar -->
  <nav class="navbar">
    <a href="#home" class="navbar-brand">JAM Lying-In Clinic</a>
    <ul class="navbar-nav">
      <li><a href="#home" class="nav-link">Home</a></li>
      <li><a href="#services" class="nav-link">Services</a></li>
      <li><a href="#doctors" class="nav-link">Doctors</a></li>
      <li><a href="#contact" class="nav-link">Contact Us</a></li>
      <li><a href="auth/signup.php" class="nav-link">Sign Up</a></li>
      <li><a href="auth/login.php?role=patient" class="nav-link">Sign In</a></li>

    </ul>
  </nav>

  <!-- Main Content -->
  <main class="main-content">

    <!-- Home Section -->
    <section id="home" class="section home">
      <div class="section-inner">
        <img src="logo.png" alt="JAM Lying-In Clinic Logo" class="logo" />
        <h1>Welcome to Our Clinic</h1>
        <p>
          At <strong>JAM Lying-In Clinic</strong>, we are committed to providing compassionate,
          affordable, and high-quality maternal care services to women in every stage of pregnancy
          and childbirth. Located at the heart of our community, we are a trusted partner in safe
          and personalized maternity care.
        </p>
      </div>
      <!-- Overlay slides for background -->
      <div class="overlay-slide2"></div>
      <div class="overlay-slide3"></div>
    </section>

    <!-- Services Section -->
    <section id="services" class="section services">
      <div class="section-inner">
        <h1>Our Services</h1>
        <p> We offer comprehensive healthcare services for mothers and families, including prenatal check-ups, birthing assistance, and postpartum care. Our clinic also provides family planning consultations and pediatric services to ensure holistic maternal and child health. </p>
        <ul style="text-align:left; max-width:500px; margin:20px auto; list-style:'✓ '; line-height:1.8;">
          <li>Prenatal Check Up</li>
          <li>Pedia Check Up</li>
          <li>Normal Delivery (Handled by Registered Midwife)</li>
          <li>BCG / HEPA-B Vaccine</li>
          <li>Newborn Screening Test</li>
          <li>Newborn Hearing Test</li>
          <li>Postpartum Check Up</li>
          <li>Family Planning</li>
          <li>Papsmear</li>
        </ul>
      </div>
    </section>

    <!-- Doctors Section -->
    <section id="doctors" class="section doctors">
      <div class="section-inner">
        <h1>Meet Our Doctors</h1>

        <div class="doctor">
          <h3>Dra. Rowena A. Cunanan</h3>
          <p><strong>Specialty:</strong> Pediatrics</p>
          <p><strong>Schedule:</strong> Wednesday - 2:00–3:00 PM</p>
          <p><strong>Services Offered:</strong></p>
          <ul style="list-style: disc; text-align:left; max-width:500px; margin:10px auto;">
            <li>Well baby check up</li>
            <li>Immunization</li>
            <li>Diagnosis and treatment of childhood illness</li>
          </ul>
        </div>

        <div class="doctor">
          <h3>Dra. Marites B. Bacunata, MD, FPOGS</h3>
          <p><strong>Specialty:</strong> OB-GYNE</p>
          <p><strong>Schedule:</strong> Tuesday - 6:00–7:00 PM</p>
          <p><strong>Services Offered:</strong></p>
          <ul style="list-style: disc; text-align:left; max-width:500px; margin:10px auto;">
            <li>Family Planning</li>
            <li>Vaccination</li>
            <li>Papsmear</li>
          </ul>
        </div>

        <div class="doctor">
          <h3>Dra. Xiemera L. Sanchez, MD, FPOGS</h3>
          <p><strong>Specialty:</strong> OB-GYNE</p>
          <p><strong>Schedule:</strong> Thursday - 10:00–11:00 AM</p>
          <p><strong>Services Offered:</strong></p>
          <ul style="list-style: disc; text-align:left; max-width:500px; margin:10px auto;">
            <li>Family Planning</li>
            <li>Vaccination</li>
            <li>Papsmear</li>
          </ul>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section contact">
      <div class="section-inner">
        <h1>Contact Us</h1>
        <p>
          Reach out for appointments, inquiries, or emergency services.
          We’re always ready to assist you.
        </p>
        <p>
          If you are a new user, please sign up to create an account. If you are an existing user,
          please sign in to access your account.
        </p>
        <p><strong>Phone:</strong> 0956 209 6078 | <strong>Email:</strong> jhen.medillo@gmail.com</p>
        <p><strong>Address:</strong> 261 GKD BLDG. MALAGASANG II-A IMUS CAVITE</p>
        <p><strong>Facebook:</strong> https://www.facebook.com/jenarmamedillo </p>
      </div>
    </section>

  </main>
</body>
</html>
