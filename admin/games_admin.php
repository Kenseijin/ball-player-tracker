<?php
require_once '../includes/header.php';
require_once '../classes/database.php';
require_once '../classes/team.php';
require_once '../classes/game.php';

$db = Database::getInstance()->getConnection();
$team = new Team($db);
$game = new Game($db);

$teams = $team->getAll();
$games = $game->getAll();

$editing = null;
if (isset($_GET['edit'])) {
    $editing = $game->getById($_GET['edit']);
}

if (isset($_GET['delete'])) {
    $game->delete($_GET['delete']);
    header("Location: games_admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Games Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Games Management</h1>
        <button onclick="toggleForm()" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">+ Schedule Game</button>
    </div>

    <!-- Floating Form -->
    <div id="formModal" class="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-start pt-20 hidden z-50">
        <div class="bg-white w-full max-w-xl p-6 rounded shadow relative">
            <button onclick="toggleForm()" class="absolute top-2 right-4 text-gray-600 hover:text-red-500 text-2xl">×</button>
            <form action="games_save.php" method="POST" class="space-y-4">
                <?php if ($editing): ?>
                    <input type="hidden" name="id" value="<?= $editing['id'] ?>">
                <?php endif; ?>

                <!-- Teams Select with VS -->
                <div class="flex items-center justify-between gap-4">
                    <!-- Home Team -->
                    <div class="flex-1">
                        <label class="block font-semibold mb-1">Home Team</label>
                        <select name="home_team_id" required class="w-full border px-3 py-2 rounded">
                            <option value="">Select Team</option>
                            <?php foreach ($teams as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($editing && $editing['home_team_id'] == $t['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- VS Divider -->
                    <div class="text-center text-xl font-extrabold text-white bg-gray-800 px-4 py-1 rounded-full shadow-md">VS</div>

                    <!-- Away Team -->
                    <div class="flex-1">
                        <label class="block font-semibold mb-1">Away Team</label>
                        <select name="away_team_id" required class="w-full border px-3 py-2 rounded">
                            <option value="">Select Team</option>
                            <?php foreach ($teams as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($editing && $editing['away_team_id'] == $t['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Game Date -->
                <div>
                    <label class="block font-semibold mb-1">Game Date</label>
                    <input type="datetime-local" name="game_date" required value="<?= $editing['game_date'] ?? '' ?>" class="w-full border px-3 py-2 rounded">
                </div>

                <div class="text-right">
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700"><?= $editing ? 'Update' : 'Add' ?> Game</button>
                    <?php if ($editing): ?>
                        <a href="games_admin.php" class="ml-4 text-gray-600 hover:underline">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Games List -->
    <h2 class="text-xl font-bold mt-8 mb-4">Scheduled Games</h2>
<table class="w-full border-collapse border border-gray-300 text-sm">
    <thead>
        <tr class="bg-gray-100">
            <th class="p-3 border">Date</th>
            <th class="p-3 border">Match</th>
            <th class="p-3 border">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($games as $g): ?>
            <tr class="border hover:bg-gray-50">
                <td class="p-2"><?= date('M d, Y H:i', strtotime($g['game_date'])) ?></td>
                <td class="p-2">
        <div class="flex items-center justify-center gap-2">
        <span class="flex-1 text-right"><?= htmlspecialchars($team->getNameById($g['home_team_id'])) ?></span>
        <span class="bg-gray-800 text-white px-2 py-0.5 rounded text-sm">VS</span>
        <span class="flex-1 text-left"><?= htmlspecialchars($team->getNameById($g['away_team_id'])) ?></span>
        </div>
                </td>

                <td class="p-2 whitespace-nowrap">
                    <a href="?edit=<?= $g['id'] ?>" class="text-blue-600 hover:underline mr-3">Edit</a>
                    <a href="?delete=<?= $g['id'] ?>" onclick="return confirm('Delete this game?')" class="text-red-600 hover:underline mr-3">Delete</a>
                    <a href="score_entry.php?id=<?= $g['id'] ?>" class="text-orange-600 font-bold hover:underline">Start Game</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</div>

<script>
    function toggleForm() {
        document.getElementById('formModal').classList.toggle('hidden');
    }
    // Auto-open form if editing
    <?php if ($editing): ?>
        window.addEventListener('DOMContentLoaded', () => {
            toggleForm();
        });
    <?php endif; ?>
</script>
</body>
</html>
