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

$pedidos = $conexao->query(
    "SELECT pedidos.*, usuarios.nome AS cliente,
            usuarios.email
     FROM pedidos
     INNER JOIN usuarios
     ON pedidos.usuario_id = usuarios.id
     ORDER BY pedidos.id DESC"
)->fetchAll();

$pedidoSelecionado = null;
$itens = [];

if (isset($_GET['pedido'])) {

    $id = (int) $_GET['pedido'];

    $consulta = $conexao->prepare(
        "SELECT pedidos.*, usuarios.nome AS cliente
         FROM pedidos
         INNER JOIN usuarios
         ON pedidos.usuario_id = usuarios.id
         WHERE pedidos.id = ?"
    );

    $consulta->execute([$id]);

    $pedidoSelecionado = $consulta->fetch();

    if ($pedidoSelecionado) {

        $consulta = $conexao->prepare(
            "SELECT itens_pedido.*,
                    produtos.nome,
                    produtos.marca
             FROM itens_pedido
             INNER JOIN produtos
             ON itens_pedido.produto_id = produtos.id
             WHERE itens_pedido.pedido_id = ?"
        );

        $consulta->execute([$id]);

        $itens = $consulta->fetchAll();

    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Pedidos | L'Essence Administração</title>

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
            GESTÃO DE VENDAS
        </span>

        <h1>Pedidos realizados</h1>

        <p>
            Acompanhe as compras da L'Essence.
        </p>

    </div>

    <?php if ($pedidoSelecionado): ?>

        <section class="painel-admin" id="detalhes">

            <h2>

                Detalhes do pedido
                #<?= $pedidoSelecionado['id'] ?>

            </h2>

            <p>

                Cliente:

                <strong>

                    <?= htmlspecialchars(
                        $pedidoSelecionado['cliente']
                    ) ?>

                </strong>

            </p>

            <p>

                Pagamento:

                <?= htmlspecialchars(
                    $pedidoSelecionado['pagamento']
                ) ?>

            </p>

            <div class="table-responsive">

                <table class="tabela-admin">

                    <thead>

                        <tr>
                            <th>Perfume</th>
                            <th>Quantidade</th>
                            <th>Preço unitário</th>
                            <th>Subtotal</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($itens as $item): ?>

                            <tr>

                                <td>

                                    <?= htmlspecialchars($item['nome']) ?>

                                </td>

                                <td>

                                    <?= $item['quantidade'] ?>

                                </td>

                                <td>

                                    R$ <?= number_format(
                                        $item['preco_unitario'],
                                        2, ',', '.'
                                    ) ?>

                                </td>

                                <td>

                                    R$ <?= number_format(
                                        $item['preco_unitario'] *
                                        $item['quantidade'],
                                        2, ',', '.'
                                    ) ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

            <div class="total-carrinho">

                <span>Total do pedido</span>

                <strong>

                    R$ <?= number_format(
                        $pedidoSelecionado['total'],
                        2, ',', '.'
                    ) ?>

                </strong>

            </div>

            <a href="pedidos.php"
               class="cancelar-edicao">

                Fechar detalhes

            </a>

        </section>

    <?php endif; ?>

    <section class="painel-admin">

        <h2>Histórico de pedidos</h2>

        <p class="descricao-conta">

            <?= count($pedidos) ?> pedidos registrados.

        </p>

        <?php if (empty($pedidos)): ?>

            <div class="nenhum-produto">

                Nenhum pedido realizado até o momento.

            </div>

        <?php else: ?>

            <div class="table-responsive">

                <table class="tabela-admin">

                    <thead>

                        <tr>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Pagamento</th>
                            <th>Total</th>
                            <th>Ações</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($pedidos as $pedido): ?>

                            <tr>

                                <td>

                                    #<?= $pedido['id'] ?>

                                </td>

                                <td>

                                    <strong>

                                        <?= htmlspecialchars($pedido['cliente']) ?>

                                    </strong>

                                    <small>

                                        <?= htmlspecialchars($pedido['email']) ?>

                                    </small>

                                </td>

                                <td>

                                    <?= date(
                                        'd/m/Y H:i',
                                        strtotime($pedido['data_pedido'])
                                    ) ?>

                                </td>

                                <td>

                                    <?= htmlspecialchars($pedido['pagamento']) ?>

                                </td>

                                <td>

                                    R$ <?= number_format(
                                        $pedido['total'],
                                        2, ',', '.'
                                    ) ?>

                                </td>

                                <td>

                                    <a class="botao-editar"
                                       href="pedidos.php?pedido=<?= $pedido['id'] ?>#detalhes">

                                        Ver detalhes

                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </section>

</main>

<footer class="rodape">

    <h2>L'ESSENCE</h2>

    <p>Sua essência. Sua assinatura.</p>

    <small>© 2026 L'Essence — Administração.</small>

</footer>

</body>
</html>