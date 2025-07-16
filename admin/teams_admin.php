<?php
require_once '../classes/team.php';
$team = new Team();

$teams = $team->getAll();
$editing = isset($_GET['edit']) ? $team->getById($_GET['edit']) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-4 rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Team Management</h1>

        <form action="teams_save.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php if ($editing): ?>
                <input type="hidden" name="id" value="<?= $editing['id'] ?>">
            <?php endif; ?>
            
            <div>
                <label class="block mb-1 font-semibold">Team Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($editing['name'] ?? '') ?>" required class="w-full border px-2 py-1 rounded">
            </div>

            <div>
                <label class="block mb-1 font-semibold">Coach</label>
                <input type="text" name="coach" value="<?= htmlspecialchars($editing['coach'] ?? '') ?>" class="w-full border px-2 py-1 rounded">
            </div>

            <div>
                <label class="block mb-1 font-semibold">City</label>
                <input type="text" name="city" value="<?= htmlspecialchars($editing['city'] ?? '') ?>" class="w-full border px-2 py-1 rounded">
            </div>

            <div>
                <label class="block mb-1 font-semibold">Logo</label>
                <input type="file" name="logo" accept="image/*" class="w-full">
                <?php if (!empty($editing['logo']) && file_exists('../uploads/' . $editing['logo'])): ?>
                    <div class="mt-2">
                        <img src="../uploads/<?= $editing['logo'] ?>" alt="Current Logo" class="w-24 h-24 object-cover border rounded cursor-pointer" onclick="togglePreview(this.src)">
                    </div>
                <?php endif; ?>
            </div>

            <div class="md:col-span-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"><?= $editing ? 'Update' : 'Add' ?> Team</button>
            </div>
        </form>

        <h2 class="text-xl font-bold mt-8">Team List</h2>
        <table class="w-full mt-4 border text-sm">
            <thead>
                <tr class="bg-gray-200 text-left">
                    <th class="p-2">Logo</th>
                    <th class="p-2">Name</th>
                    <th class="p-2">Coach</th>
                    <th class="p-2">City</th>
                    <th class="p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teams as $t): ?>
                    <tr class="border-t">
                        <td class="p-2">
                            <?php if ($t['logo']): ?>
                                <img src="../uploads/<?= $t['logo'] ?>" alt="" class="w-12 h-12 object-cover rounded">
                            <?php endif; ?>
                        </td>
                        <td class="p-2"><?= htmlspecialchars($t['name']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($t['coach']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($t['city']) ?></td>
                        <td class="p-2">
                            <a href="?edit=<?= $t['id'] ?>" class="text-blue-500 hover:underline">Edit</a>
                            <a href="teams_save.php?delete=<?= $t['id'] ?>" onclick="return confirm('Delete this team?')" class="text-red-500 hover:underline ml-2">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal image preview -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center hidden z-50">
        <img id="modalImg" src="" class="max-w-2xl max-h-[90vh]">
        <span onclick="closePreview()" class="absolute top-4 right-6 text-white text-2xl cursor-pointer">×</span>
    </div>

    <script>
        function togglePreview(src) {
            document.getElementById('modalImg').src = src;
            document.getElementById('imageModal').classList.remove('hidden');
        }
        function closePreview() {
            document.getElementById('imageModal').classList.add('hidden');
        }
    </script>
</body>
</html>
