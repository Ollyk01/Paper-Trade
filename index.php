<?php

session_start();
include('server/connection.php');
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paper Trade</title>
    <link rel="stylesheet" href="index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    
    <section class = "header">
        <nav>
            <a href ="index.php"><img src="IMAGES/Paper_Trade-removebg-preview (1).png"></a>
            <div class="nav-links" id="navLinks">
                <i class="fa fa-times" onclick="hideMenu()"></i>
                <ul>
                    <li><a href="index.php">HOME</a></li>
                    <li><a href="campus_shelf.php">CAMPUS SHELF</a></li>
                    <?php if (isset($_SESSION['logged_in'])): ?>
                        <li><a href="barter_buddy.php">BARTER BUDDY</a></li>
                        <li><a href="acc.php">MY ACC</a></li>
                        <li><a href="my_library.php">MY LIBRARY</a></li>
                    <?php endif; ?>

                    <?php if (!isset($_SESSION['logged_in'])): ?>
                        <a href="signup.php" class="hero-btn">SIGN UP</a>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['logged_in'])): ?>
                        <a href="logout.php" class="hero-btn">Logout</a>
                    <?php endif; ?>

                </ul>
            </div>
            <i class="fa fa-bars" onclick="showMenu()"></i>
        </nav>
        <div class="text-box">
            <h1>About Us</h1>
            <p>Welcome to Paper Trade, your one-stop destination for all things paper at an affordable price!</p>
        <p>Our platform was created to empower university students to buy, sell, and trade textbooks with one another. Built by students, for students, our platform offers secure payments, a barter option, and private messaging-so you can exchange books without sharing personal information or paying high fees. Whether you're clearing your shelf or preparing for a new semester, we make textbook trading simple, safe, and smart.</p>
        <p>Ready? Explore our wide range of products and find the perfect paper for your needs.</p>
        </div>
    </section>

    
<!-----JavaScript for the menu toggle------->
<script>
    var navLinks = document.getElementById("navLinks");

    function showMenu() {
        navLinks.style.right = "0";
    }

    function hideMenu() {
        navLinks.style.right = "-200px";
    }

</script>


<!-----stats------>
<section class="trader-stats" id="stats">
    <h1>Trader Stats</h1>
    <p>Join our growing community and start trading the smarter way.</p>  

    <div class="row">
        <div class="stat-column">
            <span class="number" data-target="75000">0</span>
            <p>USERS</p>
        </div>
        <div class="stat-column">
            <span class="number" data-target="100000">0</span>
            <p>BOOKS SOLD</p>
        </div>
        <div class="stat-column">
            <span class="number" data-target="50">0</span>
            <p>INSTITUTIONS</p>
        </div>
    </div>
</section>

<section class="user-manuel">
    <h2>How Paper Trade Works</h2>
    <p class="subtitle">Our platform makes textbook exchange simple, safe and affordable. Follow these easy steps to get started.</p>

    <div class="steps">
      <div class="step-card">
        <div class="step-number">1</div>
        <h3>List Your Books</h3>
        <p>Upload the textbooks you no longer need. Add details about condition and course.</p>
      </div>
      <div class="step-card">
        <div class="step-number">2</div>
        <h3>Make Connections</h3>
        <p>Browse for books you need or wait for interested students to contact you.</p>
      </div>
      <div class="step-card">
        <div class="step-number">3</div>
        <h3>Exchange Safely</h3>
        <p>Our courier service handles pickup and delivery, so you never need to meet strangers.</p>
      </div>
    </div>

    <button class="start-btn">Start Trading Books <span class="arrow">&#x25BC;</span></button>
</section>


<!-----Javascript stat counter------->
    <script>
  function animateCount(el, target) {
        const duration = 2000;
        const startTime = performance.now();
    
        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const value = Math.floor(progress * target);
    
            if (target >= 1000) {
                el.innerText = `${Math.floor(value / 1000)}K+`;
            } else {
                el.innerText = `${value}+`;
            }
    
            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }
    
        requestAnimationFrame(update);
    }
    
    function handleScrollAnimation() {
        const statsSection = document.getElementById('stats');
        const numbers = document.querySelectorAll('.number');
    
        let hasAnimated = false;
    
        function onScroll() {
            const sectionTop = statsSection.getBoundingClientRect().top;
            const sectionBottom = statsSection.getBoundingClientRect().bottom;
            const windowHeight = window.innerHeight;
    
            if (sectionTop < windowHeight && sectionBottom > 0 && !hasAnimated) {
                numbers.forEach((el) => {
                    const target = parseInt(el.getAttribute('data-target'));
                    animateCount(el, target);
                });
                hasAnimated = true;
                window.removeEventListener('scroll', onScroll);
            }
        }
    
        window.addEventListener('scroll', onScroll);
    }
    
    handleScrollAnimation();

    </script>

    

