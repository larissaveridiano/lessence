<?php
session_name('LESSENCE_SESSION');
session_start();

require_once "configuracao/conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['token_checkout'])) {
    $_SESSION['token_checkout'] = bin2hex(random_bytes(32));
}

$mensagem = "";
$pedidoConfirmado = $_SESSION['pedido_confirmado'] ?? null;

unset($_SESSION['pedido_confirmado']);

$carrinho = $_SESSION['carrinho'] ?? [];

if (empty($carrinho) && !$pedidoConfirmado) {
    header("Location: carrinho.php");
    exit;
}

/* Finalizar compra */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pagamento = $_POST['pagamento'] ?? '';

    $formasPermitidas = [
        'PIX',
        'Boleto',
        'Cartão'
    ];

    if (!hash_equals(
        $_SESSION['token_checkout'],
        $_POST['token'] ?? ''
    )) {

        $mensagem = "Solicitação inválida.";

    } elseif (!in_array($pagamento, $formasPermitidas, true)) {

        $mensagem = "Selecione uma forma de pagamento.";

    } elseif (empty($carrinho)) {

        $mensagem = "Seu carrinho está vazio.";

    } else {

        try {

            $conexao->beginTransaction();

            $totalCompra = 0;
            $produtosCompra = [];

            ksort($carrinho);

            foreach ($carrinho as $id => $quantidade) {

                $consulta = $conexao->prepare(
                    "SELECT * FROM produtos
                     WHERE id = ?
                     FOR UPDATE"
                );

                $consulta->execute([$id]);

                $produto = $consulta->fetch();

                if (
                    !$produto ||
                    $quantidade < 1 ||
                    $quantidade > $produto['estoque']
                ) {

                    throw new DomainException(
                        "Estoque insuficiente. Confira seu carrinho."
                    );

                }

                $totalCompra +=
                    $produto['preco'] * $quantidade;

                $produtosCompra[] = [
                    'id' => $produto['id'],
                    'quantidade' => $quantidade,
                    'preco' => $produto['preco']
                ];
            }

            /* Registrar pedido */

            $consulta = $conexao->prepare(
                "INSERT INTO pedidos
                (usuario_id, total, pagamento)
                VALUES (?, ?, ?)"
            );

            $consulta->execute([
                $_SESSION['usuario_id'],
                $totalCompra,
                $pagamento
            ]);

            $pedidoId = $conexao->lastInsertId();

            /* Registrar itens e atualizar estoque */

            foreach ($produtosCompra as $item) {

                $consulta = $conexao->prepare(
                    "INSERT INTO itens_pedido
                    (pedido_id, produto_id,
                     quantidade, preco_unitario)
                    VALUES (?, ?, ?, ?)"
                );

                $consulta->execute([
                    $pedidoId,
                    $item['id'],
                    $item['quantidade'],
                    $item['preco']
                ]);

                $consulta = $conexao->prepare(
                    "UPDATE produtos
                     SET estoque = estoque - ?
                     WHERE id = ?"
                );

                $consulta->execute([
                    $item['quantidade'],
                    $item['id']
                ]);
            }

            $conexao->commit();

            $_SESSION['carrinho'] = [];

            $_SESSION['pedido_confirmado'] = [
                'id' => $pedidoId,
                'total' => $totalCompra,
                'pagamento' => $pagamento
            ];

            unset($_SESSION['token_checkout']);

            header("Location: checkout.php");
            exit;

        } catch (Exception $erro) {

            if ($conexao->inTransaction()) {
                $conexao->rollBack();
            }

            if ($erro instanceof DomainException) {

                $mensagem = $erro->getMessage();

            } else {

                $mensagem =
                    "Não foi possível concluir o pedido. Tente novamente.";

            }
        }
    }
}

/* Carregar resumo do carrinho */

$itens = [];
$total = 0;
$estoqueValido = true;

