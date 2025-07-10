<?php include '../includes/header.php'; ?>
<div class="container mx-auto p-4">
<h1 class="text-3x1 font-bold mb-6 text-center text-indigo-600">Admin Dashboard</h1>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <a href="teams_admin.php" class="bg-indigo-100 hover:bg-indigo-200 p-6 rounded-x1 shadow text-center">
        <h2 class="text-x1 font-semibold text-indigo-800">Manage Teams</h2></a>
    <a href=players_admin.php" class="bg-green-100 hover:bg-green-200 p-6 rounded-x1 shadow text-center">
        <h2 class="text-x1 font-semibold text-green-800">Manage Players</h2></a>
    <a href="games_admin.php" class="bg-yellow-100 hover:bg-yellow-200 p-6 rounded-x1 shadow text-center">
        <h2 class="text-x1 font-semibold text-yellow-800">Record Stats</2></a>
</div>
</div>
<?php include '../includes/footer.php'; ?>