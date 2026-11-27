<?php

require_once __DIR__ . "/init.php";

unset($_SESSION['cadastrado']);

header("Location: index.php");
exit;