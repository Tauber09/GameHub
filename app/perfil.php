<?php 

require_once __DIR__ . "/init.php";

$id = $_GET['id'] ?? null;
$usuario = null;
$ranking = null;
$totalTorneios = 0;
$i = 1;

if ($id !== null) {
    $consulta = $pdo->prepare("
        SELECT usuarios.username_conta, equipes.nome AS nome_equipe, equipes.img AS img_equipe, equipes.jogo_equipe 
        FROM usuarios 
        LEFT JOIN equipes ON usuarios.id_equipe = equipes.id 
        WHERE usuarios.id = :id
    ");
    $consulta->execute(['id' => $id]);
    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $stmt = $pdo->query("SELECT * FROM ranking ORDER BY pontuacao DESC");
        $rankings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmtRanking = $pdo->prepare("SELECT * FROM ranking WHERE id_usuario = :id");
        $stmtRanking->execute(['id' => $id]);
        $ranking = $stmtRanking->fetch(PDO::FETCH_ASSOC);

        if ($ranking && $rankings) {
            foreach ($rankings as $r) {
                if ($r['pontuacao'] > $ranking['pontuacao']) {
                    $i++;
                } else {
                    break;
                }
            }
        }

        $stmtTorneios = $pdo->prepare("
            SELECT COUNT(*) AS total
            FROM torneios_equipes te
            INNER JOIN usuarios u ON te.id_equipe = u.id_equipe
            INNER JOIN torneios t ON te.id_torneio = t.id
            WHERE u.id = :id AND t.data_torneio < CURDATE()
        ");
        $stmtTorneios->execute(['id' => $id]);
        $totalTorneios = $stmtTorneios->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }
}

?>

<!DOCTYPE html>
<html lang="PT-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/perfil.css">
    <title>Perfil - <?= $usuario ? htmlspecialchars($usuario['username_conta']) : 'Não encontrado' ?></title>
</head>

<body class="tudo">
    <?php require_once __DIR__ . "/cabecalho.php" ?>

    <div class="container-perfil">
        <?php if (!$usuario): ?>
            <div class="card-perfil erro-card">
                <h1 class="titulo-erro">ESTE USUÁRIO <span class="texto-laranja">NÃO EXISTE</span></h1>
                <a class="btn-voltar" href="rankings.php">Voltar</a>
            </div>
        <?php else: ?>
            <div class="card-perfil">
                <div class="perfil-topo">
                    <div class="perfil-avatar">
                        <img src="./img/login.png" alt="Avatar do usuário">
                    </div>
                    <div class="perfil-identidade">
                        <p class="perfil-rotulo">Perfil do Jogador</p>
                        <h1 class="perfil-nome"><?= htmlspecialchars($usuario['username_conta']) ?></h1>
                    </div>
                </div>

                <div class="perfil-stats">
                    <div class="status">
                        <span class="status-valor">#<?= $i ?></span>
                        <span class="status-rotulo">Ranking</span>
                    </div>
                    <div class="status">
                        <span class="status-valor"><?= $ranking['pontuacao'] ?? 0 ?></span>
                        <span class="status-rotulo">Pontos</span>
                    </div>
                </div>

                <div>
                    <h3 class="perfil-informacoes">Informações</h3>
                    <ul class="perfil-info">
                        <li>
                            <span class="info-1">Equipe</span>
                            <span class="info-2 info-equipe">
                                <?php if (!empty($usuario['nome_equipe'])): ?>
                                    <img class="equipe-mini-logo" src="./img/logos/<?= htmlspecialchars($usuario['img_equipe']) ?>" alt="" onerror="this.style.display='none'">
                                    <?= htmlspecialchars($usuario['nome_equipe']) ?>
                                <?php else: ?>
                                    Sem equipe
                                <?php endif; ?>
                            </span>
                        </li>
                        <li>
                            <span class="info-1">Jogo principal</span>
                            <span class="info-2"><?= htmlspecialchars($usuario['jogo_equipe'] ?? 'Nenhum') ?></span>
                        </li>
                        <li>
                            <span class="info-1">Torneios disputados</span>
                            <span class="info-2"><?= $totalTorneios ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>