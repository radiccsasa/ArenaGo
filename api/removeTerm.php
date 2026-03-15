<?php
session_start();
header('Content-Type: application/json');
require_once "../DB/db.config.php";
function deleteTerm()
{
    global $conn;

    // Provera sesije
    if (!isset($_SESSION['user']['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Niste prijavljeni']);
        return;
    }

    $userId = $_SESSION['user']['id'];
    $termId = $_GET['termId'];

    if ($termId == 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID termina nije prosleđen']);
        return;
    }

    // Proveri da li termin pripada centru ovog korisnika
    $checkQuery = "SELECT t.id FROM terms t
                   INNER JOIN sports_centers sc ON t.center_id = sc.id
                   WHERE t.id = $termId AND sc.user_id = $userId";

    $checkResult = mysqli_query($conn, $checkQuery);

    if (!$checkResult || mysqli_num_rows($checkResult) == 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Nemate pravo da obrišete ovaj termin'
        ]);
        return;
    }

    // START TRANSACTION - sve ili ništa
    mysqli_begin_transaction($conn);

    try {
        // Prvo obriši sve rezervacije za ovaj termin
        $deleteReservations = "DELETE FROM reservations WHERE term_id = $termId";
        if (!mysqli_query($conn, $deleteReservations)) {
            throw new Exception('Greška pri brisanju rezervacija: ' . mysqli_error($conn));
        }

        // Zatim obriši termin
        $deleteTerm = "DELETE FROM terms WHERE id = $termId";
        if (!mysqli_query($conn, $deleteTerm)) {
            throw new Exception('Greška pri brisanju termina: ' . mysqli_error($conn));
        }

        // Ako je sve prošlo OK, potvrdi transakciju
        mysqli_commit($conn);

        echo json_encode([
            'status' => 'success',
            'message' => 'Termin i sve rezervacije su uspešno obrisani'
        ]);
    } catch (Exception $e) {
        // Ako je došlo do greške, poništi sve
        mysqli_rollback($conn);

        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}
deleteTerm();
header("Location: /ArenaGo/pages/center/dashboard-center.php");
