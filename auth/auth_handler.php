<?php
session_start();

 require __DIR__ . '/../mail/mail_brevo.php';

define('CREDENTIALS', [
    'username' => 'admin',
    'password' => 'pass'
]);

define('OTP_EXPIRATION', 180); // 3 minuti (180 secondi)

/**
 * Gestisce il login con username/password e genera l'OTP con timestamp.
 */
function handleLogin($username, $password) {
    if ($username === CREDENTIALS['username'] && $password === CREDENTIALS['password']) {
        // Generiamo l'OTP e salviamo il timestamp attuale
        $code = random_int(100000, 999999);
        $_SESSION['2fa_code'] = $code;
        $_SESSION['2fa_timestamp'] = time(); // Salviamo il tempo attuale
        $_SESSION['2fa_pending'] = true;

        // Invia l'OTP via email
        $body = "<h1>Il tuo codice di verifica: $code</h1><p>Scade in 3 minuti.</p>";
        sendOtpWithBrevo($body);
        return ;
    }
    return false;
}

/**
 * Verifica il codice OTP inserito e controlla se è scaduto.
 */
function verifyOtp($userCode) {
    if (!isset($_SESSION['2fa_code']) || !isset($_SESSION['2fa_timestamp'])) {
        return false;
    }

    // Controlliamo se l'OTP è scaduto
    if (time() - $_SESSION['2fa_timestamp'] > OTP_EXPIRATION) {
        unset($_SESSION['2fa_code'], $_SESSION['2fa_timestamp'], $_SESSION['2fa_pending']);
        return false; // OTP scaduto
    }

    // Verifica il codice
    if ($userCode == $_SESSION['2fa_code']) {
        $_SESSION['logged_in'] = true;
        unset($_SESSION['2fa_code'], $_SESSION['2fa_timestamp'], $_SESSION['2fa_pending']);
        return true;
    }

    return false;
}

/**
 * Controlla se l'utente è autenticato.
 */
function isAuthenticated() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Gestisce il logout.
 */
function handleLogout() {
    session_destroy();
    header('Location: /pizzeria_vanilla/');
    exit;
}