<!---------Faculty section-------->
    <section>
        <h2>Explore our Faculties</h2>

<div class="faculties-container">
  <div class="row">
    <div class="faculty-box">HUMANITIES</div>
    <div class="faculty-box">HEALTH SCIENCE</div>
    <div class="faculty-box">ENGINEERING & THE BUILT ENVIRONMENT</div>
  </div>
  <div class="row">
    <div class="faculty-box">LAW</div>
    <div class="faculty-box">COMMERCE</div>
    <div class="faculty-box">SCIENCE</div>
    <div class="faculty-box">EDUCATION</div>
  </div>
</div>
    </section>

<!-------Javacript faculty boxes interactivity------>
    <script>
    const facultyBoxes = document.querySelectorAll('.faculty-box');

facultyBoxes.forEach(box => {
  box.addEventListener('click', () => {
    // Toggle the class to make it expand on click
    box.classList.toggle('active');
  });
});
    </script>
</section>


<div class="text-block">
    <h1>Ready to Save on Textbooks?</h1>
    <p>Thousands of students are already trading textbooks the easy, secure way—why not join them?</p>
    </div>

<div class="button-container">
    <a href="campus_shelf.php" class="hero-btn">Browse Campus Shelf</a>
    <a href="barter_buddy.php" class="hero-btn">Try Barter Buddy</a>
</div>

<footer class="site-footer">
   <div class="main-content">
        <div class="footer-container">
            <div class="footer-brand">
                <img src="IMAGES/Paper_Trade-removebg-preview (1).png" alt="Paper Trade Logo" class="footer-logo" style="width: 200px; height: 100%; object-fit: cover;">
                    <div class="brand-text">
                    <div class="brand-tagline"></div>
                </div>
            </div>

            <div class="footer-section">
                <h3>Product</h3>
                <ul class="footer-links">
                    <li><a onclick="openModal('features')">Features</a></li>
                    <li><a onclick="openModal('pricing')">Pricing</a></li>
                    <li><a onclick="openModal('faq')">FAQ</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Company</h3>
                <ul class="footer-links">
                    <li><a onclick="openModal('about')">About Us</a></li>
                    <li><a onclick="openModal('contact')">Contact</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Legal</h3>
                <ul class="footer-links">
                    <li><a onclick="openModal('privacy')">Privacy Policy</a></li>
                    <li><a onclick="openModal('terms')">Terms of Service</a></li>
                    <li><a onclick="openModal('cookies')">Cookie Policy</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="copyright">© 2025 Paper Trade, Inc. All rights reserved.</div>
            <div class="social-links">
                <a onclick="openModal('twitter')">Twitter</a>
                <a onclick="openModal('linkedin')">LinkedIn</a>
                <a onclick="openModal('github')">GitHub</a>
            </div>
        </div>

    <!-- Modal -->
    <div id="modal" class="modal" onclick="closeModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2 id="modal-title" class="modal-title"></h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div id="modal-body" class="modal-body"></div>
        </div>
    </div>
</footer>

