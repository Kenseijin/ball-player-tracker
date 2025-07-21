<?php
require_once '../classes/database.php';
require_once '../classes/game.php';
require_once '../classes/player.php';
require_once '../classes/team.php';
require_once '../classes/stat.php';

if (!isset($_GET['id']) || empty($_GET['id'])) die("Game ID is missing.");

$gameId = $_GET['id'];
$game = Game::getById($gameId);
if (!$game) die("Game not found.");

$home_team_id = $game['home_team_id'];
$away_team_id = $game['away_team_id'];

$playerObj = new Player(Database::getInstance()->getConnection());
$homePlayers = $playerObj->getByTeam($home_team_id);
$awayPlayers = $playerObj->getByTeam($away_team_id);

$team = new Team(Database::getInstance()->getConnection());
$homeName = $team->getNameById($home_team_id);
$awayName = $team->getNameById($away_team_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Score Entry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.cdnfonts.com/css/digital-7-mono" rel="stylesheet">
    <style>
        .seven-segment {
            font-family: 'Digital-7 Mono', monospace;
        }
        .stat-btn {
            @apply bg-blue-500 hover:bg-blue-600 text-white rounded-full w-10 h-10 mr-1 mb-1;
        }
        .undo-btn {
            @apply bg-gray-400 hover:bg-gray-500 text-white rounded-full w-10 h-10;
        }
    </style>
</head>
<body class="bg-gray-100 p-6">

    <div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-2">Score Entry</h1>
        <div class="flex justify-between items-center mb-4">
            <div class="text-xl font-bold"><?= htmlspecialchars($homeName) ?> vs <?= htmlspecialchars($awayName) ?></div>
            <div class="text-sm text-gray-600">Game ID: <?= $gameId ?></div>
        </div>

        <!-- Quarter and team scores -->
        <div class="flex justify-between items-center mb-4">
            <div class="text-2xl seven-segment text-red-600">
                <span>Quarter: </span><span id="currentQuarter">1</span>
            </div>
            <div class="text-xl font-bold flex gap-8 seven-segment">
                <div><?= htmlspecialchars($homeName) ?>: <span id="homeTotal">0</span></div>
                <div><?= htmlspecialchars($awayName) ?>: <span id="awayTotal">0</span></div>
            </div>
        </div>

        <form action="score_save.php" method="POST" id="scoreForm">
            <input type="hidden" name="game_id" value="<?= $gameId ?>">
            <input type="hidden" name="quarter" id="quarterInput" value="1">

            <div class="grid grid-cols-2 gap-6">
                <!-- Home -->
                <div>
                    <h2 class="text-lg font-semibold mb-2"><?= $homeName ?> (Home)</h2>
                    <?php foreach ($homePlayers as $p): ?>
                        <div class="mb-4 border-b pb-2">
                            <div class="font-medium mb-1" id="summary-<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> | FT: 0 2PT: 0 3PT: 0 STL: 0 BLK: 0 AST: 0</div>
                            <?php foreach (['ft'=>'FT','twopt'=>'2PT','threept'=>'3PT','steal'=>'STL','block'=>'BLK','assist'=>'AST'] as $key => $label): ?>
                                <button type="button" class="stat-btn" onclick="incrementStat(<?= $p['id'] ?>, '<?= $key ?>', 'home')"><?= $label ?></button>
                                <button type="button" class="undo-btn" onclick="undoStat(<?= $p['id'] ?>, '<?= $key ?>', 'home')">↺</button>
                                <input type="hidden" name="stats[<?= $p['id'] ?>][<?= $key ?>]" id="input-<?= $p['id'] ?>-<?= $key ?>" value="0">
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Away -->
                <div>
                    <h2 class="text-lg font-semibold mb-2"><?= $awayName ?> (Away)</h2>
                    <?php foreach ($awayPlayers as $p): ?>
                        <div class="mb-4 border-b pb-2">
                            <div class="font-medium mb-1" id="summary-<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> | FT: 0 2PT: 0 3PT: 0 STL: 0 BLK: 0 AST: 0</div>
                            <?php foreach (['ft'=>'FT','twopt'=>'2PT','threept'=>'3PT','steal'=>'STL','block'=>'BLK','assist'=>'AST'] as $key => $label): ?>
                                <button type="button" class="stat-btn" onclick="incrementStat(<?= $p['id'] ?>, '<?= $key ?>', 'away')"><?= $label ?></button>
                                <button type="button" class="undo-btn" onclick="undoStat(<?= $p['id'] ?>, '<?= $key ?>', 'away')">↺</button>
                                <input type="hidden" name="stats[<?= $p['id'] ?>][<?= $key ?>]" id="input-<?= $p['id'] ?>-<?= $key ?>" value="0">
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Submit Stats</button>
                <button type="button" onclick="nextQuarter()" id="nextQuarterBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Next Quarter</button>
            </div>
        </form>
    </div>

    <script>
        let playerStats = {};
        let currentQuarter = 1;

        function updateSummary(playerId) {
            const keys = ['ft', 'twopt', 'threept', 'steal', 'block', 'assist'];
            const values = keys.map(k => document.getElementById(`input-${playerId}-${k}`).value);
            document.getElementById(`summary-${playerId}`).innerText =
                `Player ${playerId} | FT: ${values[0]} 2PT: ${values[1]} 3PT: ${values[2]} STL: ${values[3]} BLK: ${values[4]} AST: ${values[5]}`;
        }

        function incrementStat(playerId, statType, team) {
            const input = document.getElementById(`input-${playerId}-${statType}`);
            input.value = parseInt(input.value) + 1;
            updateSummary(playerId);
            updateTeamTotal(team);
        }

        function undoStat(playerId, statType, team) {
            const input = document.getElementById(`input-${playerId}-${statType}`);
            if (parseInt(input.value) > 0) {
                input.value = parseInt(input.value) - 1;
                updateSummary(playerId);
                updateTeamTotal(team);
            }
        }

        function updateTeamTotal(team) {
            let total = 0;
            const inputs = document.querySelectorAll(`input[name^="stats["]`);
            inputs.forEach(input => {
                const [_, playerId, stat] = input.name.match(/stats\[(\d+)\]\[(\w+)\]/);
                const val = parseInt(input.value);
                const pid = parseInt(playerId);
                if (
                    (team === 'home' && document.querySelector(`#summary-${pid}`).parentElement.parentElement.previousElementSibling?.textContent.includes('Home')) ||
                    (team === 'away' && document.querySelector(`#summary-${pid}`).parentElement.parentElement.previousElementSibling?.textContent.includes('Away'))
                ) {
                    if (stat === 'ft') total += val;
                    if (stat === 'twopt') total += val * 2;
                    if (stat === 'threept') total += val * 3;
                }
            });

            document.getElementById(team + 'Total').innerText = total;
        }

        function nextQuarter() {
            if (currentQuarter < 4) {
                currentQuarter++;
                document.getElementById('quarterInput').value = currentQuarter;
                document.getElementById('currentQuarter').innerText = currentQuarter;
            } else {
                alert("Game over. 4 quarters completed.");
                document.getElementById('nextQuarterBtn').disabled = true;
                document.getElementById('scoreForm').querySelector('button[type="submit"]').disabled = true;
            }
        }
    </script>
</body>
</html>
