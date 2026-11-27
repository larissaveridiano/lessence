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

if (empty($_SESSION['token_admin'])) {
    $_SESSION['token_admin'] = bin2hex(random_bytes(32));
}

$mensagem = $_SESSION['mensagem_admin'] ?? '';

unset($_SESSION['mensagem_admin']);

$editar = null;

/* Cadastrar, editar ou excluir */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = (int) ($_POST['id'] ?? 0);

    if (!hash_equals(
        $_SESSION['token_admin'],
        $_POST['token'] ?? ''
    )) {

        $mensagem = "Solicitação inválida.";

    } elseif (isset($_POST['excluir'])) {

        try {

            $consulta = $conexao->prepare(
                "DELETE FROM produtos WHERE id = ?"
            );

            $consulta->execute([$id]);

            $mensagem = "Produto excluído com sucesso.";

        } catch (PDOException $erro) {

            $mensagem =
                "Não é possível excluir um produto vinculado a pedidos.";

        }

    } elseif (isset($_POST['salvar'])) {

        $nome = trim($_POST['nome'] ?? '');
        $marca = trim($_POST['marca'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $categoria = (int) ($_POST['categoria_id'] ?? 0);
        $preco = $_POST['preco'] ?? '';
        $estoque = filter_var(
            $_POST['estoque'] ?? '',
            FILTER_VALIDATE_INT
        );
        $imagem = trim($_POST['imagem'] ?? '');

        if (
            $nome === '' ||
            $marca === '' ||
            $descricao === '' ||
            $categoria < 1 ||
            !is_numeric($preco) ||
            $preco <= 0 ||
            $preco > 99999999.99 ||
            $estoque === false ||
            $estoque < 0 ||
            $imagem === ''
        ) {

            $mensagem = "Preencha os campos corretamente.";

        } elseif (
            !preg_match(
                '/^[a-zA-Z0-9_-]+\.(jpg|jpeg|png|webp)$/i',
                $imagem
            ) ||
            !is_file(__DIR__ . '/../imagens/' . $imagem)
        ) {

            $mensagem =
                "Informe uma imagem existente na pasta imagens.";

        } else {

            try {

                if ($id > 0) {

                    $consulta = $conexao->prepare(
                        "UPDATE produtos SET
                         nome = ?,
                         marca = ?,
                         descricao = ?,
                         categoria_id = ?,
                         preco = ?,
                         estoque = ?,
                         imagem = ?
                         WHERE id = ?"
                    );

                    $consulta->execute([
                        $nome,
                        $marca,
                        $descricao,
                        $categoria,
                        $preco,
                        $estoque,
                        $imagem,
                        $id
                    ]);

                    $mensagem =
                        "Produto atualizado com sucesso.";

                } else {

                    $consulta = $conexao->prepare(
                        "INSERT INTO produtos
                         (nome, marca, descricao,
                          categoria_id, preco, estoque, imagem)
                         VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );

                    $consulta->execute([
                        $nome,
                        $marca,
                        $descricao,
                        $categoria,
                        $preco,
                        $estoque,
                        $imagem
                    ]);

                    $mensagem =
                        "Produto cadastrado com sucesso.";

                }

            } catch (PDOException $erro) {

                $mensagem =
                    "Não foi possível salvar o produto.";

            }
        }
    }

    $_SESSION['mensagem_admin'] = $mensagem;

    header("Location: produtos.php");
    exit;
}

/* Carregar produto para edição */

if (isset($_GET['editar'])) {

    $consulta = $conexao->prepare(
        "SELECT * FROM produtos WHERE id = ?"
    );

    $consulta->execute([
        (int) $_GET['editar']
    ]);

    $editar = $consulta->fetch();
}

/* Listar categorias */

$categorias = $conexao->query(
    "SELECT * FROM categorias ORDER BY id"
)->fetchAll();

/* Listar produtos */

$produtos = $conexao->query(
    "SELECT produtos.*, categorias.nome AS categoria
     FROM produtos
     INNER JOIN categorias
     ON produtos.categoria_id = categorias.id
     ORDER BY produtos.id"
)->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Produtos | L'Essence Administração</title>

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
            GESTÃO DO CATÁLOGO
        </span>

        <h1>Gerenciar produtos</h1>

        <p>
            Administre as fragrâncias da L'Essence.
        </p>

    </div>

    <?php if ($mensagem !== ''): ?>

        <div class="mensagem-carrinho">
            <?= htmlspecialchars($mensagem) ?>
        </div>

    <?php endif; ?>

    <section class="painel-admin" id="formulario">

        <h2>

            <?= $editar
                ? 'Editar perfume'
                : 'Cadastrar perfume' ?>

        </h2>

        <form method="POST" class="formulario-admin">

            <input type="hidden"
                   name="token"
                   value="<?= htmlspecialchars($_SESSION['token_admin']) ?>">

            <input type="hidden"
                   name="id"
                   value="<?= $editar['id'] ?? 0 ?>">

            <div class="row g-3">

                <div class="col-md-6">

                    <label>Nome do perfume</label>

                    <input type="text"
                           name="nome"
                           maxlength="150"
                           value="<?= htmlspecialchars($editar['nome'] ?? '') ?>"
                           required>

                </div>

                <div class="col-md-6">

                    <label>Marca</label>

                    <input type="text"
                           name="marca"
                           maxlength="100"
                           value="<?= htmlspecialchars($editar['marca'] ?? '') ?>"
                           required>

                </div>

                <div class="col-md-4">

                    <label>Categoria</label>

                    <select name="categoria_id" required>

                        <option value="">
                            Selecione
                        </option>

                        <?php foreach ($categorias as $categoria): ?>

                            <option value="<?= $categoria['id'] ?>"
                                <?= ($editar['categoria_id'] ?? 0) == $categoria['id']
                                    ? 'selected'
                                    : '' ?>>

                                <?= htmlspecialchars($categoria['nome']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="col-md-4">

                    <label>Preço (R$)</label>

                    <input type="number"
                           name="preco"
                           step="0.01"
                           min="0.01"
                           value="<?= htmlspecialchars($editar['preco'] ?? '') ?>"
                           required>

                </div>

                <div class="col-md-4">

                    <label>Estoque</label>

                    <input type="number"
                           name="estoque"
                           min="0"
                           value="<?= htmlspecialchars($editar['estoque'] ?? '') ?>"
                           required>

                </div>

                <div class="col-12">

                    <label>Nome do arquivo da imagem</label>

                    <input type="text"
                           name="imagem"
                           placeholder="Exemplo: lily.jpg"
                           value="<?= htmlspecialchars($editar['imagem'] ?? '') ?>"
                           required>

                </div>

                <div class="col-12">

                    <label>Descrição</label>

                    <textarea name="descricao"
                              rows="4"
                              required><?= htmlspecialchars($editar['descricao'] ?? '') ?></textarea>

                </div>

                <div class="col-12">

                    <button type="submit"
                            name="salvar"
                            class="botao-admin">

                        <?= $editar
                            ? 'SALVAR ALTERAÇÕES'
                            : 'CADASTRAR PRODUTO' ?>

                    </button>

                    <?php if ($editar): ?>

                        <a href="produtos.php"
                           class="cancelar-edicao">

                            Cancelar edição

                        </a>

                    <?php endif; ?>

                </div>

            </div>

        </form>

    </section>

    <section class="painel-admin">

        <h2>Perfumes cadastrados</h2>

        <p class="descricao-conta">
            <?= count($produtos) ?> produtos no catálogo.
        </p>

        <div class="table-responsive">

            <table class="tabela-admin">

                <thead>

                    <tr>
                        <th>Imagem</th>
                        <th>Perfume</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th>Estoque</th>
                        <th>Ações</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($produtos as $produto): ?>

                        <tr>

                            <td>

                                <img
                                    src="../imagens/<?= htmlspecialchars($produto['imagem']) ?>"
                                    alt="<?= htmlspecialchars($produto['nome']) ?>">

                            </td>

                            <td>

                                <strong>

                                    <?= htmlspecialchars($produto['nome']) ?>

                                </strong>

                                <small>

                                    <?= htmlspecialchars($produto['marca']) ?>

                                </small>

                            </td>

                            <td>

                                <?= htmlspecialchars($produto['categoria']) ?>

                            </td>

                            <td>

                                R$ <?= number_format(
                                    $produto['preco'],
                                    2, ',', '.'
                                ) ?>

                            </td>

                            <td>

                                <?= $produto['estoque'] ?>

                            </td>

                            <td>

                                <div class="acoes-tabela">

                                    <a class="botao-editar"
                                       href="produtos.php?editar=<?= $produto['id'] ?>#formulario">

                                        Editar

                                    </a>

                                    <form method="POST"
                                          onsubmit="return confirm('Deseja excluir este produto?');">

                                        <input type="hidden"
                                               name="id"
                                               value="<?= $produto['id'] ?>">

                                        <input type="hidden"
                                               name="token"
                                               value="<?= htmlspecialchars($_SESSION['token_admin']) ?>">

                                        <button type="submit"
                                                name="excluir"
                                                class="botao-excluir">

                                            Excluir

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

<footer class="rodape">

    <h2>L'ESSENCE</h2>

    <p>Sua essência. Sua assinatura.</p>

    <small>© 2026 L'Essence — Administração.</small>

</footer>

</body>
</html>