<?php

use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Model\SendSmtpEmail;

// Carichiamo l'autoloader di Composer (include la libreria SendinBlue\Client)
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
// Creiamo un'istanza di Dotenv puntando alla directory del progetto
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
// Carichiamo le variabili d'ambiente dal file .env
$dotenv->load();

/**
 * Invia un'email transazionale usando Brevo (Sendinblue) API v3.
 * @param string $htmlBody  Corpo HTML dell'email (puoi anche aggiungere textContent)
 * @return bool             true se invio OK, false se errore
 */
function sendOtpWithBrevo($htmlBody) {
    // 1) Configurazione Brevo con la tua API Key
    // Trovi la key su: https://app.brevo.com/settings/keys/api
    // Ora puoi accedere alle variabili con $_ENV o $_SERVER
    $brevoApiKey= $_ENV['BREVO_API_KEY'];
    $brevoSender= $_ENV['BREVO_SENDER_EMAIL'];
    $toEmail= $_ENV['BREVO_TO_EMAIL'];
    $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $brevoApiKey);

    // 2) Creiamo l'istanza dell'API
    $apiInstance = new TransactionalEmailsApi(null, $config);

    // 3) Prepariamo l'oggetto della mail
    $email = new SendSmtpEmail([
        'to' => [[
            'email' => $toEmail,
            'name'  => 'Admin'  // se vuoi specificare un nome
        ]],
        'sender' => [
            'email' => $brevoSender, // deve essere un indirizzo "verificato" su Brevo
            'name'  => 'Pietro'
        ],
        'subject'     => 'Codice OTP brevo- Pizzeria',
        'htmlContent' => $htmlBody
        // Volendo puoi aggiungere "textContent" => "Versione testuale"
    ]);

    try {
        $apiInstance->sendTransacEmail($email);
        // Se arriva qui, l'invio dovrebbe essere OK
        return true;
    } catch (\Exception $e) {
        error_log('Errore invio email con Brevo: ' . $e->getMessage());
        return false;
    }
}
