<?php

require_once __DIR__ . "/init.php";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    header("Location: torneios.php");
    exit;
}

$cadastro = $_SESSION['cadastrado']['perfil'] ?? null;

$tipo = null;

$nome = $_POST['nome'] ?? null;
$jogo = $_POST['jogo'] ?? null;
$formato = $_POST['formato'] ?? null;
$equipes = $_POST['equipes'] ?? null;
$jogadores = $_POST['jogadores'] ?? null;
$data = $_POST['data'] ?? null;
$hora = $_POST['hora'] ?? null;
$premio = $_POST['premio'] ?? null;
$inscricao = $_POST['inscricao'] ?? null;
$visibilidade = $_POST['visibilidade'] ?? null;
$chaveamento = $_POST['chaveamento'] ?? null;

$jogo = trim($_POST['jogo'] ?? '');

if($cadastro == "root"){
    $tipo = "gamehub";
    $img = match ($jogo) {
    "cs2"           => "Master League",
    "valorant"      => "Phantom Tournament",
    "rocket-league" => "Rocket Champions",
    "fortnite"      => "FN Cup",
    "fc27"          => "EA Cup",
    "r6-siege"      => "Siege Cup",
    "lol"           => "CNLOL",
};
}else{
    $tipo = "comunidade";
    $img = match ($jogo) {
    "cs2"           => "Master League2",
    "valorant"      => "Phantom Tournament2",
    "rocket-league" => "Rocket Champion2",
    "fortnite"      => "FN Cup2",
    "fc27"          => "EA Cup2",
    "r6-siege"      => "Siege Cup2",
    "lol"           => "CNLOL2",
};
}


try {
    $consulta = $pdo->prepare("
        INSERT INTO torneios 
        (nome_torneio, nmjogo_torneio, format_torneio, qnt_jogadores, data_torneio, horainit_torneio, premiacao, img, chaveamento, tipo) 
        VALUES 
        (:nome, :jogo, :formato, :jogadores, :data, :hora, :premio, :img, :chaveamento, :tipo)
    ");

    $consulta->execute([
        ':nome' => $nome,
        ':jogo' => $jogo,
        ':formato' => $formato,
        ':jogadores' => $jogadores,
        ':data' => $data,
        ':hora' => $hora,
        ':premio' => $premio,
        ':img' => $img,
        ':chaveamento' => $chaveamento,
        ':tipo' => $tipo,
    ]);

    header("Location: torneios.php");
  
} catch (PDOException $e) {
    error_log($e->getMessage());

    exit("Erro ao salvar as informações: " . $e->getMessage());
}

?>