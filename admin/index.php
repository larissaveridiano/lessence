<?php
session_name('LESSENCE_SESSION');
session_start();

require_once "../configuracao/conexao.php";

if (
    !isset($_SESSION['usuario_id']) ||
    ($_SESSION['usuario_tipo'] ?? '') !== 'admin'
) {
    header("Location: ../login.php");
    exit;
}

$totalProdutos = $conexao->query(
    "SELECT COUNT(*) FROM produtos"
)->fetchColumn();

$totalEstoque = $conexao->query(
    "SELECT COALESCE(SUM(estoque), 0) FROM produtos"
)->fetchColumn();

$totalPedidos = $conexao->query(
    "SELECT COUNT(*) FROM pedidos"
)->fetchColumn();

$totalVendas = $conexao->query(
    "SELECT COALESCE(SUM(total), 0) FROM pedidos"
)->fetchColumn();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Administração | L'Essence</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="../estilo.css">

</head>

<body>

<div class="faixa-superior">
    ÁREA ADMINISTRATIVA
</div>

<header class="cabecalho">

    <a href="index.php" class="logo">
        L'ESSENCE
        <span>ADMINISTRAÇÃO</span>
    </a>

    <nav class="menu">

        <a href="index.php">Painel</a>
        <a href="produtos.php">Produtos</a>
        <a href="pedidos.php">Pedidos</a>
        <a href="../index.php">Voltar à loja</a>
        <a href="../logout.php">Sair</a>

    </nav>

</header>

<main class="container pagina-admin">

    <div class="titulo-admin">

        <span class="subtitulo">
            GESTÃO DA PERFUMARIA
        </span>

        <h1>Painel administrativo</h1>

        <p>
            Gerencie sua perfumaria.
        </p>

    </div>

    <div class="row g-4">

        <div class="col-md-6">

            <div class="card-admin">

                <span>PRODUTOS CADASTRADOS</span>

                <h2><?= $totalProdutos ?></h2>

                <p>Fragrâncias disponíveis no catálogo.</p>

            </div>

        </div>

        <div class="col-md-6">

            <div class="card-admin">

                <span>ESTOQUE TOTAL</span>

                <h2><?= $totalEstoque ?></h2>

                <p>Unidades armazenadas.</p>

            </div>

        </div>

        <div class="col-md-6">

            <div class="card-admin">

                <span>PEDIDOS REALIZADOS</span>

                <h2><?= $totalPedidos ?></h2>

                <p>Compras registradas na loja.</p>

            </div>

        </div>

        <div class="col-md-6">

            <div class="card-admin">

                <span>VALOR TOTAL DAS VENDAS</span>

                <h2>

                    R$ <?= number_format(
                        $totalVendas, 2, ',', '.'
                    ) ?>

                </h2>

                <p>Valor dos pedidos simulados.</p>

            </div>

        </div>

    </div>

    <div class="row g-4 mt-3">

        <div class="col-md-6">

            <div class="acao-admin">

                <h2>Gerenciar produtos</h2>

                <p>
                    Cadastre, edite e exclua perfumes
                    do catálogo.
                </p>

                <a href="produtos.php" class="botao">
                    ACESSAR PRODUTOS
                </a>

            </div>

        </div>

        <div class="col-md-6">

            <div class="acao-admin">

                <h2>Gerenciar pedidos</h2>

                <p>
                    Consulte os pedidos realizados
                    pelos clientes.
                </p>

                <a href="pedidos.php" class="botao">
                    ACESSAR PEDIDOS
                </a>

            </div>

        </div>

    </div>

</main>

<footer class="rodape">

    <h2>L'ESSENCE</h2>

    <p>Sua essência. Sua assinatura.</p>

    <small>© 2026 L'Essence — Administração.</small>

</footer>

</body>
</html>