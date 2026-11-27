document.addEventListener("DOMContentLoaded", function () {

    // Validação das senhas no cadastro

    const senha = document.getElementById("senha");
    const confirmar = document.getElementById("confirmar");

    if (senha && confirmar) {

        function validarSenhas() {

            if (senha.value !== confirmar.value) {

                confirmar.setCustomValidity(
                    "As senhas não coincidem."
                );

            } else {

                confirmar.setCustomValidity("");

            }
        }

        senha.addEventListener("input", validarSenhas);
        confirmar.addEventListener("input", validarSenhas);
    }


    // Mostrar ou ocultar senha

    const camposSenha = document.querySelectorAll(
        'input[type="password"]'
    );

    camposSenha.forEach(function (campo) {

        const botao = document.createElement("button");

        botao.type = "button";
        botao.textContent = "Mostrar senha";
        botao.className = "mostrar-senha";

        botao.setAttribute("aria-pressed", "false");

        campo.insertAdjacentElement("afterend", botao);

        botao.addEventListener("click", function () {

            if (campo.type === "password") {

                campo.type = "text";
                botao.textContent = "Ocultar senha";
                botao.setAttribute("aria-pressed", "true");

            } else {

                campo.type = "password";
                botao.textContent = "Mostrar senha";
                botao.setAttribute("aria-pressed", "false");

            }
        });
    });


    // Validação das quantidades

    const quantidades = document.querySelectorAll(
        'input[name="quantidade"]'
    );

    quantidades.forEach(function (campo) {

        campo.addEventListener("input", function () {

            const quantidade = Number(campo.value);
            const minimo = Number(campo.min);
            const maximo = Number(campo.max);

            if (quantidade < minimo) {

                campo.setCustomValidity(
                    "A quantidade mínima é " + minimo + "."
                );

            } else if (quantidade > maximo) {

                campo.setCustomValidity(
                    "Quantidade superior ao estoque disponível."
                );

            } else {

                campo.setCustomValidity("");

            }
        });
    });


    // Confirmação de exclusão de produtos

    const botoesExcluir = document.querySelectorAll(
        ".botao-excluir"
    );

    botoesExcluir.forEach(function (botao) {

        const formulario = botao.closest("form");

        if (formulario) {

            formulario.addEventListener("submit", function (evento) {

                const confirmarExclusao = window.confirm(
                    "Deseja realmente excluir este produto?"
                );

                if (!confirmarExclusao) {

                    evento.preventDefault();

                }
            });
        }
    });


    // Evitar envio duplicado do pedido

    const formularioCheckout = document.getElementById(
        "form-checkout"
    );

    const botaoConfirmar = document.querySelector(
        ".botao-confirmar-compra"
    );

    if (formularioCheckout && botaoConfirmar) {

        formularioCheckout.addEventListener("submit", function (evento) {

            if (botaoConfirmar.disabled) {

                evento.preventDefault();
                return;

            }

            botaoConfirmar.disabled = true;
            botaoConfirmar.textContent = "PROCESSANDO PEDIDO...";

        });

        window.addEventListener("pageshow", function () {

            botaoConfirmar.disabled = false;
            botaoConfirmar.textContent = "CONFIRMAR PEDIDO";

        });
    }

});