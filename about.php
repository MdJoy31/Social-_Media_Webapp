<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About - My Friend System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --dark:  #09101b;
            --navy:  #142d4c;
            --mint:  #9fd3c7;
            --text:  #e0e0e0;
            --radius:12px;
            --trans: .3s;
        }
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            background: linear-gradient(180deg, var(--dark), var(--navy));
            color: var(--text);
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
            min-height: 100vh;
        }
        h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .accordion {
            width: 100%;
            max-width: 600px;
            margin-bottom: 2rem;
        }
        details {
            background: var(--navy);
            border-radius: var(--radius);
            margin-bottom: .5rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.5);
        }
        summary {
            list-style: none;
            cursor: pointer;
            padding: 1rem 2.5rem 1rem 1rem;
            position: relative;
            font-weight: 600;
            font-size: 1.1rem;
        }
        summary::marker { content: none; }
        summary::after {
            content: '+';
            position: absolute;
            right: 1rem;
            top: 1rem;
            font-size: 1.2rem;
            transition: transform var(--trans);
        }
        details[open] summary::after { content: '-'; }
        details p {
            padding: 0 1rem 1rem 1rem;
            line-height: 1.4;
            font-size: .95rem;
        }
        details img {
            display: block;
            max-width: 100%;
            margin: 1rem auto;
            border-radius: var(--radius);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        ul.links {
            list-style: disc inside;
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        ul.links li {
            margin: 0;
        }
        .button-73 {
            appearance: none;
            background-color: #FFFFFF;
            border-radius: 40em;
            border: none;
            box-shadow: #ADCFFF 0 -12px 6px inset;
            color: #000;
            cursor: pointer;
            display: inline-block;
            font-family: -apple-system, sans-serif;
            font-size: 1rem;
            font-weight: 700;
            padding: .75rem 1.5rem;
            text-align: center;
            transition: all var(--trans);
            user-select: none;
            text-decoration: none;
        }
        .button-73:hover {
            background-color: #FFC229;
            box-shadow: #FF6314 0 -6px 8px inset;
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <h2>About This Assignment</h2>

    <div class="accordion">
        <details>
            <summary>1. Tasks Not Completed</summary>
            <p>All tasks from Part 1 through Part 4 (Tasks 1 - 9) have been fully implemented including the extra challenge.</p>
        </details>
        <details>
            <summary>2. Special Features Attempted</summary>
            <p>Dark-gradient theme, flip-card UI, rising balloons animation, alphabetical friend's sorting, tooltip for input fields and pagination for both add friend & friend list .</p>
        </details>
        <details>
            <summary>3. Challenges Encountered</summary>
            <p>Aligning interactive components, validating form inputs, managing mutual friend counts, and PHP session handling.</p>
        </details>
        <details>
            <summary>4. Future Improvements</summary>
            <p>Implement secure password hashing, add direct messaging, improve accessibility, and add profile images.</p>
        </details>
        <details>
            <summary>5. Discussion Board Participation</summary>
            <p>I asked a question regarding the debugging lab ( lab 11) in the discussion board.</p>
            <img src="images/discussion.png" alt="Discussion board screenshot">
        </details>
    </div>

    <ul class="links">
        <li><a href="friendlist.php" class="button-73">Friend List</a></li>
        <li><a href="friendadd.php" class="button-73">Add Friends</a></li>
        <li><a href="index.php"      class="button-73">Home Page</a></li>
    </ul>

    <button class="button-73" onclick="location.href='index.php'">
        Return to Home Page
    </button>
</body>
</html>
