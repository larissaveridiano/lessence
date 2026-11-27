<?php
session_name('LESSENCE_SESSION');
session_start();

require_once "configuracao/conexao.php";

$id = (int) ($_GET['id'] ?? 0);

$consulta = $conexao->prepare(
    "SELECT produtos.*, categorias.nome AS categoria
     FROM produtos
     INNER JOIN categorias
     ON produtos.categoria_id = categorias.id
     WHERE produtos.id = ?"
);

$consulta->execute([$id]);

$produto = $consulta->fetch();

if (!$produto) {
    header("Location: index.php");
    exit;
}

if (empty($_SESSION['token_carrinho'])) {
    $_SESSION['token_carrinho'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($produto['nome']) ?> | L'Essence
    </title>

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

<main class="container pagina-detalhes">

    <div class="caminho">

        <a href="index.php">Início</a>

        /

        <a href="index.php?categoria=<?= $produto['categoria_id'] ?>#catalogo">
            <?= htmlspecialchars($produto['categoria']) ?>
        </a>

        /

        <?= htmlspecialchars($produto['nome']) ?>

    </div>

    <div class="row g-5">

        <div class="col-md-6">

            <div class="moldura-perfume">

                <div class="imagem-detalhes">

                    <img
                        src="imagens/<?= htmlspecialchars($produto['imagem']) ?>"
                        alt="<?= htmlspecialchars($produto['nome']) ?>">

                </div>

            </div>

            <p class="legenda-imagem">
                L'ESSENCE — PERFUMARIA SELECIONADA
            </p>

        </div>

        <div class="col-md-6 informacoes-detalhes">

            <span class="categoria-detalhes">
                <?= htmlspecialchars($produto['categoria']) ?>
            </span>

            <h1>
                <?= htmlspecialchars($produto['nome']) ?>
                –
                <?= htmlspecialchars($produto['marca']) ?>
            </h1>

            <div class="linha-dourada"></div>

            <p class="preco-detalhes">

                R$ <?= number_format(
                    $produto['preco'], 2, ',', '.'
                ) ?>

            </p>

            <p class="descricao-detalhes">
                <?= htmlspecialchars($produto['descricao']) ?>
            </p>

            <?php if ($produto['estoque'] > 0): ?>

                <p class="estoque-detalhes">
                    Disponível em estoque —
                    <?= $produto['estoque'] ?> unidades.
                </p>

                <form action="carrinho.php"
                      method="POST"
                      class="formulario-carrinho">

                    <input type="hidden"
                           name="produto_id"
                           value="<?= $produto['id'] ?>">

                    <input type="hidden"
                           name="token"
                           value="<?= htmlspecialchars($_SESSION['token_carrinho']) ?>">

                    <input type="number"
                           name="quantidade"
                           aria-label="Quantidade"
                           value="1"
                           min="1"
                           max="<?= $produto['estoque'] ?>"
                           required>

                    <button type="submit"
                            name="adicionar"
                            class="botao-adicionar">

                        ADICIONAR AO CARRINHO

                    </button>

                </form>

            <?php else: ?>

                <p class="sem-estoque">
                    Produto indisponível no momento.
                </p>

            <?php endif; ?>

        </div>

    </div>

    <section class="identidade-olfativa">

        <span class="subtitulo">
            CONHEÇA A FRAGRÂNCIA
        </span>

        <h2>Identidade olfativa</h2>

        <div class="informacao-olfativa">

            <h3>FRAGRÂNCIA</h3>

            <p>
                <?= htmlspecialchars($produto['nome']) ?>
            </p>

        </div>

        <div class="informacao-olfativa">

            <h3>MARCA</h3>

            <p>
                <?= htmlspecialchars($produto['marca']) ?>
            </p>

        </div>

        <div class="informacao-olfativa">

            <h3>CATEGORIA</h3>

            <p>
                <?= htmlspecialchars($produto['categoria']) ?>
            </p>

        </div>

        <div class="informacao-olfativa">

            <h3>COMPOSIÇÃO E CARACTERÍSTICAS</h3>

            <p>
                <?= htmlspecialchars($produto['descricao']) ?>
            </p>

        </div>

    </section>

</main>

<footer class="rodape">

    <h2>L'ESSENCE</h2>

    <p>Sua essência. Sua assinatura.</p>

    <small>
        © 2026 L'Essence — Projeto acadêmico.
    </small>

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="javascript.js"></script>

</body>
</html>