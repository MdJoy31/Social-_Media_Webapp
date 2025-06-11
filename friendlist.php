<?php
session_start();  // Start session

require_once 'db_config.php';  // Database credentials

// Redirect if not logged in
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit;
}

// Connect to DB
$mysqli = new mysqli($host, $user, $pswd, $dbnm);
if ($mysqli->connect_error) {
    die("Database connection error.");
}

// Get current user ID and name
$stmt = $mysqli->prepare("
    SELECT friend_id, profile_name
    FROM friends
    WHERE friend_email = ?
");
$stmt->bind_param('s', $_SESSION['user_email']);
$stmt->execute();
$stmt->bind_result($currentId, $currentName);
$stmt->fetch();
$stmt->close();

// Pagination setup
$page            = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$friendsPerPage  = 5;
if ($page < 1) {
    $page = 1;
}

// Handle unfriend action
if (isset($_GET['unfriend'])) {
    $unfriendId = (int)$_GET['unfriend'];
    $stmt = $mysqli->prepare("
        DELETE FROM myfriends
        WHERE friend_id1 = ? AND friend_id2 = ?
    ");
    $stmt->bind_param('ii', $currentId, $unfriendId);
    $stmt->execute();
    $stmt->close();

    // Update friend count for current user
    $countStmt = $mysqli->prepare("
        SELECT COUNT(*) FROM myfriends
        WHERE friend_id1 = ?
    ");
    $countStmt->bind_param('i', $currentId);
    $countStmt->execute();
    $countStmt->bind_result($newCount);
    $countStmt->fetch();
    $countStmt->close();

    $updStmt = $mysqli->prepare("
        UPDATE friends
        SET num_of_friends = ?
        WHERE friend_id = ?
    ");
    $updStmt->bind_param('ii', $newCount, $currentId);
    $updStmt->execute();
    $updStmt->close();

    header('Location: friendlist.php?page=' . $page);
    exit;
}

// Get total number of friends
$countStmt = $mysqli->prepare("
    SELECT COUNT(*) FROM myfriends
    WHERE friend_id1 = ?
");
$countStmt->bind_param('i', $currentId);
$countStmt->execute();
$countStmt->bind_result($totalFriends);
$countStmt->fetch();
$countStmt->close();

$totalPages = (int)ceil($totalFriends / $friendsPerPage);
$offset     = ($page - 1) * $friendsPerPage;

// Fetch paginated friend list, sorted alphabetically
$stmt = $mysqli->prepare("
    SELECT f.friend_id, f.profile_name
    FROM myfriends m
    JOIN friends f
      ON m.friend_id2 = f.friend_id
    WHERE m.friend_id1 = ?
    ORDER BY f.profile_name
    LIMIT ? OFFSET ?
");
$stmt->bind_param('iii', $currentId, $friendsPerPage, $offset);
$stmt->execute();
$result       = $stmt->get_result();
$friendsList  = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Compute mutual friend counts
foreach ($friendsList as &$f) {
    $stmt = $mysqli->prepare("
        SELECT COUNT(*) FROM myfriends m1
        JOIN myfriends m2
          ON m1.friend_id2 = m2.friend_id2
        WHERE m1.friend_id1 = ?
          AND m2.friend_id1 = ?
    ");
    $stmt->bind_param('ii', $currentId, $f['friend_id']);
    $stmt->execute();
    $stmt->bind_result($mutual);
    $stmt->fetch();
    $stmt->close();
    $f['mutual'] = $mutual;
}
unset($f);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($currentName) ?>'s Friend List</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --dark:  #09101b;
            --navy:  #142d4c;
            --mint:  #9fd3c7;
            --text:  #e0e0e0;
            --light: #eef2f5;
            --radius:12px;
            --trans: .3s;
        }
        *,*::before,*::after {
            box-sizing:border-box; margin:0; padding:0;
        }
        body {
            background: linear-gradient(180deg, var(--dark), var(--navy));
            color: var(--text);
            font-family:'Segoe UI',sans-serif;
            display:flex; flex-direction:column; align-items:center;
            padding:2rem; min-height:100vh;
        }
        .container {
            width:100%; max-width:600px;
            background: linear-gradient(180deg, var(--navy), var(--mint));
            border-radius: var(--radius);
            box-shadow: 0 4px 12px rgba(0,0,0,0.5);
            padding:2rem;
        }
        .title {
            text-align:center; margin-bottom:1rem;
        }
        .title h1 { margin-bottom:.5rem; }
        .count { text-align:center; margin-bottom:1.5rem; }
        table {
            width:100%; border-collapse:collapse; margin-bottom:1rem;
        }
        th,td {
            border:1px solid var(--light); padding:.75rem;
        }
        th { background: rgba(255,255,255,0.1); }
        td.actions { text-align:center; }
        .button-73 {
            appearance:none;
            background-color:#FFFFFF;
            border-radius:40em;
            border:none;
            box-shadow:#ADCFFF 0 -12px 6px inset;
            color:#000;
            cursor:pointer;
            font-family:-apple-system,sans-serif;
            font-size:1rem;
            font-weight:700;
            padding:.75rem 1.5rem;
            text-align:center;
            transition:all var(--trans);
            margin:.25rem;
            text-decoration:none;
            display:inline-block;
        }
        .button-73:hover {
            background-color:#FFC229;
            box-shadow:#FF6314 0 -6px 8px inset;
            transform:scale(1.1);
        }
        .buttons {
            text-align:center;
        }
        .buttons a {
            margin:0 .5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">
            <h1>My Friend System</h1>
            <h2><?= htmlspecialchars($currentName) ?>'s Friend List</h2>
        </div>
        <div class="count">
            Total number of friends: <?= $totalFriends ?>
        </div>

        <table>
            <tr>
                <th>Friend</th>
                <th>Mutual Friends</th>
                <th>Action</th>
            </tr>
            <?php if (empty($friendsList)): ?>
                <tr>
                    <td colspan="3">You have no friends yet.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($friendsList as $f): ?>
                    <tr>
                        <td><?= htmlspecialchars($f['profile_name']) ?></td>
                        <td><?= $f['mutual'] ?> mutual friends</td>
                        <td class="actions">
                            <a href="friendlist.php?unfriend=<?= $f['friend_id'] ?>&page=<?= $page ?>"
                               class="button-73">Unfriend</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>

        <div class="buttons">
            <?php if ($page > 1): ?>
                <a href="friendlist.php?page=<?= $page - 1 ?>" class="button-73">Previous</a>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
                <a href="friendlist.php?page=<?= $page + 1 ?>" class="button-73">Next</a>
            <?php endif; ?>
        </div>

        <div class="buttons" style="margin-top:1rem;">
            <a href="friendadd.php" class="button-73">Add Friends</a>
            <a href="logout.php"    class="button-73">Log Out</a>
        </div>
    </div>
</body>
</html>
