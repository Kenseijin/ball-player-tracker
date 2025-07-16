<?php
require_once '../includes/header.php';
require_once '../classes/database.php';
require_once '../classes/player.php';
require_once '../classes/team.php';

$db = Database::getInstance()->getConnection();
$player = new Player($db);
$team = new Team($db);

$teams = $team->getAll();

$filter_team_id = $_GET['team_id'] ?? '';

// Fetch players either filtered or all
$players = $filter_team_id ? $player->getByTeam($filter_team_id) : $player->getAll();

if (isset($_GET['delete'])) {
    $player->delete($_GET['delete']);
    header('Location: players_admin.php');
    exit;
}

$editing = isset($_GET['edit']) ? $player->getById($_GET['edit']) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Player Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-3xl font-bold mb-6">Player Management</h1>

    <!-- Team filter -->
    <form method="GET" class="mb-6 flex items-center space-x-4">
        <label for="teamFilter" class="font-semibold">Filter by Team:</label>
        <select id="teamFilter" name="team_id" class="border rounded px-3 py-1" onchange="this.form.submit()">
            <option value="">All Teams</option>
            <?php foreach ($teams as $t): ?>
                <option value="<?= $t['id'] ?>" <?= ($filter_team_id == $t['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['name'] ?? '') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($filter_team_id): ?>
            <a href="players_admin.php" class="text-red-600 hover:underline">Clear filter</a>
        <?php endif; ?>
    </form>

    <form action="players_save.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <?php if ($editing): ?>
            <input type="hidden" name="id" value="<?= $editing['id'] ?>">
        <?php endif; ?>

        <div>
            <label class="block mb-1 font-semibold">Player Name</label>
            <input
                type="text"
                name="name"
                value="<?= htmlspecialchars($editing['name'] ?? '') ?>"
                required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-600 <?= $editing ? 'border-red-600' : '' ?>"
            />
        </div>

        <div>
            <label class="block mb-1 font-semibold">Team</label>
            <select
                name="team_id"
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-600 <?= $editing ? 'border-red-600' : '' ?>"
                required
            >
                <option value="">Select Team</option>
                <?php foreach ($teams as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= ($editing && $editing['team_id'] == $t['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['name'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block mb-1 font-semibold">Age</label>
            <input
                type="number"
                name="age"
                value="<?= htmlspecialchars($editing['age'] ?? '') ?>"
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-600 <?= $editing ? 'border-red-600' : '' ?>"
            />
        </div>

        <div>
            <label class="block mb-1 font-semibold">Position</label>
            <input
                type="text"
                name="position"
                value="<?= htmlspecialchars($editing['position'] ?? '') ?>"
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-600 <?= $editing ? 'border-red-600' : '' ?>"
            />
        </div>

        <div>
            <label class="block mb-1 font-semibold">Jersey Number</label>
            <input
                type="number"
                name="jersey_number"
                value="<?= htmlspecialchars($editing['jersey_number'] ?? '') ?>"
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-600 <?= $editing ? 'border-red-600' : '' ?>"
            />
        </div>

        <div>
            <label class="block mb-1 font-semibold">Photo</label>
            <input type="file" name="photo" accept="image/*" class="w-full" />
            <?php if (!empty($editing['photo']) && file_exists('../uploads/' . $editing['photo'])): ?>
                <div class="mt-3">
                    <img
                        src="../uploads/<?= $editing['photo'] ?>"
                        alt="Current Photo"
                        class="w-28 h-28 object-cover rounded cursor-pointer border border-gray-300"
                        onclick="togglePreview(this.src)"
                        title="Click to enlarge"
                    />
                </div>
            <?php endif; ?>
        </div>

        <div class="md:col-span-2">
            <button
                type="submit"
                class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition"
            >
                <?= $editing ? 'Update' : 'Add' ?> Player
            </button>
            <?php if ($editing): ?>
                <a href="players_admin.php" class="ml-4 text-gray-600 hover:underline">Cancel</a>
            <?php endif; ?>
        </div>
    </form>

    <h2 class="text-2xl font-bold mt-12 mb-4">Player List <?= $filter_team_id ? '(Filtered)' : '' ?></h2>
    <table class="w-full border-collapse border border-gray-300 text-sm">
        <thead>
            <tr class="bg-gray-200 text-left">
                <th class="p-3 border border-gray-300">Photo</th>
                <th class="p-3 border border-gray-300">Name</th>
                <th class="p-3 border border-gray-300">Team</th>
                <th class="p-3 border border-gray-300">Position</th>
                <th class="p-3 border border-gray-300">Jersey</th>
                <th class="p-3 border border-gray-300">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($players) === 0): ?>
                <tr>
                    <td colspan="6" class="p-4 text-center text-gray-600">No players found.</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($players as $p): ?>
                <tr class="border border-gray-300 hover:bg-gray-50">
                    <td class="p-2 border border-gray-300 text-center">
                        <?php if (!empty($p['photo']) && file_exists('../uploads/' . $p['photo'])): ?>
                            <img
                                src="../uploads/<?= $p['photo'] ?>"
                                alt="Player Photo"
                                class="w-14 h-14 object-cover rounded mx-auto cursor-pointer"
                                onclick="togglePreview(this.src)"
                                title="Click to enlarge"
                            />
                        <?php else: ?>
                            <span class="text-gray-400">No photo</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-2 border border-gray-300"><?= htmlspecialchars($p['name'] ?? '') ?></td>
                    <td class="p-2 border border-gray-300"><?= htmlspecialchars($p['team_name'] ?? '') ?></td>
                    <td class="p-2 border border-gray-300"><?= htmlspecialchars($p['position'] ?? '') ?></td>
                    <td class="p-2 border border-gray-300 text-center"><?= htmlspecialchars($p['jersey_number'] ?? '') ?></td>
                    <td class="p-2 border border-gray-300 whitespace-nowrap">
                        <a href="?edit=<?= $p['id'] ?>" class="text-blue-600 hover:underline mr-3">Edit</a>
                        <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Delete this player?')" class="text-red-600 hover:underline">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal for image preview -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center hidden z-50">
    <img id="modalImg" src="" class="max-w-3xl max-h-[90vh]" />
    <span onclick="closePreview()" class="absolute top-4 right-6 text-white text-3xl cursor-pointer select-none">×</span>
</div>

<script>
    function togglePreview(src) {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImg');
        modalImg.src = src;
        modal.classList.remove('hidden');
    }
    function closePreview() {
        document.getElementById('imageModal').classList.add('hidden');
    }
</script>
</body>
</html>
