<?php

require_once __DIR__ . "/init.php";

$usuario = null;
$ranking = null;
$i = null;

if (isset($_SESSION['cadastrado'])) {
    $id = $_SESSION['cadastrado']['id'];

    $consulta = $pdo->prepare("
        SELECT usuarios.username_conta, equipes.nome AS nome_equipe, equipes.img AS img_equipe, equipes.jogo_equipe 
        FROM usuarios 
        LEFT JOIN equipes ON usuarios.id_equipe = equipes.id 
        WHERE usuarios.id = :id
    ");
    $consulta->execute(['id' => $id]);
    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT * FROM ranking ORDER BY pontuacao DESC");
    $rankings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtRanking = $pdo->prepare("SELECT * FROM ranking WHERE id_usuario = :id");
    $stmtRanking->execute(['id' => $id]);
    $ranking = $stmtRanking->fetch(PDO::FETCH_ASSOC);

    $stmtTorneios = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM torneios_equipes te
        INNER JOIN usuarios u ON te.id_equipe = u.id_equipe
        INNER JOIN torneios t ON te.id_torneio = t.id
        WHERE u.id = :id AND t.data_torneio < CURDATE()
    ");
    $stmtTorneios->execute(['id' => $id]);
    $totalTorneios = $stmtTorneios->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    if ($ranking && $rankings) {
        $i = 1;
        foreach ($rankings as $r) {
            if ($r['pontuacao'] > $ranking['pontuacao']) {
                $i++;
            } else {
                break;
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == "GET" || isset($_GET['erro'])) {
    $erro = $_GET['erro'] ?? null;
    $email = $_SESSION['erro']['email'] ?? null; 
}

unset($_SESSION['erro']);

?>

<!DOCTYPE html>
<html lang="PT-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/entrarConta.css">
    <title>Entrar conta</title>
</head>

<body class="tudo">
    <?php require_once __DIR__ . "/cabecalho.php" ?>

    <div class="formulario">

        <?php if (!isset($_SESSION['cadastrado'])): ?>
        <form class="form form-entrar" action="processarEntrarConta.php" method="POST">
            <h1 class="titulo"><span class="texto-laranja">Entrar</span> conta</h1>
            <div class="campo">
                <label for="email"><span class="texto-laranja">E-mail</span></label>
                <input type="text" name="email" id="email" placeholder="E-mail" value="<?= $email ?>">
            </div>

            <div class="campo">
                <label for="senha"><span class="texto-laranja">Senha</span></label>
                <input type="password" name="senha" id="senha" placeholder="Senha">
                <?php if ($erro == "EMAIL_SENHA"): ?>
                    <p class="erro">SENHA OU E-MAIL INCORRETOS</p>
                <?php endif ?>
            </div>

            <button type="submit">Entrar</button>

            <div class="separador">OU</div>
            <a class="entrar" href="./criarConta.php">Criar conta</a>
        </form>
        <?php endif ?>

        <?php if (isset($_SESSION['cadastrado'])): ?>
        <div class="perfil">
            <div class="perfil-topo">
                <div class="perfil-avatar">
                    <span><img src="./img/login.png" alt=""></span>
                </div>
                <div class="perfil-identidade">
                    <p class="perfil-rotulo">Você está cadastrado como</p>
                    <h2 class="perfil-nome"><?= $_SESSION['cadastrado']['username'] ?></h2>
                    <p class="perfil-email"><?= $_SESSION['cadastrado']['email'] ?></p>
                </div>
            </div>

            <div class="perfil-stats">
                <div class="status">
                    <span class="status-valor"><?= $i !== null ? '#' . $i : '-' ?></span>
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
                        <span class="info-2"><?= $usuario['nome_equipe'] ?? 'Sem equipe' ?></span>
                    </li>
                    <li>
                        <span class="info-1">Jogo principal</span>
                        <span class="info-2"><?= $usuario['jogo_equipe'] ?? 'Nenhum' ?></span>
                    </li>
                    <li>
                        <span class="info-1">Torneios disputados</span>
                        <span class="info-2"><?= $totalTorneios ?></span>
                    </li>
                </ul>
            </div>

            <div class="perfil-acoes">
                <a class="perfil1 sair" href="sair_conta.php">Sair da conta</a>
            </div>
        </div>
        <?php endif ?>
    </div>
</body>

</html>