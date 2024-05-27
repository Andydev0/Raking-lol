<?php
$apiKey = ''; //API DA RIOT
$file = 'summoners.json';
$cacheFile = 'cache.json';
$cacheTime = 300; // Tempo de cache em segundos (5 minutos)

// Função para obter dados da API com cache
function getAPIData($url, $apiKey) {
    global $cacheFile, $cacheTime;
    $cache = [];

    if (file_exists($cacheFile)) {
        $cache = json_decode(file_get_contents($cacheFile), true);
    }

    $cacheKey = md5($url);
    if (isset($cache[$cacheKey]) && (time() - $cache[$cacheKey]['timestamp']) < $cacheTime) {
        return $cache[$cacheKey]['data'];
    }

    $response = @file_get_contents($url, false, stream_context_create([
        "http" => [
            "header" => "X-Riot-Token: $apiKey"
        ]
    ]));

    if ($response !== FALSE) {
        $cache[$cacheKey] = [
            'timestamp' => time(),
            'data' => json_decode($response, true)
        ];
        file_put_contents($cacheFile, json_encode($cache));
        return $cache[$cacheKey]['data'];
    }

    return null;
}

// Função para obter o PUUID
function getPUUID($gameName, $tagLine, $apiKey) {
    $url = "https://americas.api.riotgames.com/riot/account/v1/accounts/by-riot-id/$gameName/$tagLine";
    $data = getAPIData($url, $apiKey);
    return $data['puuid'] ?? null;
}

// Função para obter o ID do invocador
function getSummonerId($puuid, $apiKey) {
    $url = "https://br1.api.riotgames.com/lol/summoner/v4/summoners/by-puuid/$puuid";
    $data = getAPIData($url, $apiKey);
    return $data['id'] ?? null;
}

// Função para obter dados de rank
function getRankData($summonerId, $apiKey) {
    $url = "https://br1.api.riotgames.com/lol/league/v4/entries/by-summoner/$summonerId";
    return getAPIData($url, $apiKey) ?? [];
}

// Função para obter o jogo atual
function getCurrentGame($puuid, $apiKey) {
    $url = "https://br1.api.riotgames.com/lol/spectator/v5/active-games/by-summoner/$puuid";
    return getAPIData($url, $apiKey);
}

// Função para obter a hora do último jogo
function getLastGameTime($puuid, $apiKey) {
    $url = "https://americas.api.riotgames.com/lol/match/v5/matches/by-puuid/$puuid/ids?start=0&count=1";
    $data = getAPIData($url, $apiKey);
    $matchId = $data[0] ?? null;

    if ($matchId) {
        $matchUrl = "https://americas.api.riotgames.com/lol/match/v5/matches/$matchId";
        $matchData = getAPIData($matchUrl, $apiKey);
        return $matchData['info']['gameEndTimestamp'] ?? null;
    }
    return null;
}

// Função para calcular o tempo decorrido
function timeElapsed($datetime) {
    $interval = time() - $datetime / 1000;
    $minutes = floor($interval / 60);
    $hours = floor($minutes / 60);
    $days = floor($hours / 24);

    if ($days > 0) {
        return $days . ' Dias';
    } elseif ($hours > 0) {
        return $hours . ' Horas';
    } else {
        return $minutes . ' Minutos';
    }
}

if (file_exists($file)) {
    $summoners = json_decode(file_get_contents($file), true);
    if (!is_array($summoners)) {
        echo "<p>Erro ao ler o arquivo JSON.</p>";
        exit;
    }

    $rankings = [];
    foreach ($summoners as $summoner) {
        $puuid = getPUUID($summoner['gameName'], $summoner['tagLine'], $apiKey);
        if (!$puuid) {
            echo "<p>Erro ao obter PUUID para {$summoner['gameName']}#{$summoner['tagLine']}.</p>";
            continue;
        }
        $summonerId = getSummonerId($puuid, $apiKey);
        if (!$summonerId) {
            echo "<p>Erro ao obter Summoner ID para {$summoner['gameName']}#{$summoner['tagLine']}.</p>";
            continue;
        }
        $rankData = getRankData($summonerId, $apiKey);
        $currentGame = getCurrentGame($puuid, $apiKey);
        
        foreach ($rankData as $entry) {
            if ($entry['queueType'] == 'RANKED_SOLO_5x5') {
                $wins = $entry['wins'];
                $losses = $entry['losses'];
                $totalGames = $wins + $losses;
                $winrate = ($totalGames > 0) ? round(($wins / $totalGames) * 100, 2) : 0;

                $status = '';
                $opggUrl = "https://br.op.gg/summoner/userName=" . urlencode($summoner['gameName'] . '-' . $summoner['tagLine']);
                if ($currentGame) {
                    $status = "<a href='$opggUrl' target='_blank' style='color: green;'>EM JOGO</a>";
                } else {
                    $lastGameTime = getLastGameTime($puuid, $apiKey);
                    if ($lastGameTime) {
                        $timeElapsed = timeElapsed($lastGameTime);
                        $status = "<span style='color: red;'>Sem jogar há $timeElapsed</span>";
                    } else {
                        $status = "<span style='color: red;'>SEM INFORMAÇÕES DE PARTIDA</span>";
                    }
                }

                $rankings[] = [
                    'gameName' => $summoner['gameName'],
                    'tier' => $entry['tier'],
                    'rank' => $entry['rank'],
                    'tierRank' => $entry['tier'] . ' ' . $entry['rank'],
                    'leaguePoints' => $entry['leaguePoints'],
                    'wins' => $wins,
                    'losses' => $losses,
                    'winrate' => $winrate,
                    'status' => $status
                ];
            }
        }
    }

    // Ordenar rankings por tier, rank e pontos
    usort($rankings, function($a, $b) {
        $tiers = ['IRON', 'BRONZE', 'SILVER', 'GOLD', 'PLATINUM', 'DIAMOND', 'MASTER', 'GRANDMASTER', 'CHALLENGER'];
        $rankOrder = ['IV' => 4, 'III' => 3, 'II' => 2, 'I' => 1];

        $tierComparison = array_search($b['tier'], $tiers) - array_search($a['tier'], $tiers);
        if ($tierComparison === 0) {
            $rankComparison = $rankOrder[$a['rank']] - $rankOrder[$b['rank']];
            if ($rankComparison === 0) {
                return $b['leaguePoints'] - $a['leaguePoints'];
            }
            return $rankComparison;
        }
        return $tierComparison;
    });

    // Exibir os rankings em uma tabela
    echo '<table>';
    echo '<tr><th>Nome do Invocador</th><th>Tier e Rank</th><th>Pontos</th><th>Vitórias</th><th>Derrotas</th><th>Winrate (%)</th><th>Status</th></tr>';
    foreach ($rankings as $rank) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($rank['gameName']) . '</td>';
        echo '<td>' . htmlspecialchars($rank['tierRank']) . '</td>';
        echo '<td>' . htmlspecialchars($rank['leaguePoints']) . '</td>';
        echo '<td>' . htmlspecialchars($rank['wins']) . '</td>';
        echo '<td>' . htmlspecialchars($rank['losses']) . '</td>';
        echo '<td>' . htmlspecialchars($rank['winrate']) . '</td>';
        echo '<td>' . $rank['status'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo '<p>Não há invocadores cadastrados.</p>';
}
?>
