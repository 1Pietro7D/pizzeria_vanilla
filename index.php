<?php
require_once __DIR__ . '/auth/auth_handler.php';
require_once __DIR__ . '/controller/pizza_controller.php';


// 1) LOGOUT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    handleLogout();
    header('Location: /pizzeria_vanilla/');
    exit;
}

// 2) LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (handleLogin($username, $password)) {
        header('Location: /pizzeria_vanilla/?verify=1');
        exit;
    }
}

// 3) Verifica OTP
if (isset($_GET['verify']) && ($_SESSION['2fa_pending'] ?? false)) {
    ?>
    <h1>Verifica Codice OTP</h1>
    <?php if ($error) echo '<p style="color:red;">' . htmlspecialchars($error) . '</p>'; ?>
    <form method="POST">
        <label>Codice OTP Brevo:</label><br>
        <input type="text" name="otp_code_brevo" maxlength="6" required><br>

        <label>Codice OTP Gmail:</label><br>
        <input type="text" name="otp_code_gmail" maxlength="6" required><br>

        <button type="submit" name="verify_otp">Verifica</button>
    </form>
    <?php
    exit;
}

// 4) Pagina pubblica
if (!isset($_GET['admin'])) {
    $pizze = loadAllPizze();
    echo '<h1>Pizza Menu</h1><ul>';
    foreach ($pizze as $pizza) {
        echo '<li>';
        echo '<strong>' . htmlspecialchars($pizza['nome']) . '</strong><br>';
        echo 'Ingredients: ' . htmlspecialchars($pizza['ingredienti']) . '<br>';
        echo 'Normal Price: ' . htmlspecialchars($pizza['prezzo_normale']) . '€<br>';
        echo 'Maxi Price: ' . htmlspecialchars($pizza['prezzo_maxi']) . '€<br>';
        if (!empty($pizza['immagine'])) {
            echo '<img src="' . htmlspecialchars($pizza['immagine']) . '" style="width:150px;"><br>';
        }
        echo '<hr></li>';
    }
    echo '</ul>';
    echo '<a href="?admin=true">Admin Login</a>';
    exit;
}

// 5) Form di login
if (!isAuthenticated()) {
    ?>
    <h1>Admin Login</h1>
    <?php if ($error) echo '<p style="color:red;">' . htmlspecialchars($error) . '</p>'; ?>
    <form method="POST">
        <label>Username</label><br>
        <input type="text" name="username" required><br><br>
        <label>Password</label><br>
        <input type="password" name="password" required><br><br>
        <button type="submit" name="login">Login</button>
    </form>
    <?php
    exit;
}

// 6) CRUD gestione pizze
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        addPizza($_POST['nome'], $_POST['ingredienti'], $_POST['prezzo_normale'], $_POST['prezzo_maxi'], $_FILES['immagine'] ?? null);
    } elseif ($_POST['action'] === 'update') {
        updatePizza($_POST['id'], $_POST['nome'], $_POST['ingredienti'], $_POST['prezzo_normale'], $_POST['prezzo_maxi'], $_FILES['immagine'] ?? null);
    } elseif ($_POST['action'] === 'delete') {
        deletePizza($_POST['id']);
    }
}

// Carichiamo di nuovo le pizze
$pizze = loadAllPizze();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Panel con 2FA Email</title>
</head>
<body>
<h1>Admin Panel</h1>

<h2>Add New Pizza</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="add">
    <input type="text" name="nome" placeholder="Pizza Name" required><br>
    <textarea name="ingredienti" placeholder="Ingredients" required></textarea><br>
    <input type="number" step="0.01" name="prezzo_normale" placeholder="Normal Price" required><br>
    <input type="number" step="0.01" name="prezzo_maxi" placeholder="Maxi Price" required><br>
    <input type="file" name="immagine" accept="image/*" required><br>
    <button type="submit">Add Pizza</button>
</form>

<h2>Pizza List</h2>
<ul>
    <?php foreach ($pizze as $pizza): ?>
    <li>
        <strong><?= htmlspecialchars($pizza['nome']) ?></strong><br>
        Ingredients: <?= htmlspecialchars($pizza['ingredienti']) ?><br>
        Normal Price: <?= htmlspecialchars($pizza['prezzo_normale']) ?>€<br>
        Maxi Price: <?= htmlspecialchars($pizza['prezzo_maxi']) ?>€<br>
        <?php if (!empty($pizza['immagine'])): ?>
            <img src="<?= htmlspecialchars($pizza['immagine']) ?>" style="width:150px;"><br>
        <?php endif; ?>
        <a href="?admin=true&edit_id=<?= $pizza['id'] ?>">Edit</a>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $pizza['id'] ?>">
            <button type="submit">Delete</button>
        </form>
    </li>
    <?php endforeach; ?>
</ul>

<form method="POST">
    <button type="submit" name="logout">Logout</button>
</form>
</body>
</html>
