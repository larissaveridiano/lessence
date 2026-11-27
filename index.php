<?php
session_name('LESSENCE_SESSION');
session_start();

require_once "configuracao/conexao.php";

$pesquisa = trim($_GET['pesquisa'] ?? '');
$grupo = $_GET['grupo'] ?? '';
$categoria = (int) ($_GET['categoria'] ?? 0);

$sql = "SELECT produtos.*, categorias.nome AS categoria
        FROM produtos
        INNER JOIN categorias
        ON produtos.categoria_id = categorias.id
        WHERE 1=1";

$parametros = [];

if ($pesquisa !== '') {
    $sql .= " AND (produtos.nome LIKE ?
              OR produtos.marca LIKE ?)";

    $parametros[] = "%$pesquisa%";
    $parametros[] = "%$pesquisa%";
}

if ($grupo === 'femininos') {
    $sql .= " AND produtos.categoria_id IN (1, 3)";
}

if ($grupo === 'masculinos') {
    $sql .= " AND produtos.categoria_id IN (2, 4)";
}

if ($grupo === 'arabes') {
    $sql .= " AND produtos.categoria_id = 5";
}

if ($categoria > 0) {
    $sql .= " AND produtos.categoria_id = ?";
    $parametros[] = $categoria;
}

$sql .= " ORDER BY produtos.id";

$consulta = $conexao->prepare($sql);
$consulta->execute($parametros);

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

<form method="GET"
      action="index.php#catalogo"
      class="filtros">

    <input type="search"
           name="pesquisa"
           placeholder="Busque seu perfume ou marca..."
           value="<?= htmlspecialchars($pesquisa) ?>">

    <select name="categoria">

        <option value="0">Todas as categorias</option>

        <?php foreach ($categorias as $item): ?>

            <option value="<?= $item['id'] ?>"
                <?= $categoria == $item['id'] ? 'selected' : '' ?>>

                <?= htmlspecialchars($item['nome']) ?>

            </option>

        <?php endforeach; ?>

    </select>

    <button type="submit" class="botao-pesquisa">
        PESQUISAR
    </button>

    <a href="index.php#catalogo" class="limpar-filtros">
        Limpar
    </a>

</form>

<p class="resultado-pesquisa">
    <?= count($produtos) ?> fragrância(s) encontrada(s)
</p>

<?php if (empty($produtos)): ?>

    <div class="nenhum-produto">
        Nenhuma fragrância encontrada.
        Tente pesquisar outro perfume.
    </div>

<?php endif; ?>

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

<script src="javascript.js"></script>

</body>
</html>