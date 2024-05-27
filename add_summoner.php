<?php
$apiKey = ''; // Substitua pela sua chave de API

// Função para obter o PUUID
function getPUUID($gameName, $tagLine, $apiKey) {
    $url = "https://americas.api.riotgames.com/riot/account/v1/accounts/by-riot-id/$gameName/$tagLine";
    $response = @file_get_contents($url, false, stream_context_create([
        "http" => [
            "header" => "X-Riot-Token: $apiKey"
        ]
    ]));
    if ($response === FALSE) {
        return null;
    }
    $data = json_decode($response, true);
    return $data['puuid'] ?? null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gameName = $_POST['gameName'];
    $tagLine = $_POST['tagLine'];

    // Verifica se os campos estão preenchidos
    if (empty($gameName) || empty($tagLine)) {
        echo '<p>Por favor, preencha todos os campos.</p>';
        exit;
    }

    // Verifica se o invocador existe
    $puuid = getPUUID($gameName, $tagLine, $apiKey);
    if (!$puuid) {
        echo '<p>Invocador não encontrado. Por favor, verifique o nick e tag.</p>';
        exit;
    }

    $file = 'summoners.json';

    // Verifica se o arquivo existe e lê seu conteúdo
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
    } else {
        $data = [];
    }

    // Verifica se o invocador já está cadastrado
    foreach ($data as $summoner) {
        if ($summoner['gameName'] === $gameName) {
            echo '<p>Invocador já cadastrado.</p>';
            exit;
        }
    }

    // Adiciona o novo invocador ao array
    $data[] = ['gameName' => $gameName, 'tagLine' => $tagLine];

    // Salva os dados no arquivo JSON
    if (file_put_contents($file, json_encode($data))) {
        echo '<p>Invocador adicionado com sucesso.</p>';
    } else {
        echo '<p>Erro ao adicionar invocador.</p>';
    }
}
?>
