<?php

require_once __DIR__ . "/init.php";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    header("Location: torneios.php");
    exit;
}


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

$img = match ($jogo) {
    "cs2"           => "./img/cs2_banner.png",
    "valorant"      => "./img/banner_valorant.png",
    "rocket-league" => "./img/banner_rocket.png",
    "fortnite"      => "./img/banner-fortnite.png",
    "fc27"          => "./img/banner_fc.png",
    "r6-siege"      => "./img/banner-r6.png",
    "lol"           => "./img/banner-lol.png",
    default         => "./img/banner_padrao.png",
};

try {
    $consulta = $pdo->prepare("
        INSERT INTO torneios 
        (nome_torneio, nmjogo_torneio, format_torneio, qnt_jogadores, data_torneio, horainit_torneio, premiacao, img, chaveamento) 
        VALUES 
        (:nome, :jogo, :formato, :jogadores, :data, :hora, :premio, :img, :chaveamento)
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
    ]);

  
} catch (PDOException $e) {
    error_log($e->getMessage());

    exit("Erro ao salvar as informações: " . $e->getMessage());
}

?>