<?php
require_once __DIR__ . "/init.php";
require_once __DIR__ . "/cabecalho.php";

$torneios = readAll($pdo, "torneios");

function normalizarTextoImg(string $texto): string {
    $mapa = [
        'Á' => 'a', 'À' => 'a', 'Ã' => 'a', 'Â' => 'a',
        'É' => 'e', 'Ê' => 'e', 'Í' => 'i', 'Ó' => 'o',
        'Ô' => 'o', 'Õ' => 'o', 'Ú' => 'u', 'Ç' => 'c',
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a',
        'é' => 'e', 'ê' => 'e', 'í' => 'i', 'ó' => 'o',
        'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ç' => 'c'
    ];
    $texto = strtolower(strtr(trim($texto), $mapa));
    return (string)preg_replace('/[^a-z0-9]/', '', $texto);
}

function listarArquivosImagem(string $baseDir): array {
    $extensoesValidas = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
    $pastas = [
        ['disco' => $baseDir . '/img', 'web' => './img/'],
        ['disco' => $baseDir . '/imagens', 'web' => './imagens/'],
        ['disco' => $baseDir . '/uploads', 'web' => './uploads/'],
        ['disco' => dirname($baseDir) . '/img', 'web' => '../img/'],
        ['disco' => dirname($baseDir) . '/uploads', 'web' => '../uploads/'],
    ];

    $arquivos = [];
    foreach ($pastas as $p) {
        if (!is_dir($p['disco'])) {
            continue;
        }
        $itens = @scandir($p['disco']);
        if (!is_array($itens)) {
            continue;
        }
        foreach ($itens as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $caminhoCompleto = $p['disco'] . '/' . $item;
            if (is_file($caminhoCompleto)) {
                $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
                if (!in_array($ext, $extensoesValidas, true)) {
                    continue;
                }
                $semExt = pathinfo($item, PATHINFO_FILENAME);
                $arquivos[] = [
                    'exato'   => strtolower($item),
                    'sem_ext' => strtolower($semExt),
                    'norm'    => normalizarTextoImg($semExt),
                    'web'     => $p['web'] . rawurlencode($item),
                ];
            } elseif (is_dir($caminhoCompleto)) {
                $subItens = @scandir($caminhoCompleto);
                if (!is_array($subItens)) {
                    continue;
                }
                foreach ($subItens as $subItem) {
                    if ($subItem === '.' || $subItem === '..') {
                        continue;
                    }
                    $subCaminho = $caminhoCompleto . '/' . $subItem;
                    if (is_file($subCaminho)) {
                        $ext = strtolower(pathinfo($subItem, PATHINFO_EXTENSION));
                        if (!in_array($ext, $extensoesValidas, true)) {
                            continue;
                        }
                        $semExt = pathinfo($subItem, PATHINFO_FILENAME);
                        $arquivos[] = [
                            'exato'   => strtolower($subItem),
                            'sem_ext' => strtolower($semExt),
                            'norm'    => normalizarTextoImg($semExt),
                            'web'     => $p['web'] . rawurlencode($item) . '/' . rawurlencode($subItem),
                        ];
                    }
                }
            }
        }
    }
    return $arquivos;
}

