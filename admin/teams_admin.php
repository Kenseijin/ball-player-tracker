<?php
require_once '../includes/header.php';
require_once '../classes/database.php';
require_once '../classes/team.php';

$db = Database::getInstance()->getConnection();
$team = new Team($db);

// Handle search
$search = $_GET['search'] ?? '';
$teams = $search ? $team->searchByName($search) : $team->getAll();

// Edit or delete
$editing = isset($_GET['edit']) ? $team->getById($_GET['edit']) : null;
if (isset($_GET['delete'])) {
    if ($team->hasPlayers($_GET['delete'])) {
        echo "<script>alert('Cannot delete: Team has assigned players.'); window.location='teams_admin.php';</script>";
        exit;
    }
    $team->delete($_GET['delete']);
    header('Location: teams_admin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Team Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<div class="max-w-6xl mx-auto bg-white p-6 rounded shadow relative">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Team Management</h1>
        <button onclick="toggleForm()" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">+ Add Team</button>
    </div>

    <!-- Filter/Search -->
    <form method="GET" class="mb-4 flex items-center space-x-4">
        <input
            type="text"
            name="search"
            placeholder="Search team name..."
            value="<?= htmlspecialchars($search) ?>"
            class="border px-3 py-2 rounded w-64"
        />
        <button class="bg-blue-600 text-white px-4 py-1.5 rounded hover:bg-blue-700">Search</button>
        <?php if ($search): ?>
            <a href="teams_admin.php" class="text-red-600 hover:underline">Clear</a>
        <?php endif; ?>
    </form>

    <!-- Floating Form Modal -->
    <div id="formModal" class="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-start pt-24 <?= $editing ? '' : 'hidden' ?> z-50">
        <div class="bg-white w-full max-w-xl p-6 rounded shadow relative">
            <button onclick="toggleForm()" class="absolute top-2 right-4 text-gray-600 hover:text-red-500 text-2xl">×</button>
            <form action="teams_save.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <?php if ($editing): ?>
                    <input type="hidden" name="id" value="<?= $editing['id'] ?>">
                <?php endif; ?>

                <div>
                    <label class="block font-semibold mb-1">Team Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="<?= htmlspecialchars($editing['name'] ?? '') ?>" required class="w-full border px-3 py-2 rounded">
                </div>

                <div>
                    <label class="block font-semibold mb-1">Coach</label>
                    <input type="text" name="coach" value="<?= htmlspecialchars($editing['coach'] ?? '') ?>" class="w-full border px-3 py-2 rounded">
                </div>

                <div>
                    <label class="block font-semibold mb-1">City</label>
                    <input type="text" name="city" value="<?= htmlspecialchars($editing['city'] ?? '') ?>" class="w-full border px-3 py-2 rounded">
                </div>

                <div>
                    <label class="block font-semibold mb-1">Logo</label>
                    <input type="file" name="logo" accept="image/*" class="w-full">
                    <?php if (!empty($editing['logo']) && file_exists("../uploads/teams/" . $editing['logo'])): ?>
                        <div class="mt-2">
                            <img src="../uploads/teams/<?= $editing['logo'] ?>" class="w-24 h-24 object-cover rounded border cursor-pointer" onclick="toggleLogo(this.src)">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="text-right">
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                        <?= $editing ? 'Update' : 'Add' ?> Team
                    </button>
                    <?php if ($editing): ?>
                        <a href="teams_admin.php" class="ml-4 text-gray-600 hover:underline">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Team Table -->
    <h2 class="text-xl font-bold mt-8 mb-4">Team List <?= $search ? '(Filtered)' : '' ?></h2>
    <table class="w-full border-collapse border border-gray-300 text-sm">
        <thead>
        <tr class="bg-gray-200">
            <th class="p-3 border">Logo</th>
            <th class="p-3 border">Name</th>
            <th class="p-3 border">Coach</th>
            <th class="p-3 border">City</th>
            <th class="p-3 border">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($teams as $t): ?>
            <tr class="border hover:bg-gray-50">
                <td class="p-2 text-center">
                    <?php if (!empty($t['logo']) && file_exists("../uploads/teams/" . $t['logo'])): ?>
                        <img src="../uploads/teams/<?= $t['logo'] ?>" class="w-14 h-14 object-cover rounded cursor-pointer mx-auto" onclick="toggleLogo(this.src)">
                    <?php else: ?>
                        <span class="text-gray-400">No logo</span>
                    <?php endif; ?>
                </td>
                <td class="p-2"><?= htmlspecialchars($t['name']) ?></td>
                <td class="p-2"><?= htmlspecialchars($t['coach']) ?></td>
                <td class="p-2"><?= htmlspecialchars($t['city']) ?></td>
                <td class="p-2 whitespace-nowrap">
                    <a href="?edit=<?= $t['id'] ?>" class="text-blue-600 hover:underline mr-3">Edit</a>
                    <a href="?delete=<?= $t['id'] ?>" onclick="return confirm('Are you sure you want to delete this team?')" class="text-red-600 hover:underline">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal for logo preview -->
<div id="logoModal" class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center hidden z-50">
    <img id="modalLogo" src="" class="max-w-3xl max-h-[90vh]" />
    <span onclick="closeLogo()" class="absolute top-4 right-6 text-white text-3xl cursor-pointer select-none">×</span>
</div>

<script>
    function toggleForm() {
        document.getElementById('formModal').classList.toggle('hidden');
    }
    function toggleLogo(src) {
        document.getElementById('modalLogo').src = src;
        document.getElementById('logoModal').classList.remove('hidden');
    }
    function closeLogo() {
        document.getElementById('logoModal').classList.add('hidden');
    }
</script>
</body>
</html>
