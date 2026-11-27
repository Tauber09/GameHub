<?php
require_once __DIR__ . "/init.php";

$idUsuario = $_SESSION['cadastrado']['id'] ?? null;
$usernameUsuario = $_SESSION['cadastrado']['username'] ?? null;

if (!isset($_SESSION['solicitacoes_equipes'])) {$_SESSION['solicitacoes_equipes'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_equipe'])) {$idEquipePost = filter_input(INPUT_POST, 'id_equipe', FILTER_VALIDATE_INT);
    $acao =$_POST['acao'] ?? 'entrar';

    if (!$idUsuario && !$idEquipePost) {
        header("Location: equipes.php");
        exit;
    }

    $user = read($pdo, "usuarios", "id = ?", [$idUsuario]);

    if (!$user) {
        header("Location: equipes.php");
        exit;
    }

    if ($acao === 'sair') {
        if ($user['id_equipe'] ==$idEquipePost) {
            update($pdo, "usuarios", ["id_equipe" => null], "id = ?", [$idUsuario]);

            header("Location: detalhes_equipe.php?id={$idEquipePost}&msg=saiu");
            exit;
        }
    } elseif ($acao === 'entrar') {
        if (!empty($user['id_equipe'])) {
            header("Location: detalhes_equipe.php?id={$idEquipePost}&msg=ja_possui_equipe");
            exit;
        }

        $eq = read($pdo, "equipes", "id = ?", [$idEquipePost]);

        if ($eq) {
            if ($eq['privacidade'] === 'publica') {
                update($pdo, "usuarios", ["id_equipe" => $idEquipePost], "id = ?", [$idUsuario]);
                unset($_SESSION['solicitacoes_equipes'][$idEquipePost][$idUsuario]);

                header("Location: detalhes_equipe.php?id={$idEquipePost}&msg=inscrito");
                exit;
            } else {
                $_SESSION['solicitacoes_equipes'][$idEquipePost][$idUsuario] =$usernameUsuario;

                header("Location: detalhes_equipe.php?id={$idEquipePost}&msg=solicitado");
                exit;
            }
        }
    } elseif ($acao === 'aceitar') {$idSolicitante = filter_input(INPUT_POST, 'id_solicitante', FILTER_VALIDATE_INT);

        if ($user['id_equipe'] ==$idEquipePost && $idSolicitante) {$solicitante = read($pdo, "usuarios", "id = ?", [$idSolicitante]);

            if ($solicitante && empty($solicitante['id_equipe'])) {
                update($pdo, "usuarios", ["id_equipe" => $idEquipePost], "id = ?", [$idSolicitante]);
                unset($_SESSION['solicitacoes_equipes'][$idEquipePost][$idSolicitante]);

                header("Location: detalhes_equipe.php?id={$idEquipePost}&msg=aceito");
                exit;
            } else {
                unset($_SESSION['solicitacoes_equipes'][$idEquipePost][$idSolicitante]);

                header("Location: detalhes_equipe.php?id={$idEquipePost}&msg=solicitante_com_equipe");
                exit;
            }
        }
    } elseif ($acao === 'recusar') {$idSolicitante = filter_input(INPUT_POST, 'id_solicitante', FILTER_VALIDATE_INT);

        if ($user['id_equipe'] == $idEquipePost && $idSolicitante) {
            unset($_SESSION['solicitacoes_equipes'][$idEquipePost][$idSolicitante]);

            header("Location: detalhes_equipe.php?id={$idEquipePost}&msg=recusado");
            exit;
        }
    }
}

$idEquipe = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$idEquipe) {
    header("Location: equipes.php");
    exit;
}

$equipe = read($pdo, "equipes", "id = ?", [$idEquipe]);

if (!$equipe) {
    header("Location: equipes.php");
    exit;
}

$membros = readAll($pdo, "usuarios", "id_equipe = ?", [$idEquipe]);

$usuarioLogado = null;
$solicitacaoPendente = false;
$solicitacoesRecebidas = [];

