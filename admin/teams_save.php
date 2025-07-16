<?php
require_once '../classes/Database.php';
require_once '../classes/Team.php'; // your Team class with add/update methods

session_start();

$db = Database::getInstance()->getConnection();
$team = new Team($db);

$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? '';
$coach = $_POST['coach'] ?? '';
$city = $_POST['city'] ?? '';

$uploadDir = 'uploads/teams/'; // make sure this folder exists and is writable

// Handle logo upload
$logoPath = null;
if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['logo']['tmp_name'];
    $fileName = basename($_FILES['logo']['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newFileName = uniqid('team_', true) . '.' . $fileExt;

    if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $destPath = $uploadDir . $newFileName;
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $logoPath = $destPath;
        } else {
            $_SESSION['error'] = 'Error moving uploaded file.';
            header('Location: teams_admin.php');
            exit;
        }
    } else {
        $_SESSION['error'] = 'Invalid file type for logo.';
        header('Location: teams_admin.php');
        exit;
    }
}

// If updating, keep existing logo if no new upload
if ($id && !$logoPath) {
    $existingTeam = $team->getById($id);
    $logoPath = $existingTeam['logo'] ?? null;
}

if ($id) {
    $success = $team->update($id, $name, $coach, $city, $logoPath);
    $_SESSION['message'] = $success ? 'Team updated successfully.' : 'Failed to update team.';
} else {
    $success = $team->add($name, $coach, $city, $logoPath);
    $_SESSION['message'] = $success ? 'Team added successfully.' : 'Failed to add team.';
}

header('Location: teams_admin.php');
exit;
