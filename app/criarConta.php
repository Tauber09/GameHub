<?php

require_once __DIR__ . "/init.php";



?>

<!DOCTYPE html>
<html lang="PT-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar</title>
</head>
<body>
    <?php require_once __DIR__ . "/cabecalho.php" ?>

    <form action="processarCriarConta" method="POST">
        <label for="email">E-mail</label>
        <input type="text" name="email" id="email" placeholder="E-mail">
        <?php if($_SERVER['REQUEST_METHOD'] == "GET" || $_GET['erro'] == "EMAIL"):?>
            <p>E-MAIL SÓ PODE CONTER NO MAXIMO 100 CARACTERIES</p>
        <?php endif ?>  
        <br>
        <label for="username">Username</label>
        <input type="text" name="username" id="username" placeholder="Username">
        <?php if($_SERVER['REQUEST_METHOD'] == "GET" || $_GET['erro'] == "USERNAME"):?>
            <p>USERNAME SÓ PODE CONTER NO MAXIMO 20 CARACTERIES</p>
        <?php endif ?>  
        <br>
        <label for="senha">Senha</label>
        <input type="text" name="senha" id="senha" placeholder="Senha">
        <?php if($_SERVER['REQUEST_METHOD'] == "GET" || $_GET['erro'] == "SENHA"):?>
            <p>SENHA SÓ PODE CONTER NO MAXIMO 50 CARACTERIES</p>
        <?php endif ?>  
        <br>
        <button type="submit">Criar</button>  
    </form>
</body>
</html>