function buscarImagemTorneio(array $torneio, array $arquivos): string {
    $fallback = './img/Capa-EA-Sports-FC-26-e1752689011939.webp';
    $imgBanco = trim((string)($torneio['img'] ?? ''));

    if ($imgBanco !== '' && (strpos($imgBanco, 'http://') === 0 || strpos($imgBanco, 'https://') === 0 || strpos($imgBanco, 'data:image') === 0)) {
        return $imgBanco;
    }

    $arquivoBanco = strtolower(basename(str_replace('\\', '/', $imgBanco)));
    $semExtBanco  = strtolower(pathinfo($arquivoBanco, PATHINFO_FILENAME));
    $normBanco    = normalizarTextoImg($semExtBanco);
    $normFallback = normalizarTextoImg('Capa-EA-Sports-FC-26-e1752689011939');
    $ehFallbackBanco = ($normBanco === '' || $normBanco === $normFallback);

    if (!$ehFallbackBanco) {
        foreach ($arquivos as $arq) {
            if ($arq['exato'] === $arquivoBanco) {
                return $arq['web'];
            }
        }
        foreach ($arquivos as $arq) {
            if ($arq['sem_ext'] === $semExtBanco || $arq['norm'] === $normBanco) {
                return $arq['web'];
            }
        }
    }

    $normNome = normalizarTextoImg((string)($torneio['nome_torneio'] ?? ''));
    $normJogo = normalizarTextoImg((string)($torneio['jogo'] ?? ''));

    foreach ([$normJogo, $normNome] as $chave) {
        if ($chave === '') {
            continue;
        }
        foreach ($arquivos as $arq) {
            if ($arq['norm'] === $chave) {
                return $arq['web'];
            }
        }
    }

    $chavesBusca = [$normJogo, $normNome];
    if (!$ehFallbackBanco) {
        $chavesBusca[] = $normBanco;
    }

    foreach ($chavesBusca as $chave) {
        if (strlen($chave) < 2) {
            continue;
        }
        $termos = [$chave];
        if (strpos($chave, 'fn') !== false || strpos($chave, 'fortnite') !== false) {
            $termos[] = 'fortnite';
            $termos[] = 'fn';
        }
        if (strpos($chave, 'siege') !== false || strpos($chave, 'r6') !== false || strpos($chave, 'rainbow') !== false) {
            $termos[] = 'siege';
            $termos[] = 'r6';
            $termos[] = 'rainbow';
        }
        if (strpos($chave, 'lol') !== false || strpos($chave, 'league') !== false) {
            $termos[] = 'lol';
            $termos[] = 'league';
        }
        if (strpos($chave, 'master') !== false || strpos($chave, 'rocket') !== false) {
            $termos[] = 'rocket';
            $termos[] = 'master';
            $termos[] = 'pes';
            $termos[] = 'efootball';
        }
        if (strpos($chave, 'phantom') !== false || strpos($chave, 'valorant') !== false) {
            $termos[] = 'valorant';
            $termos[] = 'phantom';
        }
        if (strpos($chave, 'ea') !== false || strpos($chave, 'fc') !== false || strpos($chave, 'fifa') !== false) {
            $termos[] = 'fc26';
            $termos[] = 'eafc';
            $termos[] = 'fifa';
        }
        if (strpos($chave, 'cs') !== false || strpos($chave, 'counter') !== false) {
            $termos[] = 'cs2';
            $termos[] = 'csgo';
            $termos[] = 'counter';
        }

        foreach ($termos as $termo) {
            foreach ($arquivos as $arq) {
                if (strpos($arq['norm'], 'banner') !== false || strpos($arq['norm'], 'logo') !== false) {
                    continue;
                }
                if (strpos($arq['norm'], $termo) !== false || (strlen($arq['norm']) >= 2 && strpos($termo, $arq['norm']) !== false)) {
                    return $arq['web'];
                }
            }
        }
    }

    return $fallback;
}

$arquivosImagens = listarArquivosImagem(__DIR__);

$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');

if ($mes < 1) {
    $mes = 12;
    $ano--;
} elseif ($mes > 12) {
    $mes = 1;
    $ano++;
}

$mesesNomes = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];

$torneiosPorData = [];
foreach ($torneios as $t) {
    $dataBruta = trim((string)($t['data_torneio'] ?? ''));
    if (strpos($dataBruta, '/') !== false) {
        $partes = explode('/', $dataBruta);
        if (count($partes) === 3) {
            $dataChave = "{$partes[2]}-{$partes[1]}-{$partes[0]}";
        } else {
            continue;
        }
    } else {
        $dataChave = $dataBruta !== '' ? date('Y-m-d', strtotime($dataBruta)) : '';
    }
    if ($dataChave !== '') {
        $torneiosPorData[$dataChave][] = $t;
    }
}

$torneioSelecionado = !empty($torneios) ? $torneios[0] : null;
if (isset($_GET['torneio_id'])) {
    foreach ($torneios as $t) {
        if ((int)$t['id'] === (int)$_GET['torneio_id']) {
            $torneioSelecionado = $t;
            break;
        }
    }
}

$imgSrc = $torneioSelecionado ? buscarImagemTorneio($torneioSelecionado, $arquivosImagens) : './img/Capa-EA-Sports-FC-26-e1752689011939.webp';

$primeiroDiaSemana = (int)date('N', strtotime("$ano-$mes-01"));
$diasNoMes = (int)date('t', strtotime("$ano-$mes-01"));
$diasMesAnterior = (int)date('t', strtotime("$ano-$mes-01 -1 month"));

$celulas = [];

for ($i = $primeiroDiaSemana - 1; $i > 0; $i--) {
    $celulas[] = [
        'dia' => $diasMesAnterior - $i + 1,
        'mesAtual' => false,
        'data' => null
    ];
}

for ($d = 1; $d <= $diasNoMes; $d++) {
    $dataFormatada = sprintf('%04d-%02d-%02d', $ano, $mes, $d);
    $celulas[] = [
        'dia' => $d,
        'mesAtual' => true,
        'data' => $dataFormatada
    ];
}

$proximoDia = 1;
while (count($celulas) % 7 !== 0) {
    $celulas[] = [
        'dia' => $proximoDia++,
        'mesAtual' => false,
        'data' => null
    ];
}

