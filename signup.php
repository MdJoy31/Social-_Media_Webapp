<?php
session_start();                                // Start session for user tracking
require_once 'db_config.php';                   // Database credentials
require_once 'database_setup.php';              // Ensure tables exist

// Create tables if they do not exist
$setup     = new DatabaseSetup($host, $user, $pswd, $dbnm);
$dbMessage = $setup->setup();

// Connect to database for signup processing
$mysqli = new mysqli($host, $user, $pswd, $dbnm);
if ($mysqli->connect_error) {
    die("Database connection error.");
}

$errors      = [];
$email       = '';
$profileName = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and trim input values
    $email       = isset($_POST['email']) ? trim($_POST['email']) : '';
    $profileName = isset($_POST['profile_name']) ? trim($_POST['profile_name']) : '';
    $pw1         = isset($_POST['password']) ? $_POST['password'] : '';
    $pw2         = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Validate email format and uniqueness
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } else {
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM friends WHERE friend_email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        if ($count > 0) {
            $errors['email'] = "Email already registered.";
        }
    }

    // Validate profile name (letters only)
    if ($profileName === '' || !preg_match('/^[A-Za-z]+$/', $profileName)) {
        $errors['profile_name'] = "Letters only, no spaces.";
    }

    // Validate password content and match
    if ($pw1 === '' || !preg_match('/^[A-Za-z0-9]+$/', $pw1)) {
        $errors['password'] = "Letters and numbers only.";
    }
    if ($pw1 !== $pw2) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    // Insert new user and redirect on success
    if (empty($errors)) {
        $today = date('Y-m-d');
        $stmt  = $mysqli->prepare(
            "INSERT INTO friends
             (friend_email, password, profile_name, date_started, num_of_friends)
             VALUES (?, ?, ?, ?, 0)"
        );
        $stmt->bind_param('ssss', $email, $pw1, $profileName, $today);
        $stmt->execute();
        $_SESSION['user_email'] = $email;
        header("Location: friendadd.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - My Friend System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --dark:  #09101b;
            --navy:  #142d4c;
            --mint:  #9fd3c7;
            --text:  #e0e0e0;
            --light: #eef2f5;
            --radius:12px;
        }
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            background: var(--dark);
            color: var(--text);
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }
        .container {
            background: linear-gradient(180deg, var(--navy), var(--mint));
            padding: 2rem;
            border-radius: var(--radius);
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.5);
        }
        h2 {
            text-align: center;
            margin-bottom: 1rem;
        }
        form p {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: .3rem;
        }
        input {
            width: 100%;
            padding: .5rem;
            border: 1px solid var(--light);
            border-radius: 4px;
            background: var(--dark);
            color: var(--text);
        }
        .error {
            color: #FF6B6B;
            font-size: .9rem;
            margin-top: .3rem;
        }
        .buttons {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }
        .button-73 {
            background-color: #FFFFFF;
            border-radius: 40em;
            border: none;
            box-shadow: #ADCFFF 0 -12px 6px inset;
            color: #000;
            cursor: pointer;
            font-family: -apple-system, sans-serif;
            font-size: 1.2rem;
            font-weight: 700;
            padding: 1rem 1.3rem;
            margin: .25rem;
            text-decoration: none;
            transition: all .15s;
        }
        .button-73:hover {
            background-color: #FFC229;
            box-shadow: #FF6314 0 -6px 8px inset;
            transform: scale(1.125);
        }
        .button-73:active {
            transform: scale(1.025);
        }
        @media (min-width: 768px) {
            .button-73 {
                font-size: 1.5rem;
                padding: .75rem 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Registration Page</h2>
        <form method="post">
            <p>
                <label for="email">Email</label><br>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="e.g. user@example.com"
                    title="Enter a valid email address"
                    value="<?= htmlspecialchars($email) ?>"
                >
                <?php if (!empty($errors['email'])): ?>
                    <div class="error"><?= $errors['email'] ?></div>
                <?php endif; ?>
            </p>
            <p>
                <label for="profile_name">Profile Name</label><br>
                <input
                    type="text"
                    id="profile_name"
                    name="profile_name"
                    placeholder="Letters only"
                    title="Use letters A - Z only"
                    value="<?= htmlspecialchars($profileName) ?>"
                >
                <?php if (!empty($errors['profile_name'])): ?>
                    <div class="error"><?= $errors['profile_name'] ?></div>
                <?php endif; ?>
            </p>
            <p>
                <label for="password">Password</label><br>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Letters & numbers"
                    title="Letters and numbers only"
                >
                <?php if (!empty($errors['password'])): ?>
                    <div class="error"><?= $errors['password'] ?></div>
                <?php endif; ?>
            </p>
            <p>
                <label for="confirm_password">Confirm Password</label><br>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Repeat your password"
                    title="Must match the password above"
                >
                <?php if (!empty($errors['confirm_password'])): ?>
                    <div class="error"><?= $errors['confirm_password'] ?></div>
                <?php endif; ?>
            </p>
            <div class="buttons">
                <button type="submit" class="button-73">Register</button>
                <button type="reset"  class="button-73">Clear</button>
            </div>
        </form>
        <div class="buttons">
            <a href="index.php" class="button-73">Home</a>
        </div>
    </div>
</body>
</html>
