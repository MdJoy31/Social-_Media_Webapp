<?php
// Start session for user state handling
session_start();

// Load database configuration and setup logic
require_once 'db_config.php';
require_once 'database_setup.php';

// Create required table and insert initial record, capturing status message
$setup     = new DatabaseSetup($host, $user, $pswd, $dbnm);
$dbMessage = $setup->setup();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Friend System - Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    /* Root variables & global reset */
    :root {
      --dark:   #09101b;
      --navy:   #142d4c;
      --mint:   #9fd3c7;
      --light:  #eef2f5;
      --text:   #e0e0e0;
      --radius:12px;
      --trans:  0.3s;
    }
    *,*::before,*::after {
      box-sizing: border-box;
      margin:     0;
      padding:    0;
    }
    html, body {
      height:      100%;
      overflow:    hidden;
      background:  linear-gradient(180deg, var(--dark), var(--navy));
      font-family: 'Segoe UI', sans-serif;
      color:       var(--text);
      display:     flex;
      flex-direction: column;
      align-items:    center;
      justify-content: center;
      padding:        1rem;
    }

    /* Hero banner */
    .hero {
      position:      relative;
      width:         100%;
      max-width:     600px;
      height:        140px;
      margin-bottom: 2rem;
      background:    linear-gradient(180deg, var(--navy), var(--mint));
      border-radius: var(--radius);
      overflow:      visible;
      display:       flex;
      align-items:   center;
      justify-content: center;
    }
    .banner {
      z-index:      1;
      font-size:    1.8rem;
      font-weight:  600;
      color:        var(--light);
      opacity:      0;
      transform:    translateY(-20px);
      animation:    slideDown 1s ease-out forwards 0.3s;
      text-align:   center;
      cursor:       pointer;
      transition:   color var(--trans);
    }
    .banner:hover {
      color: #fff;
    }
    @keyframes slideDown {
      to {
        opacity:   1;
        transform: translateY(0);
      }
    }

    /* Balloon animation */
    .balloon {
      position:      absolute;
      bottom:        -20px;
      width:         14px;
      height:        18px;
      background:    var(--light);
      border-radius: 50% 50% 45% 45%;
      opacity:       0.6;
      animation-name:      rise;
      animation-timing-function: ease-in-out;
      animation-fill-mode: forwards;
    }
    @keyframes rise {
      0%   { transform: translateY(0) scale(0.7); opacity:0.3; }
      50%  { transform: translateY(-100px) scale(1);  opacity:0.7; }
      100% { transform: translateY(-180px) scale(0.7); opacity:0;   }
    }

    /* Flip card */
    .flip-container {
      perspective: 1000px;
      width:       90%;
      max-width:   500px;
      height:      200px;
      margin-bottom: 2rem;
    }
    .flipper {
      position:       relative;
      width:          100%;
      height:         100%;
      transform-style: preserve-3d;
      transition:     transform .8s;
    }
    .flip-container.flipped .flipper {
      transform: rotateY(180deg);
    }
    .flip-card-front,
    .flip-card-back {
      position:           absolute;
      top:                0;
      left:               0;
      width:              100%;
      height:             100%;
      backface-visibility: hidden;
      border-radius:      var(--radius);
      padding:            1rem;
      box-shadow:         0 4px 16px rgba(0,0,0,0.5);
      display:            flex;
      flex-direction:     column;
      justify-content:    center;
      align-items:        center;
      text-align:         center;
    }
    .flip-card-front {
      background: linear-gradient(180deg, var(--navy), var(--mint));
      color:      #fff;
    }
    .flip-card-back {
      background: linear-gradient(180deg, var(--mint), var(--navy));
      color:      var(--navy);
      transform:  rotateY(180deg);
      font-weight: 600;
    }

    /* Buttons */
    .buttons {
      display:      flex;
      gap:          1rem;
      flex-wrap:    wrap;
      justify-content: center;
    }
    .button-74 {
      position:         relative;
      display:          inline-flex;
      align-items:      center;
      justify-content:  center;
      padding:          0 30px;
      height:           50px;
      background:       radial-gradient(circle, var(--mint) 40%, var(--navy) 100%);
      color:            var(--navy);
      border:           2px solid var(--navy);
      border-radius:    30px;
      font-size:        16px;
      font-weight:      600;
      cursor:           pointer;
      user-select:      none;
      transition:       background var(--trans), color var(--trans), transform var(--trans);
      text-decoration:  none;
    }
    .button-74:after {
      content:      "";
      position:     absolute;
      top:          -2px;
      left:         0;
      width:        100%;
      height:       50px;
      background:   var(--navy);
      border-radius:30px;
      transform:    translate(8px, 8px);
      transition:   transform .2s ease-out;
      z-index:      -1;
    }
    .button-74:hover {
      background: var(--navy);
      color:      #fff;
      transform:  translateY(-2px);
    }
    .button-74:hover:after {
      transform: translate(0, 0);
    }
    .button-74:active {
      box-shadow: var(--navy) 2px 2px 0 0;
      transform:  translate(2px, 2px);
    }
    @media (min-width: 768px) {
      .button-74 { padding: 0 40px; }
    }
    @media (max-width: 480px) {
      .button-74 { width: 80%; margin: .5rem 0; }
    }

    /* Database setup message */
    .msg {
      margin-top:    2rem;
      padding:       1rem;
      background:    var(--dark);
      border-left:   4px solid var(--mint);
      border-radius: var(--radius);
      font-style:    italic;
      text-align:    center;
      max-width:     600px;
    }
  </style>
</head>
<body>

  <!-- Hero banner; click title to flip student card -->
  <div class="hero" id="hero">
    <div class="banner" onclick="this.classList.toggle('flipped')">
      My Friend System
    </div>
  </div>

  <!-- Flip card showing student details / declaration -->
  <div class="flip-container" id="flip" onclick="this.classList.toggle('flipped')">
    <div class="flipper">
      <div class="flip-card-front">
        <h2>Md Jannatul Rakib Joy</h2>
        <p>Student ID: 103799644</p>
        <p>Email: 103799644@student.swin.edu.au</p>
        <p style="margin-top:1rem;font-size:.9rem;">
          I declare that this assignment is my individual work.<br>
          I have not worked collaboratively nor copied from any other source.
        </p>
      </div>
      <div class="flip-card-back">
        <p>Click to flip back and view student details</p>
      </div>
    </div>
  </div>

  <!-- Navigation links -->
  <div class="buttons">
    <a class="button-74" href="signup.php">Sign Up</a>
    <a class="button-74" href="login.php">Log In</a>
    <a class="button-74" href="about.php">About</a>
  </div>

  <!-- Show result of database setup if available -->
  <?php if (!empty($dbMessage)): ?>
    <div class="msg"><?= htmlspecialchars($dbMessage) ?></div>
  <?php endif; ?>

  <script>
    // Periodically launch animated balloons in the hero area
    setInterval(() => {
      const b = document.createElement('div');
      b.className = 'balloon';
      b.style.left = Math.random() * 80 + 10 + '%';
      const d = Math.random() * 2 + 4;
      b.style.animationDuration = d + 's';
      document.getElementById('hero').appendChild(b);
      setTimeout(() => b.remove(), d * 1000);
    }, 3000);
  </script>
</body>
</html>
