<?php

// Cartella dove caricare le immagini
define('UPLOAD_DIR', 'uploads/');

// Se la cartella uploads non esiste, la creiamo (con 0777)
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

/********************************************
 * FUNZIONI GENERICHE PER I FILE JSON
 ********************************************/

/**
 * Carica i dati di un “model” (es. 'pizze', 'bevande') da un file JSON
 * che avrà lo stesso nome del model (es. pizze.json, bevande.json).
 *
 * - Se il file non esiste, viene creato vuoto.
 * - Ritorna un array associativo con i dati.
 *
 * @param string $model
 * @return array
 */
function loadData($model) {
    // Il file .json si troverà nello stesso folder di questo script
    $jsonFile = __DIR__ . "/{$model}.json";

    // Se non esiste, crea un file vuoto (array [])
    if (!file_exists($jsonFile)) {
        file_put_contents($jsonFile, json_encode([]));
    }

    // Leggiamo e decodifichiamo in un array
    return json_decode(file_get_contents($jsonFile), true);
}

/**
 * Salva i dati di un “model” nel corrispondente file JSON
 * (es. 'pizze' -> 'pizze.json')
 *
 * @param string $model
 * @param array  $data
 * @return void
 */
function saveData($model, $data) {
    $jsonFile = __DIR__ . "/{$model}.json";

    // Salvataggio in modalità “pretty print” (leggibile)
    file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
}

/********************************************
 * Esempio di funzione generica per l'upload
 * (se ti serve gestire i file caricati).
 ********************************************/
/**
 * Carica un file in UPLOAD_DIR e restituisce il percorso finale
 * (o stringa vuota se non c'è file).
 *
 * @param array $file Arrivato da $_FILES['qualcosa']
 * @return string Percorso nel filesystem (relativo a uploads)
 */
function uploadFile($file) {
    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        // Costruiamo il path di destinazione
        $targetPath = UPLOAD_DIR . basename($file['name']);
        move_uploaded_file($file['tmp_name'], $targetPath);
        return $targetPath;
    }
    return '';
}