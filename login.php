<?php
session_start();  // Start session

require_once 'db_config.php';  // Database credentials

// Connect to database
$mysqli = new mysqli($host, $user, $pswd, $dbnm);
if ($mysqli->connect_error) {
    die("Database connection error.");
}

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve input
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $pw    = isset($_POST['password']) ? $_POST['password'] : '';

    // Verify credentials
    $stmt = $mysqli->prepare("SELECT password FROM friends WHERE friend_email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($storedPw);

    if ($stmt->fetch()) {
        if ($pw !== $storedPw) {
            $errors['password'] = "Incorrect password.";
        }
    } else {
        $errors['email'] = "Email not found.";
    }
    $stmt->close();

    // On success, set session and redirect
    if (empty($errors)) {
        $_SESSION['user_email'] = $email;
        header("Location: friendlist.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log In - My Friend System</title>
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
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .button-73 {
            appearance: none;
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
            text-align: center;
            transition: all .15s;
            text-decoration: none;
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
        <h2>Log In</h2>
        <form method="post">
            <p>
                <label for="email">Email</label><br>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="user@example.com"
                    title="Enter your registered email"
                    value="<?= htmlspecialchars($email) ?>"
                >
                <?php if (!empty($errors['email'])): ?>
                    <div class="error"><?= $errors['email'] ?></div>
                <?php endif; ?>
            </p>
            <p>
                <label for="password">Password</label><br>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Password"
                    title="Enter your password"
                >
                <?php if (!empty($errors['password'])): ?>
                    <div class="error"><?= $errors['password'] ?></div>
                <?php endif; ?>
            </p>
            <div class="buttons">
                <button type="submit" class="button-73">Log In</button>
                <button type="reset" class="button-73">Clear</button>
            </div>
        </form>
        <div class="buttons" style="margin-top:1rem;">
            <a href="index.php" class="button-73">Home</a>
        </div>
    </div>
</body>
</html>