foreach ($carrinho as $id => $quantidade) {

    $consulta = $conexao->prepare(
        "SELECT * FROM produtos WHERE id = ?"
    );

    $consulta->execute([$id]);

    $produto = $consulta->fetch();

    if (!$produto) {
        $estoqueValido = false;
        continue;
    }

    if ($quantidade < 1 || $quantidade > $produto['estoque']) {
        $estoqueValido = false;
    }

    $produto['quantidade'] = $quantidade;

    $produto['subtotal'] =
        $produto['preco'] * $quantidade;

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

    <title>Finalizar compra | L'Essence</title>

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

        <?php if (($_SESSION['usuario_tipo'] ?? '') === 'admin'): ?>

            <a href="admin/index.php">Administração</a>

        <?php endif; ?>

        <a href="logout.php">Sair</a>

        <a href="carrinho.php">Carrinho</a>

    </nav>

</header>

<main class="container pagina-checkout">

    <?php if ($pedidoConfirmado): ?>

        <div class="pedido-confirmado">

            <div class="icone-confirmacao">✓</div>

            <span class="subtitulo">
                OBRIGADA PELA PREFERÊNCIA
            </span>

            <h1>Compra realizada com sucesso!</h1>

            <p>
                Seu pedido foi registrado na L'Essence.
            </p>

            <div class="dados-confirmacao">

                <p>

                    Número do pedido:

                    <strong>
                        #<?= $pedidoConfirmado['id'] ?>
                    </strong>

                </p>

                <p>

                    Forma de pagamento:

                    <strong>
                        <?= htmlspecialchars(
                            $pedidoConfirmado['pagamento']
                        ) ?>
                    </strong>

                </p>

                <p>

                    Valor total:

                    <strong>

                        R$ <?= number_format(
                            $pedidoConfirmado['total'],
                            2, ',', '.'
                        ) ?>

                    </strong>

                </p>

            </div>

            <p class="aviso-compra">
                Pagamento simulado para fins acadêmicos.
            </p>

            <a href="index.php"
               class="botao-finalizar">

                VOLTAR À LOJA

            </a>

        </div>

    <?php else: ?>

        <div class="titulo-checkout">

            <span class="subtitulo">
                ÚLTIMA ETAPA DA SUA EXPERIÊNCIA
            </span>

            <h1>Finalizar compra</h1>

            <p>
                Confira seu pedido e escolha a forma de pagamento.
            </p>

        </div>

        <?php if ($mensagem !== ''): ?>

            <div class="alert alert-danger">

                <?= htmlspecialchars($mensagem) ?>

            </div>

        <?php endif; ?>

        <div class="row g-4">

            <div class="col-lg-7">

                <div class="painel-checkout">

                    <span class="subtitulo">
                        IDENTIFICAÇÃO
                    </span>

                    <h2>Seus dados</h2>

                    <p>

                        Olá,

                        <strong>
                            <?= htmlspecialchars(
                                $_SESSION['usuario_nome']
                            ) ?>!
                        </strong>

                    </p>

                    <p>
                        Você está logada(o) e pode finalizar
                        sua compra.
                    </p>

                </div>

                <div class="painel-checkout">

                    <span class="subtitulo">
                        PAGAMENTO
                    </span>

                    <h2>Forma de pagamento</h2>

                    <p>
                        Selecione a opção de sua preferência.
                    </p>

                    <form method="POST" id="form-checkout">

                        <input type="hidden"
                               name="token"
                               value="<?= htmlspecialchars(
                                   $_SESSION['token_checkout']
                               ) ?>">

                        <label class="opcao-pagamento">

                            <input type="radio"
                                   name="pagamento"
                                   value="PIX"
                                   required>

                            <div>

                                <strong>PIX</strong>

                                <span>
                                    Pagamento instantâneo.
                                </span>

                            </div>

                        </label>

                        <label class="opcao-pagamento">

                            <input type="radio"
                                   name="pagamento"
                                   value="Boleto"
                                   required>

                            <div>

                                <strong>Boleto bancário</strong>

                                <span>
                                    Pagamento por boleto.
                                </span>

                            </div>

                        </label>

                        <label class="opcao-pagamento">

                            <input type="radio"
                                   name="pagamento"
                                   value="Cartão"
                                   required>

                            <div>

                                <strong>Cartão de crédito</strong>

                                <span>
                                    Pagamento por cartão.
                                </span>

                            </div>

                        </label>

                    </form>

                    <div class="aviso-pagamento">

                        Este é um ambiente demonstrativo.
                        Nenhum pagamento real será realizado.

                    </div>

                </div>

                <a href="carrinho.php"
                   class="continuar-comprando">

                    ← Voltar ao carrinho

                </a>

            </div>

            <div class="col-lg-5">

                <aside class="resumo-pedido">

                    <span class="subtitulo">
                        SUA SELEÇÃO
                    </span>

                    <h2>Resumo do pedido</h2>

                    <?php foreach ($itens as $item): ?>

                        <div class="item-checkout">

                            <img
                                src="imagens/<?= htmlspecialchars($item['imagem']) ?>"
                                alt="<?= htmlspecialchars($item['nome']) ?>">

                            <div class="dados-item-checkout">

                                <strong>

                                    <?= htmlspecialchars(
                                        $item['nome']
                                    ) ?>

                                </strong>

                                <span>

                                    Quantidade:
                                    <?= $item['quantidade'] ?>

                                </span>

                            </div>

                            <strong class="preco-item-checkout">

                                R$ <?= number_format(
                                    $item['subtotal'],
                                    2, ',', '.'
                                ) ?>

                            </strong>

                        </div>

                    <?php endforeach; ?>

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

                    <?php if ($estoqueValido): ?>

                        <button type="submit"
                                form="form-checkout"
                                class="botao-confirmar-compra">

                            CONFIRMAR PEDIDO

                        </button>

                    <?php else: ?>

                        <p class="text-danger">

                            Estoque insuficiente.
                            Volte ao carrinho para ajustar
                            os produtos.

                        </p>

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

</body>
</html>