$semanas = array_chunk($celulas, 7);
$classesLinha = ['cardcinza', 'cardcinza1', 'cardcinza2', 'cardcinza3', 'cardcinza4', 'cardcinza4'];
$paramTorneio = $torneioSelecionado ? '&torneio_id=' . (int)$torneioSelecionado['id'] : '';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Partidas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/partidas.css">
</head>

<body>
    <div class="tudo">

        <section class="banner-partida">
            <img class="banner-partida__foto" src="./img/BANNER PARTIDA.png" alt="">
            <h1 class="banner-partida__titulo">
                <span class="destaque">Organize</span> suas<br>
                partidas<br>
                e <span class="destaque">aumente</span><br>
                seu <span class="destaque">desempenho</span>
            </h1>
        </section>

        <div class="t1" id="programacao">
            <h1><span class="destaque">Programação</span> de torneios</h1>
        </div>

        <div class="paiCards">

            <div class="card">
                <?php if ($torneioSelecionado): ?>
                    <?php
                        $normClasseNome = normalizarTextoImg((string)($torneioSelecionado['nome_torneio'] ?? ''));
                        $normClasseJogo = normalizarTextoImg((string)($torneioSelecionado['jogo'] ?? ''));
                        $classesImg = trim(($normClasseNome !== '' ? 'img-' . $normClasseNome : '') . ' ' . ($normClasseJogo !== '' ? 'img-' . $normClasseJogo : ''));
                    ?>
                    <img class="<?= htmlspecialchars($classesImg) ?>" src="<?= htmlspecialchars($imgSrc) ?>" alt="">
                    
                    <div class="titulo">
                        <p>data</p>
                    </div>
                    <div class="subP">
                        <p><?= htmlspecialchars($torneioSelecionado['data_torneio']) ?> | <?= htmlspecialchars($torneioSelecionado['horainit_torneio']) ?></p>
                    </div>

                    <div class="titulo2">
                        <p>taxa de entrada</p>
                    </div>
                    <div class="subP2">
                        <p><?= htmlspecialchars($torneioSelecionado['taxa']) ?></p>
                    </div>

                    <div class="titulo3">
                        <p>premiação</p>
                    </div>
                    <div class="subP3">
                        <p><?= htmlspecialchars($torneioSelecionado['premiacao']) ?></p>
                    </div>

                    <div class="titulo4">
                        <p>jogadores</p>
                    </div>
                    <div class="subP4">
                        <p><?= htmlspecialchars($torneioSelecionado['qnt_jogadores']) ?></p>
                    </div>
                <?php else: ?>
                    <img src="./img/Capa-EA-Sports-FC-26-e1752689011939.webp" alt="">
                    <div class="titulo"><p>Nenhum torneio cadastrado</p></div>
                <?php endif; ?>
            </div>

            <div class="card2">
                <div class="nav-mes">
                    <a href="?mes=<?= $mes - 1 ?>&ano=<?= $ano ?><?= $paramTorneio ?>#programacao"><</a>
                    <span><?= $mesesNomes[$mes] ?> <?= $ano ?></span>
                    <a href="?mes=<?= $mes + 1 ?>&ano=<?= $ano ?><?= $paramTorneio ?>#programacao">></a>
                </div>

                <div class="pCard2H">
                    <p>SEG</p>
                    <p>TER</p>
                    <p>QUA</p>
                    <p>QUI</p>
                    <p class="tamanho">SEX</p>
                    <p class="tamanho">SAB</p>
                    <p class="tamanho">DOM</p>
                </div>

                <?php foreach ($semanas as $indiceSemana => $semana): ?>
                    <div class="pCard2">
                        <?php foreach ($semana as $celula): ?>
                            <?php
                                $classeBase = $celula['mesAtual'] ? $classesLinha[$indiceSemana] : 'cardcinza5';
                                $estiloLinhaZero = (!$celula['mesAtual'] && $indiceSemana === 0) ? 'style="margin-top: 0;"' : '';
                                $temTorneio = $celula['data'] && isset($torneiosPorData[$celula['data']]);
                            ?>
                            <div class="<?= $classeBase ?>" <?= $estiloLinhaZero ?>>
                                <span><?= sprintf('%02d', $celula['dia']) ?></span>
                                <?php if ($temTorneio): ?>
                                    <?php foreach ($torneiosPorData[$celula['data']] as $tDia): ?>
                                        <a class="torneio-dia <?= ($torneioSelecionado && (int)$torneioSelecionado['id'] === (int)$tDia['id']) ? 'ativo' : '' ?>"
                                           href="?mes=<?= $mes ?>&ano=<?= $ano ?>&torneio_id=<?= $tDia['id'] ?>#programacao"
                                           title="<?= htmlspecialchars($tDia['nome_torneio']) ?>">
                                            <?= htmlspecialchars($tDia['nome_torneio']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>

        <?php require_once __DIR__ . "/rodape.php" ?>
    </div>
</body>

</html>