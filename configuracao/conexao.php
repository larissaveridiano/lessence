<?php

$config = parse_ini_file(__DIR__ . '/../.env');

if ($config === false) {
    die("Arquivo de configuração não encontrado.");
}

try {
    $conexao = new PDO(
        "mysql:host=" . $config['DB_HOST'] .
        ";dbname=" . $config['DB_NAME'] .
        ";charset=utf8mb4",
        $config['DB_USER'],
        $config['DB_PASS']
    );

    $conexao->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $conexao->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $erro) {
    die("Erro ao conectar ao banco de dados.");
}
