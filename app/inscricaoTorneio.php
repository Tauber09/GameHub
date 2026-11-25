<?php
require_once __DIR__ . "/init.php"

?>

<!DOCTYPE html>
<html lang="en">
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
                <span class="destaque">PHANTOM</span>
                TOURNAMENT
            </h1>

            <img class="resumo__imagem" src="./img/phantom-tournament.jpg" alt="Banner de Valorant">

            <ul class="resumo__info">
                <li>Formato de equipe: <span class="destaque">Pontos corridos</span></li>
                <li>Premiação de <span class="destaque">R$ 10.000</span></li>
                <li>Início: <span class="destaque">16:00</span></li>
                <li>Data: 20/11/2026</li>
            </ul>
        </section>


        <section class="formulario">
            <h2 class="formulario__titulo">Inscrição da equipe</h2>

            <form action="" method="post">

                <fieldset class="grupo">
                    <legend>Equipe</legend>

                    <div class="campo">
                        <label for="equipe">Nome da equipe</label>
                        <input type="text" id="equipe" name="equipe" maxlength="40" required>
                    </div>

                    <div class="linha">
                        <div class="campo">
                            <label for="tag">Tag</label>
                            <input type="text" id="tag" name="tag" maxlength="5" required>
                        </div>

                </fieldset>

                <fieldset class="grupo">
                    <legend>Jogadores</legend>

                    <div class="campo">
                        <label for="jogador1">Capitão</label>
                        <input type="text" id="jogador1" name="jogadores[]" placeholder="Nick#TAG" required>
                    </div>
                    <div class="campo">
                        <label for="jogador2">Jogador 2</label>
                        <input type="text" id="jogador2" name="jogadores[]" placeholder="Nick#TAG" required>
                    </div>
                    <div class="campo">
                        <label for="jogador3">Jogador 3</label>
                        <input type="text" id="jogador3" name="jogadores[]" placeholder="Nick#TAG" required>
                    </div>
                    <div class="campo">
                        <label for="jogador4">Jogador 4</label>
                        <input type="text" id="jogador4" name="jogadores[]" placeholder="Nick#TAG" required>
                    </div>
                    <div class="campo">
                        <label for="jogador5">Jogador 5</label>
                        <input type="text" id="jogador5" name="jogadores[]" placeholder="Nick#TAG" required>
                    </div>
                    <div class="campo">
                        <label for="reserva">Reserva <small>(opcional)</small></label>
                        <input type="text" id="reserva" name="reserva" placeholder="Nick#TAG">
                    </div>

                    <label class="termos">
                    <input type="checkbox" name="termos" required>
                    <span>Li e aceito o regulamento do torneio.</span>
                </label>
                </fieldset>


                <div class="acoes">
                    <a class="botao botao--secundario" href="./torneios.php">Voltar</a>
                    <button type="submit" class="botao">Confirmar inscrição</button>
                </div>
            </form>
        </section>
        
    </main>
    <?php require_once __DIR__ . "/rodape.php" ?>
</body>
</html>