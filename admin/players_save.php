<?php
require_once '../classes/Database.php';
require_once '../classes/player.php';

session_start();

$db = Database::getInstance()->getConnection();
$player = new Player($db);

$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? '';
$age = $_POST['age'] ?? null;
$position = $_POST['position'] ?? '';
$jersey_number = $_POST['jersey_number'] ?? null;
$team_id = $_POST['team_id'] ?? null;

$uploadDir = '../uploads/players/';
$photoPath = null;

// Handle upload
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['photo']['tmp_name'];
    $fileName = basename($_FILES['photo']['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newFileName = uniqid('player_', true) . '.' . $fileExt;

    if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $destPath = $uploadDir . $newFileName;
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $photoPath = $newFileName;
        } else {
            $_SESSION['error'] = 'Error moving uploaded file.';
            header('Location: players_admin.php');
            exit;
        }
    } else {
        $_SESSION['error'] = 'Invalid file type.';
        header('Location: players_admin.php');
        exit;
    }
}

// If no new photo, keep old one
if ($id && !$photoPath) {
    $existingPlayer = $player->getById($id);
    $photoPath = $existingPlayer['photo'] ?? null;
}

// Save
if ($id) {
    $success = $player->update($id, $name, $age, $position, $jersey_number, $photoPath, $team_id);
    $_SESSION['message'] = $success ? 'Player updated successfully.' : 'Failed to update player.';
} else {
    $success = $player->add($name, $age, $position, $jersey_number, $photoPath, $team_id);
    $_SESSION['message'] = $success ? 'Player added successfully.' : 'Failed to add player.';
}

header('Location: players_admin.php');
exit;
?>