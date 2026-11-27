<?php
session_name('LESSENCE_SESSION');
session_start();

require_once "configuracao/conexao.php";

$mensagem = "";

if (empty($_SESSION['token_conta'])) {
    $_SESSION['token_conta'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if (!hash_equals($_SESSION['token_conta'], $_POST['token'] ?? '')) {

        $mensagem = "Solicitação inválida.";

    } elseif ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $mensagem = "Preencha os dados corretamente.";

    } elseif (strlen($senha) < 6 || strlen($senha) > 72) {

        $mensagem = "A senha deve ter entre 6 e 72 bytes.";

    } elseif ($senha !== $confirmar) {

        $mensagem = "As senhas não coincidem.";

    } else {

        $consulta = $conexao->prepare(
            "SELECT id FROM usuarios WHERE email = ?"
        );

        $consulta->execute([$email]);

        if ($consulta->fetch()) {

            $mensagem = "Este e-mail já está cadastrado.";

        } else {

            $senhaProtegida = password_hash(
                $senha,
                PASSWORD_DEFAULT
            );

            $consulta = $conexao->prepare(
                "INSERT INTO usuarios
                (nome, email, senha, tipo)
                VALUES (?, ?, ?, 'cliente')"
            );

            try {

                $consulta->execute([
                    $nome,
                    $email,
                    $senhaProtegida
                ]);

                header("Location: login.php?cadastro=sucesso");
                exit;

            } catch (PDOException $erro) {

                $mensagem = "Não foi possível realizar o cadastro.";

            }
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

    <title>Cadastro | L'Essence</title>

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
        <a href="login.php">Entrar</a>
        <a href="carrinho.php">Carrinho</a>
    </nav>

</header>

<main class="container pagina-conta">

    <div class="abas-conta">

        <a href="login.php">Login</a>

        <a href="cadastro.php" class="aba-ativa">
            Cadastro
        </a>

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
                FAÇA PARTE DA L'ESSENCE
            </span>

            <h1>Crie sua conta</h1>

            <p class="descricao-conta">
                Cadastre-se para realizar suas compras.
            </p>

            <?php if ($mensagem !== ''): ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars($mensagem) ?>
                </div>

            <?php endif; ?>

            <form method="POST" class="formulario-conta">

                <input type="hidden"
                       name="token"
                       value="<?= htmlspecialchars($_SESSION['token_conta']) ?>">

                <label for="nome">Nome completo</label>

                <input type="text"
                       id="nome"
                       name="nome"
                       placeholder="Seu nome completo"
                       maxlength="100"
                       autocomplete="name"
                       required>

                <label for="email">E-mail</label>

                <input type="email"
                       id="email"
                       name="email"
                       placeholder="Seu e-mail"
                       maxlength="150"
                       autocomplete="email"
                       required>

                <label for="senha">Senha</label>

                <input type="password"
                       id="senha"
                       name="senha"
                       placeholder="Sua senha"
                       minlength="6"
                       maxlength="72"
                       autocomplete="new-password"
                       required>

                <label for="confirmar">Confirmar senha</label>

                <input type="password"
                       id="confirmar"
                       name="confirmar"
                       placeholder="Confirme sua senha"
                       minlength="6"
                       maxlength="72"
                       autocomplete="new-password"
                       required>

                <button type="submit" class="botao-conta">
                    CRIAR CONTA
                </button>

            </form>

            <p class="link-conta">
                Já possui uma conta?
                <a href="login.php">Entre aqui</a>
            </p>

        </div>

    </div>

</main>

<footer class="rodape">

    <h2>L'ESSENCE</h2>

    <p>Sua essência. Sua assinatura.</p>

    <small>© 2026 L'Essence — Projeto acadêmico.</small>

</footer>

<script src="javascript.js"></script>

</body>
</html>