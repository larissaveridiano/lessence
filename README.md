# L'Essence — Perfumaria

## Sobre o projeto

A L'Essence é um e-commerce de perfumes desenvolvido como projeto acadêmico do 4º bimestre do Ensino Médio Técnico em Tecnologia da Informação do Senac Nações Unidas.

O objetivo do projeto é aplicar os conhecimentos adquiridos durante as aulas de desenvolvimento web, banco de dados, programação e versionamento, criando uma loja virtual funcional, responsiva e com uma identidade visual padronizada.

## Funcionalidades

### Área do cliente

- Catálogo com 30 perfumes nacionais, internacionais e árabes.
- Pesquisa por nome e marca.
- Filtro por categorias.
- Visualização dos detalhes dos produtos.
- Cadastro e autenticação de usuários.
- Carrinho de compras com controle de quantidade e estoque.
- Finalização de pedidos.
- Pagamentos simulados por PIX, boleto e cartão.

### Área administrativa

- Painel com indicadores de produtos, estoque e vendas.
- Cadastro, edição, listagem e exclusão de produtos.
- Controle de estoque.
- Consulta de pedidos realizados.
- Visualização dos detalhes das compras.
- Controle de acesso administrativo.

## Tecnologias utilizadas

- HTML5
- CSS3
- JavaScript
- Bootstrap 5
- PHP
- MySQL
- PDO
- XAMPP
- Git e GitHub
- Visual Studio Code

## Banco de dados

O banco de dados `lessence` possui as seguintes tabelas:

- usuarios
- categorias
- produtos
- pedidos
- itens_pedido

O arquivo `database/schema.sql` contém a estrutura do banco de dados e o catálogo inicial de produtos.

## Como executar o projeto

1. Instale o XAMPP.
2. Baixe ou clone este repositório.
3. Coloque a pasta `lessence` dentro de `C:\xampp\htdocs`.
4. Inicie o Apache e o MySQL pelo painel do XAMPP.
5. Acesse `http://localhost/phpmyadmin`.
6. Importe o arquivo `database/schema.sql`.
7. Crie uma cópia do arquivo `.env.example` com o nome `.env`.
8. Configure as informações de conexão com o banco de dados no arquivo `.env`.
9. Acesse `http://localhost/lessence/` no navegador.

## Como acessar a administração

1. Realize um cadastro pelo site.
2. Acesse o banco de dados `lessence` pelo phpMyAdmin.
3. Abra a tabela `usuarios`.
4. Localize o usuário cadastrado.
5. Altere o campo `tipo` de `cliente` para `admin`.
6. Saia da conta e realize o login novamente.
7. Acesse a opção Administração no menu.

## Segurança

O projeto utiliza consultas preparadas com PDO, proteção de senhas com `password_hash`, autenticação com sessões PHP, tokens de proteção de formulários e controle de acesso às páginas administrativas.

O arquivo `.env` é protegido pelo `.gitignore` para evitar o compartilhamento das credenciais de conexão.

As compras utilizam transações no banco de dados para garantir a consistência dos pedidos e do estoque.

## Identidade visual

A identidade visual da L'Essence foi desenvolvida utilizando tons de marrom, champagne, areia, marfim e branco, buscando transmitir elegância, sofisticação e harmonia.

## Versionamento

O desenvolvimento foi realizado em etapas, utilizando Git e GitHub para registrar a evolução do projeto por meio de commits.

## Observação

Este projeto possui finalidade exclusivamente acadêmica. Os pagamentos são simulados e nenhuma transação financeira real é realizada.

## Desenvolvedora

Larissa Veridiano

3º ano — Ensino Médio Técnico em Tecnologia da Informação

Senac Nações Unidas — 2026

**L'Essence — Sua essência. Sua assinatura.**