if ($idUsuario) {$usuarioLogado = read($pdo, "usuarios", "id = ?", [$idUsuario]);

    if ($usuarioLogado) {
        $solicitacaoPendente = isset($_SESSION['solicitacoes_equipes'][$idEquipe][$idUsuario]);

        if ($usuarioLogado['id_equipe'] ==$equipe['id'] && $equipe['privacidade'] !== 'publica') {$solicitacoesRecebidas = $_SESSION['solicitacoes_equipes'][$idEquipe] ?? [];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="PT-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($equipe['nome']) ?> - Detalhes</title>
    <link rel="stylesheet" href="./css/equipes.css">
</head>

<body>
    <?php require_once __DIR__ . "/cabecalho.php"; ?>

    <section class="fundo-equipes">
        <div class="card-detalhes">

            <?php if (isset($_GET['msg'])): ?>
                <div class="alerta">
                    <?php
                    if ($_GET['msg'] === 'inscrito') echo "Você entrou na equipe com sucesso!";
                    elseif ($_GET['msg'] === 'saiu') echo "Você saiu da equipe.";
                    elseif ($_GET['msg'] === 'solicitado') echo "Solicitação enviada! Aguarde a aprovação da equipe.";
                    elseif ($_GET['msg'] === 'ja_possui_equipe') echo "Você já faz parte de uma equipe.";
                    elseif ($_GET['msg'] === 'aceito') echo "Jogador aceito na equipe com sucesso!";
                    elseif ($_GET['msg'] === 'recusado') echo "Solicitação recusada.";
                    elseif ($_GET['msg'] === 'solicitante_com_equipe') echo "Este jogador já entrou em outra equipe.";
                    ?>
                </div>
            <?php endif; ?>

            <div class="topo-equipe">
                <img src="./img/logos/<?= htmlspecialchars($equipe['img']) ?>" alt="<?= htmlspecialchars($equipe['nome']) ?>">
                <div class="info-principal">
                    <h1><?= htmlspecialchars($equipe['nome']) ?></h1>
                    <span class="badge">Jogo: <?= htmlspecialchars($equipe['jogo_equipe']) ?></span>
                    <span class="badge">
                        <?= $equipe['privacidade'] === 'publica' ? 'Equipe Pública' : 'Equipe Privada' ?>
                    </span>
                </div>
            </div>

            <h3>Sobre a Equipe</h3>
            <p class="descricao-box"><?= nl2br(htmlspecialchars($equipe['descricao'])) ?></p>

            <h3>Integrantes (<?= count($membros) ?>)</h3>
            <?php if (!empty($membros)): ?>
                <ul class="lista-membros">
                    <?php foreach ($membros as$m): ?>
                        <li><?= htmlspecialchars($m['username_conta']) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="descricao-box">Nenhum integrante cadastrado nesta equipe ainda.</p>
            <?php endif; ?>

            <?php if ($usuarioLogado &&$usuarioLogado['id_equipe'] == $equipe['id'] &&$equipe['privacidade'] !== 'publica'): ?>
                <h3>Solicitações Pendentes (<?= count($solicitacoesRecebidas) ?>)</h3>
                <?php if (!empty($solicitacoesRecebidas)): ?>
                    <ul class="lista-membros">
                        <?php foreach ($solicitacoesRecebidas as $idSol =>$nomeSol): ?>
                            <li class="item-solicitacao">
                                <span><?= htmlspecialchars($nomeSol) ?></span>
                                <form action="" method="POST" class="form-solicitacao">
                                    <input type="hidden" name="id_equipe" value="<?= $equipe['id'] ?>">
                                    <input type="hidden" name="id_solicitante" value="<?= $idSol ?>">
                                    <button type="submit" name="acao" value="aceitar" class="btn-acao btn-mini">Aceitar</button>
                                    <button type="submit" name="acao" value="recusar" class="btn-acao btn-sair btn-mini">Recusar</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="descricao-box">Nenhuma solicitação pendente no momento.</p>
                <?php endif; ?>
            <?php endif; ?>

            <div class="acoes-equipe">
                <a href="equipes.php" class="btn-voltar">Voltar</a>

                <?php if (!$usuarioLogado): ?>
                    <span class="aviso-login">Faça login para participar desta equipe</span>

                <?php elseif ($usuarioLogado['id_equipe'] ==$equipe['id']): ?>
                    <form action="" method="POST">
                        <input type="hidden" name="id_equipe" value="<?= $equipe['id'] ?>">
                        <input type="hidden" name="acao" value="sair">
                        <button type="submit" class="btn-acao btn-sair">Sair da Equipe</button>
                    </form>

                <?php elseif (!empty($usuarioLogado['id_equipe'])): ?>
                    <button type="button" class="btn-acao btn-desabilitado" disabled>Você já pertence a outra equipe</button>

                <?php elseif ($solicitacaoPendente): ?>
                    <button type="button" class="btn-acao btn-desabilitado" disabled>Solicitação Pendente</button>

                <?php else: ?>
                    <form action="" method="POST">
                        <input type="hidden" name="id_equipe" value="<?= $equipe['id'] ?>">
                        <input type="hidden" name="acao" value="entrar">
                        <?php if ($equipe['privacidade'] === 'publica'): ?>
                            <button type="submit" class="btn-acao">Inscrever-se na Equipe</button>
                        <?php else: ?>
                            <button type="submit" class="btn-acao">Pedir para Entrar</button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>

        </div>
        <?php require_once __DIR__ . "/rodape.php"; ?>
    </section>
</body>

</html>