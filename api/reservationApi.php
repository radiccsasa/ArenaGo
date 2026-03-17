<?php
session_start();
header('Content-Type: application/json');
require_once "../DB/db.config.php";

$method = $_POST['methodName'];


function reserve()
{
    global $conn;

    if (isset($_SESSION['user'])) {
        if ($_SESSION['user']['role'] != 'user') {
            echo json_encode([
                'status' => 'error',
                'message' => 'Nemate pristup'
            ]);
            return;
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Morate da se ulogujete za ovu mogucnost'
        ]);
        return;
    }

    $termId = $_POST['termId'];
    $userId = $_SESSION['user']['id'];

    $approved_check = "SELECT id FROM reservations WHERE term_id = $termId AND status = 'approved'";
    $approved_result = mysqli_query($conn, $approved_check);
    
    if (mysqli_num_rows($approved_result) > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Ovaj termin je već rezervisan od strane drugog korisnika'
        ]);
        return;
    }

    $check_query = "SELECT id FROM reservations WHERE term_id = $termId AND user_id = $userId";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Već ste poslali zahtev za zakazivanje, molim vas sacekajte.'
        ]);
        return;
    }

    // Ako nema rezervacije, onda insertuj
    $query = "INSERT INTO reservations (term_id, user_id, status, created_at) 
        VALUES ($termId, $userId, 'pending', NOW());";

    if (mysqli_query($conn, $query)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Uspešno rezervisan termin'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Greska prilikom rezervisanja'
        ]);
    }
}

function updateReservationStatus()
{
    global $conn;

    if (!isset($_SESSION['user']['id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Niste prijavljeni'
        ]);
        return;
    }

    $userId = $_SESSION['user']['id'];
    $reservationId = isset($_POST['reservation_id']) ? (int)$_POST['reservation_id'] : 0;
    $status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : '';

    // Validacija ID-ja
    if ($reservationId == 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'ID rezervacije nije prosleđen'
        ]);
        return;
    }

    // Validacija statusa
    if (!in_array($status, ['approved', 'rejected'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Neispravan status'
        ]);
        return;
    }

    // Provera da li rezervacija pripada centru ovog korisnika
    $checkQuery = "SELECT r.id, r.term_id, r.user_id, r.status as trenutni_status
                   FROM reservations r
                   INNER JOIN terms t ON r.term_id = t.id
                   INNER JOIN sports_centers sc ON t.center_id = sc.id
                   WHERE r.id = $reservationId AND sc.user_id = $userId";

    $checkResult = mysqli_query($conn, $checkQuery);

    if (!$checkResult) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Greška u bazi: ' . mysqli_error($conn)
        ]);
        return;
    }

    if (mysqli_num_rows($checkResult) == 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Nemate pravo da menjate ovu rezervaciju'
        ]);
        return;
    }

    $reservation = mysqli_fetch_assoc($checkResult);

    // Provera da li je rezervacija već obrađena
    if ($reservation['trenutni_status'] !== 'pending') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Rezervacija je već obrađena'
        ]);
        return;
    }

    // Ažuriraj status rezervacije
    $updateQuery = "UPDATE reservations SET status = '$status' WHERE id = $reservationId";

    if (mysqli_query($conn, $updateQuery)) {

        // Ako je rezervacija prihvaćena, možeš dodati neku dodatnu logiku
        if ($status === 'approved') {
            // Na primer: smanji broj dostupnih mesta za termin
            $updateTermQuery = "UPDATE terms SET available_spots = available_spots - 1 WHERE id = " . $reservation['term_id'];
            mysqli_query($conn, $updateTermQuery); // Ne moraš proveravati grešku, opciono je
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Status rezervacije uspešno ažuriran'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Greška pri ažuriranju: ' . mysqli_error($conn)
        ]);
    }
}

switch ($method) {
    case 'reserve':
        reserve();
        break;
    case 'updateReservationStatus':
        updateReservationStatus();
        break;
}
