<?php
session_start();

require __DIR__ . '/../mail/mail_brevo.php';
require __DIR__ . '/../mail/mail_gmail.php';

define('CREDENTIALS', [
    'username' => 'admin',
    'password' => 'pass'
]);

define('OTP_EXPIRATION', 180); // 3 minuti (180 secondi)

/**
 * Gestisce il login con username/password e genera due OTP con timestamp.
 */
function handleLogin($username, $password) {
    if ($username === CREDENTIALS['username'] && $password === CREDENTIALS['password']) {
        echo "Login success!<br>";
        $codeBrevo = random_int(100000, 999999);
        $codeGmail = random_int(100000, 999999);
        $_SESSION['2fa_code_brevo'] = $codeBrevo;
        $_SESSION['2fa_code_gmail'] = $codeGmail;
        $_SESSION['2fa_timestamp'] = time();
        $_SESSION['2fa_pending'] = true;


        $htmlBodyBrevo = "<h1>Il tuo codice OTP è: <strong>$codeBrevo</strong></h1><p>Scade in 3 minuti.</p>";
        sendOtpWithBrevo($htmlBodyBrevo);
        $htmlBodyGmail = "<h1>Il tuo codice OTP è: <strong>$codeGmail</strong></h1><p>Scade in 3 minuti.</p>";
        sendOtpWithGmail($htmlBodyGmail);
        header('Location: /pizzeria_vanilla/?verify=1');
        exit;
    }
    echo "Login failed!";
    return false;
}


/**
 * Verifica entrambi i codici OTP (Brevo e Gmail)
 * @return bool True se entrambi i codici sono corretti, False altrimenti.
 */
function verifyOtp($otpBrevo, $otpGmail) {
    if (!isset($_SESSION['2fa_code_brevo'], $_SESSION['2fa_code_gmail'], $_SESSION['2fa_timestamp'])) {
        return false;
    }

    // Controlliamo se l'OTP è scaduto
    if (time() - $_SESSION['2fa_timestamp'] > OTP_EXPIRATION) {
        unset($_SESSION['2fa_code_brevo'], $_SESSION['2fa_code_gmail'], $_SESSION['2fa_timestamp'], $_SESSION['2fa_pending']);
        return false; // OTP scaduto
    }

    // Entrambi i codici devono essere corretti
    if ($otpBrevo == $_SESSION['2fa_code_brevo'] && $otpGmail == $_SESSION['2fa_code_gmail']) {
        $_SESSION['logged_in'] = true;
        unset($_SESSION['2fa_code_brevo'], $_SESSION['2fa_code_gmail'], $_SESSION['2fa_timestamp'], $_SESSION['2fa_pending']);
        return true;
    }

    return false;
}

/**
 * Gestisce la richiesta di verifica OTP.
 * @return string|null Messaggio di errore se fallisce, null se successo.
 */
function handleOtpVerification() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
        $otpBrevo = $_POST['otp_code_brevo'] ?? '';
        $otpGmail = $_POST['otp_code_gmail'] ?? '';

        if (verifyOtp($otpBrevo, $otpGmail)) {
            header('Location: /pizzeria_vanilla/?admin=true');
            exit;
        } else {
            return "Uno o entrambi i codici OTP sono errati o scaduti!";
        }
    }
    return null;
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
