<?php
require_once __DIR__ . "/init.php";

if (!isset($_SESSION['cadastrado']['id'])) {
    header("Location: entrarConta.php");
    exit;
}

$id_usuario = $_SESSION['cadastrado']['id'];
$username = $_SESSION['cadastrado']['username'];

if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['id']) && $_GET['id'] != "" && $_GET['id'] != null) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
} else {
    header("Location: torneios.php");
    exit;
}

if (!$id) {
    header("Location: torneios.php");
    exit;
}

$torneio = read($pdo, "torneios", "id = ?", [$id]);
if (!$torneio) {
    header("Location: torneios.php");
    exit;
}

$slug = strtolower(str_replace(' ', '-', $torneio['img']));
$texto = "";

if ($torneio['nmjogo_torneio'] == "Counter-Strike 2") {
    $texto = "destaque-cs2";
} elseif ($torneio['nmjogo_torneio'] == "Valorant") {
    $texto = "destaque-valorant";
} elseif ($torneio['nmjogo_torneio'] == "Rocket League") {
    $texto = "destaque-rocket";
} elseif ($torneio['nmjogo_torneio'] == "Fortnite") {
    $texto = "destaque-fortnite";
} elseif ($torneio['nmjogo_torneio'] == "EA FC") {
    $texto = "texto-sobreposto-fc";
} elseif ($torneio['nmjogo_torneio'] == "Tom Clancy's Rainbow Six Siege") {
    $texto = "texto-sobreposto-r6";
} elseif ($torneio['nmjogo_torneio'] == "League of Legends") {
    $texto = "texto-sobreposto-lol";
}

$sql = "SELECT e.id, e.nome, e.jogo_equipe
        FROM usuarios u
        INNER JOIN equipes e ON u.id_equipe = e.id
        WHERE u.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_usuario]);
$equipe = $stmt->fetch(PDO::FETCH_ASSOC);

$integrantes = [];
$ja_inscrita = false;

if ($equipe) {
    $inscricao = read(
        $pdo,
        "torneios_equipes",
        "id_equipe = ? AND id_torneio = ?",
        [$equipe['id'], $torneio['id']]
    );

    if ($inscricao) {
        $ja_inscrita = true;
    } else {
        $integrantes = readAll(
            $pdo,
            "usuarios",
            "id_equipe = ? ORDER BY username_conta",
            [$equipe['id']]
        );
    }
}

$equipe_compativel = $equipe && $equipe['jogo_equipe'] == $torneio['nmjogo_torneio'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/inscricaoTorneio.css">
    <title>GameHub</title>
</head>
<body>
    <?php require_once __DIR__ . "/cabecalho.php" ?>
    <main class="inscricao">
        <section class="resumo">
            <h1 class="resumo__titulo">
                <span class="<?= $texto ?>">
                    <?= htmlspecialchars($torneio['nome_torneio']) ?>
                </span>
            </h1>
            <img class="resumo__imagem"
                src="./img/<?= htmlspecialchars($slug) ?>.jpg"
                alt="Banner do torneio">
            <ul class="resumo__info">
                <li>
                    Formato de equipe:
                    <span class="<?= $texto ?>">
                        <?= htmlspecialchars($torneio['format_torneio']) ?>
                    </span>
                </li>
                <li>
                    Premiação de:
                    <span class="<?= $texto ?>">
                        R$: <?= number_format($torneio['premiacao'], 2, ',', '.') ?>
                    </span>
                </li>
                <li>
                    Início:
                    <span class="<?= $texto ?>">
                        <?= htmlspecialchars($torneio['horainit_torneio']) ?>
                    </span>
                </li>
                <li>
                    Data:
                    <span class="<?= $texto ?>">
                        <?= date('d/m/Y', strtotime($torneio['data_torneio'])) ?>
                    </span>
                </li>
            </ul>
        </section>
        <section class="formulario">
            <h2 class="formulario__titulo">Inscrição da equipe</h2>
            <?php if (!$equipe): ?>
                <p>Você precisa fazer parte de uma equipe para realizar a inscrição.</p>
            <?php elseif ($ja_inscrita): ?>
                <div class="erro-inscricao">
                    <h3>Equipe já inscrita!</h3>
                    <p>Sua equipe já está inscrita neste torneio.</p>
                    <a class="botao botao--secundario" href="./torneios.php">Voltar aos torneios</a>
                </div>
            <?php elseif (!$equipe_compativel): ?>
                <div class="erro-inscricao">
                    <h3>Equipe incompatível!</h3>
                    <p>Sua equipe joga <?= htmlspecialchars($equipe['jogo_equipe']) ?>, mas este torneio é de <?= htmlspecialchars($torneio['nmjogo_torneio']) ?>.</p>
                    <p>Não é possível inscrever sua equipe neste torneio.</p>
                    <a class="botao botao--secundario" href="./torneios.php">Voltar aos torneios</a>
                </div>
            <?php else: ?>
                <form action="processarInscricao.php" method="post">
                    <input type="hidden" name="id_torneio" value="<?= $torneio['id'] ?>">
                    <input type="hidden" name="id_equipe" value="<?= $equipe['id'] ?>">
                    <fieldset class="grupo">
                        <legend>Equipe</legend>
                        <div class="campo">
                            <label for="equipe">Nome da equipe</label>
                            <input
                                type="text"
                                id="equipe"
                                name="equipe"
                                value="<?= htmlspecialchars($equipe['nome']) ?>"
                                readonly
                            >
                        </div>
                    </fieldset>
                    <fieldset class="grupo">
                        <legend>Jogadores</legend>
                        <div class="campo">
                            <label for="jogador1">Capitão</label>
                            <select id="jogador1" name="jogadores[]">
                                <option value="<?= htmlspecialchars($username) ?>" selected>
                                    <?= htmlspecialchars($username) ?>
                                </option>
                            </select>
                        </div>
                        <?php for ($i = 2; $i <= 5; $i++): ?>
                            <div class="campo">
                                <label for="jogador<?= $i ?>">
                                    Jogador <?= $i ?>
                                </label>
                                <select id="jogador<?= $i ?>" name="jogadores[]">
                                    <option value="">Selecione um jogador</option>
                                    <?php foreach ($integrantes as $integrante): ?>
                                        <?php if ($integrante['id'] != $id_usuario): ?>
                                            <option value="<?= htmlspecialchars($integrante['username_conta']) ?>">
                                                <?= htmlspecialchars($integrante['username_conta']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endfor; ?>
                        <div class="campo">
                            <label for="reserva">
                                Reserva <small>(opcional)</small>
                            </label>
                            <select id="reserva" name="reserva">
                                <option value="">Sem reserva</option>
                                <?php foreach ($integrantes as $integrante): ?>
                                    <?php if ($integrante['id'] != $id_usuario): ?>
                                        <option value="<?= htmlspecialchars($integrante['username_conta']) ?>">
                                            <?= htmlspecialchars($integrante['username_conta']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <label class="termos">
                            <input type="checkbox" name="termos">
                            <span>Li e aceito o regulamento do torneio.</span>
                        </label>
                    </fieldset>
                    <div class="acoes">
                        <a class="botao botao--secundario" href="./torneios.php">
                            Voltar
                        </a>
                        <button type="submit" class="botao">
                            Confirmar inscrição
                        </button>
                        <p>
                            Taxa: <?= number_format($torneio['taxa'], 2, ',', '.') ?>
                        </p>
                    </div>
                </form>
            <?php endif; ?>
        </section>
    </main>
    <?php require_once __DIR__ . "/rodape.php" ?>
</body>
</html>