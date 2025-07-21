<?php
require_once '../classes/database.php';
require_once '../classes/team.php';

$db = Database::getInstance()->getConnection();
$team = new Team($db);

// Handle delete via GET (optional if needed here)
if (isset($_GET['delete'])) {
    $team->delete($_GET['delete']);
    header("Location: teams_admin.php");
    exit;
}

// Form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = $_POST['id'] ?? null;
    $name   = $_POST['name'] ?? '';
    $coach  = $_POST['coach'] ?? '';
    $city   = $_POST['city'] ?? '';
    $logo   = null;

    // Handle file upload
    if (!empty($_FILES['logo']['name'])) {
        $targetDir = '../uploads/teams/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $filename = time() . '_' . basename($_FILES['logo']['name']);
        $targetFile = $targetDir . $filename;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetFile)) {
            $logo = $filename;
        }
    }

    // Perform add or update
    if ($id) {
        $team->update($id, $name, $coach, $city, $logo);
    } else {
        $team->add($name, $coach, $city, $logo);
    }

    header("Location: teams_admin.php");
    exit;
}
?>
