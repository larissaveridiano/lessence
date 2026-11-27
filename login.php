<?php
session_name('LESSENCE_SESSION');
session_start();

require_once "configuracao/conexao.php";

$mensagem = "";

if (empty($_SESSION['token_conta'])) {
    $_SESSION['token_conta'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (!hash_equals($_SESSION['token_conta'], $_POST['token'] ?? '')) {

        $mensagem = "Solicitação inválida.";

    } else {

        $consulta = $conexao->prepare(
            "SELECT * FROM usuarios WHERE email = ?"
        );

        $consulta->execute([$email]);

        $usuario = $consulta->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {

            session_regenerate_id(true);

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];

            header("Location: index.php");
            exit;

        } else {

            $mensagem = "E-mail ou senha incorretos.";

        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login | L'Essence</title>

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
        <a href="cadastro.php">Cadastro</a>
        <a href="carrinho.php">Carrinho</a>
    </nav>

</header>

<main class="container pagina-conta">

    <div class="abas-conta">

        <a href="login.php" class="aba-ativa">
            Login
        </a>

        <a href="cadastro.php">Cadastro</a>

    </div>

    <div class="painel-conta">

        <div class="lado-imagem-conta">

            <img src="imagens/banner.jpg"
                 alt="Perfumaria L'Essence">

            <div class="texto-imagem-conta">

                <span class="subtitulo">
                    O UNIVERSO L'ESSENCE
                </span>

                <h2>Descubra sua verdadeira essência.</h2>

                <p>
                    Fragrâncias que traduzem sua personalidade.
                </p>

            </div>

        </div>

        <div class="lado-formulario">

            <span class="subtitulo">
                BEM-VINDO À L'ESSENCE
            </span>

            <h1>Acesse sua conta</h1>

            <p class="descricao-conta">
                Entre para continuar sua experiência.
            </p>

            <?php if (($_GET['cadastro'] ?? '') === 'sucesso'): ?>

                <div class="alert alert-success">
                    Cadastro realizado com sucesso!
                </div>

            <?php endif; ?>

            <?php if ($mensagem !== ''): ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars($mensagem) ?>
                </div>

            <?php endif; ?>

            <form method="POST" class="formulario-conta">

                <input type="hidden"
                       name="token"
                       value="<?= htmlspecialchars($_SESSION['token_conta']) ?>">

                <label for="email">E-mail</label>

                <input type="email"
                       id="email"
                       name="email"
                       placeholder="Seu e-mail"
                       autocomplete="email"
                       required>

                <label for="senha">Senha</label>

                <input type="password"
                       id="senha"
                       name="senha"
                       placeholder="Sua senha"
                       autocomplete="current-password"
                       required>

                <button type="submit" class="botao-conta">
                    ENTRAR
                </button>

            </form>

            <p class="link-conta">
                Ainda não possui uma conta?
                <a href="cadastro.php">Cadastre-se</a>
            </p>

        </div>

    </div>

</main>

<footer class="rodape">

    <h2>L'ESSENCE</h2>

    <p>Sua essência. Sua assinatura.</p>

    <small>© 2026 L'Essence — Projeto acadêmico.</small>

</footer>

</body>
</html>