<?php
session_name('LESSENCE_SESSION');
session_start();

require_once "configuracao/conexao.php";

$consulta = $conexao->query(
    "SELECT produtos.*, categorias.nome AS categoria
     FROM produtos
     INNER JOIN categorias
     ON produtos.categoria_id = categorias.id
     ORDER BY produtos.id"
);

$produtos = $consulta->fetchAll();

$categorias = $conexao->query(
    "SELECT * FROM categorias ORDER BY id"
)->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>L'Essence | Perfumaria</title>

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
        <a href="index.php?grupo=femininos#catalogo">Femininos</a>
        <a href="index.php?grupo=masculinos#catalogo">Masculinos</a>
        <a href="index.php?grupo=arabes#catalogo">Árabes</a>

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

<section class="banner">

    <div class="banner-conteudo">

        <span class="subtitulo">
            A ARTE DA PERFUMARIA
        </span>

        <h1>Sua essência. Sua assinatura.</h1>

        <p>
            Fragrâncias que traduzem sua personalidade
            e tornam momentos inesquecíveis.
        </p>

        <a href="#catalogo" class="botao">
            EXPLORE A COLEÇÃO
        </a>

    </div>

    <div class="banner-imagem">
        <img src="imagens/banner.jpg"
             alt="Perfumaria L'Essence">
    </div>

</section>



<main class="container catalogo" id="catalogo">

    <div class="titulo-secao">

    <span class="subtitulo">
        DESCUBRA SUA PRÓXIMA FRAGRÂNCIA
    </span>

    <h2>Nossa coleção</h2>

</div>

    <div class="row g-4">

        <?php foreach ($produtos as $produto): ?>

            <div class="col-6 col-md-4 col-lg-3">

                <article class="card-produto">

                    <div class="foto-produto">

                        <img
                            src="imagens/<?= htmlspecialchars($produto['imagem']) ?>"
                            alt="<?= htmlspecialchars($produto['nome']) ?>"
                            loading="lazy">

                    </div>

                    <div class="info-produto">

                        <span class="marca">
                            <?= htmlspecialchars($produto['marca']) ?>
                        </span>

                        <h3>
                            <?= htmlspecialchars($produto['nome']) ?>
                        </h3>

                        <p class="categoria-produto">
                            <?= htmlspecialchars($produto['categoria']) ?>
                        </p>

                        <p class="preco">
                            R$ <?= number_format(
                                $produto['preco'], 2, ',', '.'
                            ) ?>
                        </p>

                        <?php if ($produto['estoque'] > 0): ?>

                            <p class="estoque">Em estoque</p>

                        <?php else: ?>

                            <p class="sem-estoque">Indisponível</p>

                        <?php endif; ?>

                        <a class="botao-produto"
                           href="detalhes.php?id=<?= $produto['id'] ?>">
                            Ver detalhes
                        </a>

                    </div>

                </article>

            </div>

        <?php endforeach; ?>

    </div>

</main>

<footer class="rodape">

    <h2>L'ESSENCE</h2>

    <p>Sua essência. Sua assinatura.</p>

    <small>© 2026 L'Essence — Projeto acadêmico.</small>

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>