<?php
session_start();
require_once 'db_config.php';   // Database credentials

// 1. Redirect if not logged in
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit;
}

// 2. Connect
$mysqli = new mysqli($host, $user, $pswd, $dbnm);
if ($mysqli->connect_error) {
    die("Database connection error.");
}

// 3. Get current user ID and name
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

// 4. Count current number of friends
$stmt = $mysqli->prepare("
    SELECT COUNT(*) 
    FROM myfriends 
    WHERE friend_id1 = ?
");
$stmt->bind_param('i', $currentId);
$stmt->execute();
$stmt->bind_result($currentCount);
$stmt->fetch();
$stmt->close();

// 5. Handle “Add Friend”
if (isset($_GET['add'])) {
    $addId = (int)$_GET['add'];
    // insert relationship
    $stmt = $mysqli->prepare("
        INSERT IGNORE INTO myfriends (friend_id1, friend_id2)
        VALUES (?, ?)
    ");
    $stmt->bind_param('ii', $currentId, $addId);
    $stmt->execute();
    $stmt->close();
    // update count
    $stmt = $mysqli->prepare("
        UPDATE friends 
        SET num_of_friends = num_of_friends + 1 
        WHERE friend_id = ?
    ");
    $stmt->bind_param('i', $currentId);
    $stmt->execute();
    $stmt->close();
    // redirect back to this page preserving page number
    $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page']>1 
            ? (int)$_GET['page'] : 1;
    header('Location: friendadd.php?page=' . $page);
    exit;
}

// 6. Pagination setup
if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page']>1) {
    $page = (int)$_GET['page'];
} else {
    $page = 1;
}
$perPage = 5;
$offset  = ($page - 1) * $perPage;

// 7. Count total suggestions
$stmt = $mysqli->prepare("
    SELECT COUNT(*) 
    FROM friends f
    LEFT JOIN myfriends m 
      ON m.friend_id1 = ? AND m.friend_id2 = f.friend_id
    WHERE m.friend_id2 IS NULL
      AND f.friend_id <> ?
");
$stmt->bind_param('ii', $currentId, $currentId);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();
$totalPages = (int)ceil($total / $perPage);

// 8. Fetch suggestions for this page
$stmt = $mysqli->prepare("
    SELECT f.friend_id, f.profile_name
    FROM friends f
    LEFT JOIN myfriends m 
      ON m.friend_id1 = ? AND m.friend_id2 = f.friend_id
    WHERE m.friend_id2 IS NULL
      AND f.friend_id <> ?
    ORDER BY f.profile_name
    LIMIT ? OFFSET ?
");
$stmt->bind_param('iiii', $currentId, $currentId, $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();
$suggestions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 9. Compute mutual counts
foreach ($suggestions as &$u) {
    $stmt = $mysqli->prepare("
        SELECT COUNT(*) 
        FROM myfriends m1
        JOIN myfriends m2 
          ON m1.friend_id2 = m2.friend_id2
        WHERE m1.friend_id1 = ? 
          AND m2.friend_id1 = ?
    ");
    $stmt->bind_param('ii', $currentId, $u['friend_id']);
    $stmt->execute();
    $stmt->bind_result($mutual);
    $stmt->fetch();
    $stmt->close();
    $u['mutual'] = $mutual;
}
unset($u);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($currentName) ?>'s Add Friend Page</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
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
      background: linear-gradient(180deg,var(--dark),var(--navy));
      color: var(--text);
      font-family:'Segoe UI',sans-serif;
      display:flex; flex-direction:column; align-items:center;
      padding:2rem; min-height:100vh;
    }
    .container {
      width:100%; max-width:600px;
      background: linear-gradient(180deg,var(--navy),var(--mint));
      border-radius:var(--radius);
      box-shadow:0 4px 12px rgba(0,0,0,0.5);
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
    .pager {
      display:flex; justify-content:center; gap:1rem; margin-bottom:1rem;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="title">
      <h1>My Friend System</h1>
      <h2><?= htmlspecialchars($currentName) ?>'s Add Friend Page</h2>
    </div>
    <div class="count">
      Total number of friends is <?= $currentCount ?>
    </div>

    <table>
      <tr><th>Profile Name</th><th>Mutual Friends</th><th>Action</th></tr>
      <?php if (empty($suggestions)): ?>
        <tr><td colspan="3">No other users to add.</td></tr>
      <?php else: ?>
        <?php foreach ($suggestions as $u): ?>
          <tr>
            <td><?= htmlspecialchars($u['profile_name']) ?></td>
            <td><?= $u['mutual'] ?> mutual friends</td>
            <td class="actions">
              <a href="friendadd.php?add=<?= $u['friend_id'] ?>&page=<?= $page ?>"
                 class="button-73">Add as friend</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </table>

    <div class="pager">
      <?php if ($page > 1): ?>
        <a href="friendadd.php?page=<?= $page-1 ?>" class="button-73">Previous</a>
      <?php endif; ?>
      <?php if ($page < $totalPages): ?>
        <a href="friendadd.php?page=<?= $page+1 ?>" class="button-73">Next</a>
      <?php endif; ?>
    </div>

    <div class="pager">
      <a href="friendlist.php" class="button-73">Friend List</a>
      <a href="logout.php"    class="button-73">Log Out</a>
    </div>
  </div>
</body>
</html>
