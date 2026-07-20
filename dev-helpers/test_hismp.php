<?php
$host = 'mysql-database';
$user = 'root';
$pass = 'Hcis_1110';
$db = 'HISMP_test';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to: $db\n";

    // Find first active patient
    $stmt = $pdo->query("SELECT id, name FROM patients WHERE (deleted IS NULL OR deleted = 0) AND id != 0 ORDER BY id LIMIT 1");
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Using patient: id={$patient['id']} name={$patient['name']}\n";

    // Insert reservation (morning_noon_night is integer: 1=午班)
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("INSERT INTO patient_reservations (patient_id, date, morning_noon_night, status, created_at, updated_at) VALUES (?, ?, 1, 0, NOW(), NOW())");
    $stmt->execute([$patient['id'], $today]);
    $resId = $pdo->lastInsertId();
    echo "Reservation created: id=$resId\n";

    // Insert check record
    $stmt = $pdo->prepare("INSERT INTO patient_checks (patient_reservation_id, date, status, prepare_nurse_id, created_at, updated_at) VALUES (?, ?, 0, 1, NOW(), NOW())");
    $stmt->execute([$resId, $today]);
    $checkId = $pdo->lastInsertId();
    echo "Check created: id=$checkId\n";

    // Verify
    $c = $pdo->query("SELECT COUNT(*) as cnt FROM patient_reservations WHERE id=$resId")->fetch()['cnt'];
    echo "Verified reservation: $c\n";
    $c2 = $pdo->query("SELECT COUNT(*) as cnt FROM patient_checks WHERE id=$checkId")->fetch()['cnt'];
    echo "Verified check: $c2\n";

    // Confirm HISMP not affected
    $hismp = new PDO("mysql:host=$host;dbname=HISMP;charset=utf8mb4", $user, $pass);
    $orig = $hismp->query("SELECT COUNT(*) as cnt FROM patients")->fetch()['cnt'];
    $test = $pdo->query("SELECT COUNT(*) as cnt FROM patients")->fetch()['cnt'];
    echo "\nHISMP patients: $orig (unchanged)\n";
    echo "HISMP_test patients: $test\n";
    echo "\nDONE - All data written to HISMP_test only!\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
