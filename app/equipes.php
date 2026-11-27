<?php

require_once __DIR__ . "/init.php";

$idUsuario = $_SESSION['cadastrado']['id'] ?? null;
$jaTemEquipe = false;

if ($idUsuario) {
    $usuario = read($pdo, "usuarios", "id = ?", [$idUsuario]);
    if ($usuario && !empty($usuario['id_equipe'])) {
        $jaTemEquipe = true;
    }
}

$busca = filter_input(INPUT_GET, 'busca', FILTER_SANITIZE_SPECIAL_CHARS);
if ($busca == "cs" || $busca == "cs2" || $busca == "CS" || $busca == "CS2") {
    $busca = "Counter-Strike 2";
}

if (!empty($busca)) {
    $sql = "SELECT * FROM equipes WHERE nome LIKE ? OR jogo_equipe LIKE ? ORDER BY jogo_equipe ASC";
    $stmt = $pdo->prepare($sql);

    $termo = '%' . $busca . '%';
    $stmt->execute([$termo, $termo]);

    $equipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $sql = "SELECT * FROM equipes ORDER BY jogo_equipe ASC";
    $equipes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

$temLogo = isset($_SESSION['logoSelecionada']) && !empty($_SESSION['logoSelecionada']);
$caminhoLogo = "";
if (!empty($_SESSION['logoSelecionada'])) {
    $caminhoLogo = "./img/logos/" . $_SESSION['logoSelecionada'];
}

?>

<!DOCTYPE html>
<html lang="PT-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipes</title>
    <link rel="stylesheet" href="./css/equipes.css">
</head>

<body>
    <?php require_once __DIR__ . "/cabecalho.php" ?>

    <section class="fundo-equipes">

        <div class="funcao">

            <form action="" method="get" class="barra-pesquisa">
                <input type="text" name="busca" value="<?= htmlspecialchars($busca ?? '') ?>" placeholder="Buscar equipe:" required class="pesquisar">
                <?php if (!empty($busca)): ?>
                    <div class="limpar"><a href="equipes.php#painel" class="limpar">Limpar</a></div>
                <?php else: ?>
                    <button type="submit" class="enviar">Buscar</button>
                <?php endif ?>
            </form>

            <?php if ($jaTemEquipe): ?>
                <a href="detalhes_equipe.php?id=<?= $usuario['id_equipe'] ?>"><i>MINHA <span>EQUIPE</span></i></a>
            <?php else: ?>
                <a href="#popup"><i>CRIAR <span>EQUIPE</span></i></a>

                <div id="popup" class="criar-equipe">
                    <div class="popup-box">
                        <a class="fechar" href="#">&times;</a>

                        <h1>CRIAÇÃO DE <span>EQUIPE</span></h1>

                        <form class="criarConta" action="processaEquipes.php" method="POST">
                            <div class="linha-superior">

                                <button type="button" popovertarget="popup-logos" class="logo" id="btn-abrir-logos">
                                    <?php if (empty($caminhoLogo)): ?>
                                        <span id="texto-logo">ESCOLHA UMA LOGO</span>
                                    <?php else: ?>
                                        <img id="preview-logo" class="logoEscolhida"
                                            src="<?= htmlspecialchars($caminhoLogo) ?>"
                                            alt="Logo selecionada"
                                            style="width: 120px; height: 100px; object-fit: contain; display: block;">
                                    <?php endif; ?>
                                </button>

                                <div id="popup-logos" popover class="escolha-logo">

                                    <h2>Escolha uma <span>logo</span></h2>

                                    <?php $_SESSION['logoEquipe'] = [
                                        'logo_01.png' => '<img src="./img/logos/logo_01.png" alt="">',
                                        'logo_02.png' => '<img src="./img/logos/logo_02.png" alt="">',
                                        'logo_03.png' => '<img src="./img/logos/logo_03.png" alt="">',
                                        'logo_04.png' => '<img src="./img/logos/logo_04.png" alt="">',
                                        'logo_05.png' => '<img src="./img/logos/logo_05.png" alt="">',
                                        'logo_06.png' => '<img src="./img/logos/logo_06.png" alt="">',
                                        'logo_07.png' => '<img src="./img/logos/logo_07.png" alt="">',
                                        'logo_08.png' => '<img src="./img/logos/logo_08.png" alt="">',
                                        'logo_09.png' => '<img src="./img/logos/logo_09.png" alt="">',
                                        'logo_10.png' => '<img src="./img/logos/logo_10.png" alt="">',
                                        'logo_11.png' => '<img src="./img/logos/logo_11.png" alt="">',
                                        'logo_12.png' => '<img src="./img/logos/logo_12.png" alt="">',
                                        'logo_13.png' => '<img src="./img/logos/logo_13.png" alt="">',
                                        'logo_14.png' => '<img src="./img/logos/logo_14.png" alt="">',
                                        'logo_15.png' => '<img src="./img/logos/logo_15.png" alt="">',
                                        'logo_16.png' => '<img src="./img/logos/logo_16.png" alt="">',
                                        'logo_17.png' => '<img src="./img/logos/logo_17.png" alt="">',
                                        'logo_18.png' => '<img src="./img/logos/logo_18.png" alt="">',
                                        'logo_19.png' => '<img src="./img/logos/logo_19.png" alt="">',
                                        'logo_20.png' => '<img src="./img/logos/logo_20.png" alt="">',
                                        'logo_21.png' => '<img src="./img/logos/logo_21.png" alt="">',
                                        'logo_22.png' => '<img src="./img/logos/logo_22.png" alt="">',
                                        'logo_23.png' => '<img src="./img/logos/logo_23.png" alt="">',
                                        'logo_24.png' => '<img src="./img/logos/logo_24.png" alt="">',
                                        'logo_25.png' => '<img src="./img/logos/logo_25.png" alt="">',
                                        'logo_26.png' => '<img src="./img/logos/logo_26.png" alt="">',
                                        'logo_27.png' => '<img src="./img/logos/logo_27.png" alt="">',
                                        'logo_28.png' => '<img src="./img/logos/logo_28.png" alt="">',
                                        'logo_29.png' => '<img src="./img/logos/logo_29.png" alt="">',
                                        'logo_30.png' => '<img src="./img/logos/logo_30.png" alt="">',
                                    ] ?>
                                    <?php foreach ($_SESSION['logoEquipe'] as $chave => $l): ?>
                                        <?php print "<a href='./processaEquipes.php?id={$chave}'>$l</a>" ?>
                                    <?php endforeach ?>

                                    <?php if (isset($_GET['id']) && isset($_SESSION['logoEquipe'][$_GET['id']])) {
                                        print($_SESSION['logoEquipe'][$_GET['id']]);
                                    } ?>

                                    <button type="button" popovertarget="popup-logos" popovertargetaction="hide">
                                        Fechar
                                    </button>
                                </div>

                                <input type="hidden" name="logo_selecionada" id="logo_selecionada" value="">

                                <div class="coluna-input">
                                    <input type="text" name="nome" id="nome" placeholder="ESCOLHA UM NOME:">
                                    <select name="jogo" id="jogo" required>
                                        <option value="Counter-Strike 2">Counter-Strike 2</option>
                                        <option value="Valorant">Valorant</option>
                                        <option value="Rocket League">Rocket League</option> 
                                        <option value="EA FC">EA FC</option> 
                                        <option value="Fortnite">Fortnite</option> 
                                        <option value="Tom Clancy's Rainbow Six Siege">Tom Clancy's Rainbow Six Siege</option> 
                                        <option value="League of Legends">League of Legends</option> 
                                    </select>
                                </div>

                                <select name="privacidade" id="privacidade" class="privacidade">
                                    <option value="" disabled selected>TIPO DE PRIVACIDADE</option>
                                    <option value="publica">PÚBLICA</option>
                                    <option value="privada">PRIVADA</option>
                                </select>
                            </div>

                            <textarea name="descricao" id="descricao" placeholder="DESCRIÇÃO:"></textarea>

                            <button type="submit">CRIAR EQUIPE:</button>

                        </form>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <div class="conteudo-equipes">
            <table class="tabela-equipes">
                <thead>
                    <tr>
                        <th><i>Escudo</i></th>
                        <th><i>Nome</i></th>
                        <th><i>Jogo</i></th>
                        <th><i>Descrição</i></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($equipes)): ?>
                        <?php foreach ($equipes as $e): ?>
                            <tr>
                                <td>
                                    <a href="detalhes_equipe.php?id=<?= $e['id'] ?>" class="link-equipe">
                                        <img src="./img/logos/<?= htmlspecialchars($e['img']) ?>" alt="<?= htmlspecialchars($e['nome']) ?>" class="img-equipe">
                                    </a>
                                </td>
                                <td>
                                    <a href="detalhes_equipe.php?id=<?= $e['id'] ?>" class="link-equipe">
                                        <?= htmlspecialchars($e['nome']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($e['jogo_equipe']) ?></td>
                                <td><span><?= htmlspecialchars($e['descricao']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 20px;">Nenhuma equipe encontrada para "<?= htmlspecialchars($busca) ?>".</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php require_once __DIR__ . "/rodape.php" ?>
    </section>
</body>

</html>