<?php
require_once __DIR__ . "/init.php";
if ($_SERVER['REQUEST_METHOD'] != "POST") {
    header("Location: torneios.php");
    exit;
}
if (!isset($_SESSION['cadastrado']['id'])) {
    header("Location: entrarConta.php");
    exit;
}
$id_usuario = $_SESSION['cadastrado']['id'];
$id_torneio = filter_input(INPUT_POST, 'id_torneio', FILTER_VALIDATE_INT);
if (!$id_torneio) {
    header("Location: torneios.php?erro=ID_INVALIDO");
    exit;
}
$torneio = read($pdo, "torneios", "id = $id_torneio");
if (!$torneio) {
    header("Location: torneios.php?erro=TORNEIO_NAO_ENCONTRADO");
    exit;
}
$sql = "SELECT e.id, e.jogo_equipe
        FROM usuarios u
        INNER JOIN equipes e ON u.id_equipe = e.id
        WHERE u.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_usuario]);
$equipe = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$equipe) {
    header("Location: inscricaoTorneio.php?id=$id_torneio&erro=EQUIPE_NAO_ENCONTRADA");
    exit;
}
if ($equipe['jogo_equipe'] != $torneio['nmjogo_torneio']) {
    header("Location: inscricaoTorneio.php?id=$id_torneio&erro=JOGO_DIFERENTE");
    exit;
}
$sql = "SELECT id FROM torneios_equipes
        WHERE id_torneio = :id_torneio AND id_equipe = :id_equipe";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':id_torneio' => $id_torneio,
    ':id_equipe' => $equipe['id']
]);
if ($stmt->fetch()) {
    header("Location: inscricaoTorneio.php?id=$id_torneio&erro=EQUIPE_JA_INSCRITA");
    exit;
}
try {
    $sql = "INSERT INTO torneios_equipes (id_torneio, id_equipe)
            VALUES (:torneio, :equipe)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':torneio' => $id_torneio,
        ':equipe' => $equipe['id']
    ]);
    header("Location: torneios.php?sucesso=INSCRICAO_REALIZADA");
    exit;
} catch (PDOException $e) {
    header("Location: inscricaoTorneio.php?id=$id_torneio&erro=ERRO_INSCRICAO");
    exit;
}
?>