<script>
         const modalContent = {
    features: {
      title: "Features",
      content: `
        <p>Paper Trade is packed with features designed to make textbook trading easy, secure, and affordable.</p>
        <h4>Core Features</h4>
        <p>
          • User-friendly interface for easy navigation<br>
          • Secure payment processing<br>
          • Smart search & filter<br>
          • Integrated courier service for hassle-free delivery<br>
          • Private messaging system for safe communication<br>
          • Barter Buddy for trading textbooks without cash<br>
          • Mobile-friendly design
        </p>
      `
    },
    pricing: {
      title: "Pricing",
      content: `
        <p>Using the platform is 100% free—no listing or sign-up charges.</p>
        <p>For trades, courier costs are split equally between users.</p>
        <p>For purchases, we charge a small fee to cover courier costs.</p>
        <p>10% of the purchase price is charged as a service fee.</p>
        <p>No hidden fees, no surprises. Just straightforward, student-friendly pricing.</p>
      `
    },
    faq: {
      title: "Frequently Asked Questions",
      content: `
        <h4>How do I get started?</h4>
        <p>Simply create an account, verify it, and you can start trading.</p>
        <h4>How long does delivery take?</h4>
        <p>Delivery takes 3-5 business days.</p>
        <h4>Do you offer customer support?</h4>
        <p>We provide 24/7 customer support via email, chat, and phone for all paid plans.</p>
        <h4>Is delivery included in the price?</h4>
        <p>Absolutely. All prices displayed include delivery.</p>
      `
    },
    about: {
      title: "About Us",
      content: `
        <p>Founded in 2025, Paper Trade is a student-friendly platform to sell, buy, or trade textbooks at an affordable price.</p>
        <h4>Our Mission</h4>
        <p>Empower students through affordable and accessible textbook exchange.</p>
        <h4>Our Values</h4>
        <p>
          • Affordability: Reducing textbook costs<br>
          • Community: Fostering student connections<br>
          • Accessibility: Easy to use for everyone<br>
          • Convenience: Simplified process with integrated courier system
        </p>
      `
    },
    contact: {
      title: "Contact Us",
      content: `
        <p>We'd love to hear from you. Get in touch with our team:</p>
        <h4>General Inquiries</h4>
        <p>Email: support@papertrade.com<br>
        Phone: +27 78 712 3327</p>
        <h4>Support</h4>
        <p>Email: help@papertrade.com</p>
      `
    },
    privacy: {
      title: "Privacy Policy",
      content: `
        <p>Your privacy is important to us. This policy explains how we collect, use, and protect your information.</p>
        <h4>Information We Collect</h4>
        <p>We collect information you provide directly, usage data, and device information to improve our services.</p>
        <h4>How We Use Your Information</h4>
        <p>
          • To provide and improve our services<br>
          • To communicate with you<br>
          • To ensure security and prevent fraud<br>
          • To comply with legal obligations
        </p>
        <h4>Data Protection</h4>
        <p>We implement industry-standard security measures to protect your personal information.</p>
        <p>Last updated: January 15, 2025</p>
      `
    },
    terms: {
      title: "Terms of Service",
      content: `
        <p>By using our services, you agree to these terms and conditions.</p>
        <h4>Acceptance of Terms</h4>
        <p>By accessing our platform, you accept these terms in full.</p>
        <h4>User Responsibilities</h4>
        <p>
          • Provide accurate information<br>
          • Use services lawfully<br>
          • Respect intellectual property<br>
          • Maintain account security
        </p>
        <h4>Service Availability</h4>
        <p>We strive for 99.9% uptime but cannot guarantee uninterrupted service.</p>
        <p>Last updated: January 15, 2025</p>
      `
    },
    cookies: {
      title: "Cookie Policy",
      content: `
        <p>We use cookies to enhance your experience on our website.</p>
        <h4>What Are Cookies?</h4>
        <p>Cookies are small text files stored on your device that help us improve functionality.</p>
        <h4>Types of Cookies We Use</h4>
        <p>
          • Essential cookies for basic functionality<br>
          • Analytics cookies to understand usage<br>
          • Preference cookies to remember your settings<br>
          • Marketing cookies for relevant advertisements
        </p>
        <h4>Managing Cookies</h4>
        <p>You can control cookies through your browser settings.</p>
      `
    },
    twitter: {
      title: "Follow Us on Twitter",
      content: `
        <p>Stay up to date with our latest news and insights.</p>
        <h4>Paper Trade</h4>
        <p>
          • Product updates<br>
          • Industry trends<br>
          • Behind-the-scenes<br>
          • Customer stories
        </p>
      `
    },
    linkedin: {
      title: "Connect on LinkedIn",
      content: `
        <p>Connect with us on LinkedIn for professional updates and networking opportunities.</p>
        <p>
          • Career opportunities<br>
          • Thought leadership<br>
          • Company culture<br>
          • Partnerships
        </p>
      `
    },
    github: {
      title: "Open Source on GitHub",
      content: `
        <p>We believe in open source and contribute to the developer community.</p>
        <p>
          • Open source tools<br>
          • Code samples<br>
          • Docs and guides<br>
          • Community collaboration
        </p>
      `
    }
  };

  function openModal(contentKey) {
    const modal = document.getElementById('modal');
    const title = document.getElementById('modal-title');
    const body = document.getElementById('modal-body');

    const content = modalContent[contentKey];
    if (content) {
      title.textContent = content.title;
      body.innerHTML = content.content;
      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeModal(event) {
    const modal = document.getElementById('modal');
    if (!event || event.target === modal || event.target.classList.contains('close-btn')) {
      modal.classList.remove('active');
      document.body.style.overflow = 'auto';
    }
  }

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeModal();
    }
  });
    </script>
</body>
</html>