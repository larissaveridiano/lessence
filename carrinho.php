<?php
session_name('LESSENCE_SESSION');
session_start();

require_once "configuracao/conexao.php";

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

if (empty($_SESSION['token_carrinho'])) {
    $_SESSION['token_carrinho'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = (int) ($_POST['produto_id'] ?? 0);
    $quantidade = (int) ($_POST['quantidade'] ?? 1);

    if (!hash_equals(
        $_SESSION['token_carrinho'],
        $_POST['token'] ?? ''
    )) {

        $_SESSION['mensagem_carrinho'] = "Solicitação inválida.";

    } elseif (isset($_POST['remover'])) {

        unset($_SESSION['carrinho'][$id]);

        $_SESSION['mensagem_carrinho'] =
            "Produto removido do carrinho.";

    } elseif (
        isset($_POST['adicionar']) ||
        isset($_POST['atualizar'])
    ) {

        $consulta = $conexao->prepare(
            "SELECT id, estoque FROM produtos WHERE id = ?"
        );

        $consulta->execute([$id]);

        $produto = $consulta->fetch();

        if (isset($_POST['adicionar'])) {

            $quantidade += $_SESSION['carrinho'][$id] ?? 0;

        }

        if (!$produto) {

            $_SESSION['mensagem_carrinho'] =
                "Produto não encontrado.";

        } elseif ($quantidade < 1) {

            $_SESSION['mensagem_carrinho'] =
                "Quantidade inválida.";

        } elseif ($quantidade > $produto['estoque']) {

            $_SESSION['mensagem_carrinho'] =
                "Quantidade superior ao estoque disponível.";

        } else {

            $_SESSION['carrinho'][$id] = $quantidade;

            $_SESSION['mensagem_carrinho'] =
                "Carrinho atualizado com sucesso.";

        }
    }

    header("Location: carrinho.php");
    exit;
}

$mensagem = $_SESSION['mensagem_carrinho'] ?? '';

unset($_SESSION['mensagem_carrinho']);

$itens = [];
$total = 0;
$estoqueValido = true;

foreach ($_SESSION['carrinho'] as $id => $quantidade) {

    $consulta = $conexao->prepare(
        "SELECT * FROM produtos WHERE id = ?"
    );

    $consulta->execute([$id]);

    $produto = $consulta->fetch();

    if (!$produto) {

        unset($_SESSION['carrinho'][$id]);
        continue;

    }

    $produto['quantidade'] = $quantidade;

    $produto['subtotal'] =
        $produto['preco'] * $quantidade;

    if ($quantidade > $produto['estoque']) {
        $estoqueValido = false;
    }

    $total += $produto['subtotal'];

    $itens[] = $produto;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Carrinho | L'Essence</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="estilo.css">

</head>

<body>

<div class="faixa-superior">
    PERFUMARIA SELECIONADA
</div>

<header class="cabecalho">

    <a href="index.php" class="logo">
        L'ESSENCE
        <span>PERFUMARIA</span>
    </a>

    <nav class="menu">

        <a href="index.php">Início</a>

        <a href="index.php?grupo=femininos#catalogo">
            Femininos
        </a>

        <a href="index.php?grupo=masculinos#catalogo">
            Masculinos
        </a>

        <a href="index.php?grupo=arabes#catalogo">
            Árabes
        </a>

        <?php if (isset($_SESSION['usuario_id'])): ?>

            <?php if (($_SESSION['usuario_tipo'] ?? '') === 'admin'): ?>

                <a href="admin/index.php">Administração</a>

            <?php endif; ?>

            <a href="logout.php">Sair</a>

        <?php else: ?>

            <a href="login.php">Entrar</a>

        <?php endif; ?>

        <a href="carrinho.php">Carrinho</a>

    </nav>

</header>

<main class="container pagina-carrinho">

    <div class="titulo-carrinho">

        <span class="subtitulo">
            SUA SELEÇÃO DE FRAGRÂNCIAS
        </span>

        <h1>Meu carrinho</h1>

        <p>
            Confira os perfumes escolhidos.
        </p>

    </div>

    <?php if ($mensagem !== ''): ?>

        <div class="mensagem-carrinho">
            <?= htmlspecialchars($mensagem) ?>
        </div>

    <?php endif; ?>

    <?php if (empty($itens)): ?>

        <div class="carrinho-vazio">

            <h2>Seu carrinho está vazio.</h2>

            <p>
                Descubra nossa coleção e encontre
                sua próxima fragrância.
            </p>

            <a href="index.php#catalogo"
               class="botao-continuar">

                EXPLORAR PERFUMES

            </a>

        </div>

    <?php else: ?>

        <div class="row g-4">

            <div class="col-lg-8">

                <div class="lista-carrinho">

                    <?php foreach ($itens as $item): ?>

                        <article class="item-carrinho">

                            <div class="foto-carrinho">

                                <img
                                    src="imagens/<?= htmlspecialchars($item['imagem']) ?>"
                                    alt="<?= htmlspecialchars($item['nome']) ?>">

                            </div>

                            <div class="dados-carrinho">

                                <span class="marca">
                                    <?= htmlspecialchars($item['marca']) ?>
                                </span>

                                <h2>
                                    <?= htmlspecialchars($item['nome']) ?>
                                </h2>

                                <p class="valor-unitario">

                                    R$ <?= number_format(
                                        $item['preco'], 2, ',', '.'
                                    ) ?>

                                    por unidade

                                </p>

                                <?php if ($item['quantidade'] > $item['estoque']): ?>

                                    <p class="text-danger">
                                        Estoque insuficiente.
                                        Disponível:
                                        <?= $item['estoque'] ?>
                                    </p>

                                <?php endif; ?>

                                <form method="POST"
                                      class="alterar-quantidade">

                                    <input type="hidden"
                                           name="produto_id"
                                           value="<?= $item['id'] ?>">

                                    <input type="hidden"
                                           name="token"
                                           value="<?= htmlspecialchars($_SESSION['token_carrinho']) ?>">

                                    <input type="number"
                                           name="quantidade"
                                           aria-label="Quantidade"
                                           min="1"
                                           max="<?= $item['estoque'] ?>"
                                           value="<?= $item['quantidade'] ?>"
                                           required>

                                    <button type="submit"
                                            name="atualizar">

                                        Atualizar

                                    </button>

                                    <button type="submit"
                                            name="remover"
                                            class="remover-produto"
                                            formnovalidate>

                                        Remover

                                    </button>

                                </form>

                            </div>

                            <div class="subtotal-produto">

                                <span>Subtotal</span>

                                <strong>

                                    R$ <?= number_format(
                                        $item['subtotal'], 2, ',', '.'
                                    ) ?>

                                </strong>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

                <a href="index.php#catalogo"
                   class="continuar-comprando">

                    ← Continuar comprando

                </a>

            </div>

            <div class="col-lg-4">

                <aside class="resumo-pedido">

                    <span class="subtitulo">
                        SUA COMPRA
                    </span>

                    <h2>Resumo do pedido</h2>

                    <div class="linha-resumo">

                        <span>Quantidade de itens</span>

                        <span>
                            <?= array_sum(
                                array_column($itens, 'quantidade')
                            ) ?>
                        </span>

                    </div>

                    <div class="linha-resumo">

                        <span>Subtotal</span>

                        <span>

                            R$ <?= number_format(
                                $total, 2, ',', '.'
                            ) ?>

                        </span>

                    </div>

                    <div class="total-carrinho">

                        <span>Total</span>

                        <strong>

                            R$ <?= number_format(
                                $total, 2, ',', '.'
                            ) ?>

                        </strong>

                    </div>

                    <?php if (!$estoqueValido): ?>

                        <p class="text-danger">
                            Ajuste as quantidades antes de continuar.
                        </p>

                    <?php elseif (isset($_SESSION['usuario_id'])): ?>

                        <a href="checkout.php"
                           class="botao-finalizar">

                            FINALIZAR COMPRA

                        </a>

                    <?php else: ?>

                        <a href="login.php"
                           class="botao-finalizar">

                            ENTRAR PARA FINALIZAR

                        </a>

                    <?php endif; ?>

                    <p class="aviso-compra">

                        Compra demonstrativa —
                        projeto acadêmico.

                    </p>

                </aside>

            </div>

        </div>

    <?php endif; ?>

</main>

<footer class="rodape">

    <h2>L'ESSENCE</h2>

    <p>Sua essência. Sua assinatura.</p>

    <small>
        © 2026 L'Essence — Projeto acadêmico.
    </small>

</footer>

<script src="javascript.js"></script>

</body>
</html>