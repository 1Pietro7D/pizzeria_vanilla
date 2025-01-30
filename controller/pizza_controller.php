<?php
// Includiamo la logica di lettura/scrittura JSON
require_once __DIR__ . '/../fs_db_json/json_manager.php';

define ('model' , 'pizze');

function loadAllPizze(){
    return loadData(model);
}
function readPizza($id) {
    $pizze = loadData('pizze');
    $pizze = array_filter($pizze, function($pizza) use ($id) {
        return $pizza['id'] === $id;
    });
    saveData(model, $pizze);
}

/**
 * Aggiunge una nuova pizza, gestendo l'upload dell'immagine.
 */
function addPizza($nome, $ingredienti, $prezzo_normale, $prezzo_maxi, $file) {
    $pizze = loadData(model);
    $imagePath = '';

    // Se è stato caricato un file immagine valido (UPLOAD_ERR_OK)
    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        $imagePath = UPLOAD_DIR . basename($file['name']);
        move_uploaded_file($file['tmp_name'], $imagePath);
    }

    // Creiamo la nuova pizza
    $pizze[] = [
        'id' => uniqid(), // Genera un ID univoco
        'nome' => $nome,
        'ingredienti' => $ingredienti,
        'prezzo_normale' => $prezzo_normale,
        'prezzo_maxi' => $prezzo_maxi,
        'immagine' => $imagePath
    ];

    saveData(model, $pizze);
}

/**
 * Aggiorna una pizza esistente (cerca per id).
 * Se carichiamo una nuova immagine, sostituisce la precedente.
 */
function updatePizza($id, $nome, $ingredienti, $prezzo_normale, $prezzo_maxi, $file) {
    $pizze = loadData(model);
    
    foreach ($pizze as &$pizza) {
        if (isset($pizza) && $pizza['id'] === $id) {
            // Se c’è un nuovo file caricato
            if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
                $newImagePath = UPLOAD_DIR . basename($file['name']);
                move_uploaded_file($file['tmp_name'], $newImagePath);

                // Aggiorniamo il campo immagine
                $pizza['immagine'] = $newImagePath;
            }
            // Aggiorniamo gli altri campi
            $pizza['nome'] = $nome;
            $pizza['ingredienti'] = $ingredienti;
            $pizza['prezzo_normale'] = $prezzo_normale;
            $pizza['prezzo_maxi'] = $prezzo_maxi;
            break;
        }
    }
    
    saveData(model, $pizze);
}


/**
 * Elimina una pizza in base all'id.
 */
function deletePizza($id) {
    $pizze = loadData(model);

    // Controlliamo e cancelliamo l'immagine prima di rimuovere la pizza
    foreach ($pizze as $pizza) {
        if ($pizza['id'] === $id) {
            if (!empty($pizza['immagine']) && file_exists($pizza['immagine'])) {
                unlink($pizza['immagine']); // Cancella il file immagine
            }
            break; // Una volta trovata e cancellata l'immagine, usciamo dal ciclo
        }
    }
    
    $pizze = array_filter($pizze, function($pizza) use ($id) {
        return $pizza['id'] !== $id;
    });
    saveData(model, $pizze);
}
