# Hoshi Calendar

Calendário pessoal em **PHP 8 e MySQL/MariaDB**, com interface inspirada em anime e constelações. Uma marcação pode ocupar um dia ou um período inteiro sem virar vários registros. O projeto foi desenvolvido para o trabalho de Análise e Projeto de Sistemas de Felipe Melo dos Santos.

## Funcionalidades

- Criar, editar, concluir, reabrir e excluir marcações.
- Classificar cada marcação como **tarefa, compromisso, prazo, lembrete, evento ou outro**.
- Definir data inicial e data final opcional. O mesmo registro aparece em todos os dias do intervalo, inclusive ao atravessar semanas e meses.
- Alternar entre as visões de **dia, semana e mês**. A semana resume os itens por dia; o mês mostra pontos coloridos e abre os detalhes do dia em painel lateral.
- Filtrar por tipo, navegar entre períodos, voltar para hoje e iniciar uma marcação com a data selecionada.
- Usar a interface em telas pequenas, com foco visível, transições e preferência por movimento reduzido.

## Executar localmente

1. Tenha PHP 8+ com a extensão `pdo_mysql`, além de MySQL ou MariaDB.
2. Crie um banco chamado `calendario` e uma tabela inicial, se ainda não existir:

```sql
CREATE TABLE compromissos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    data_compromisso DATE NOT NULL,
    hora_compromisso TIME NOT NULL,
    concluido TINYINT(1) NOT NULL DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

3. Execute **uma vez** o arquivo [migrations/001_periodos_e_tipos.sql](migrations/001_periodos_e_tipos.sql) no banco `calendario`. Ele adiciona `data_fim` e `tipo`, preservando os dados existentes.
4. Copie `config.example.php` para `config.php` e informe as credenciais locais. `config.php` é ignorado pelo Git.
5. Na raiz do projeto, execute `php -S localhost:8000` e abra `http://localhost:8000/` no navegador.

Se o projeto já estava instalado, mantenha sua tabela e faça apenas o passo 3 antes de abrir esta versão. A migração não deve ser repetida no mesmo banco.

## Estrutura

| Caminho | Finalidade |
| --- | --- |
| `index.php` | Visões de dia, semana e mês, filtros e painel de detalhes |
| `item_form.php`, `criar.php`, `editar.php` | Formulário compartilhado para cadastro e edição |
| `alterar_status.php`, `excluir.php` | Ações de status e exclusão |
| `lib/calendar.php` | Tipos, datas, consulta por sobreposição, agrupamento e proteção de formulários |
| `css/style.css`, `js/app.js` | Aparência responsiva e interações |
| `migrations/` | Evolução do banco de dados |
| `docs/` | Documento acadêmico em Word/PDF, diagramas e [notas do projeto](docs/notas-projeto.md) |
| `tools/` | Geradores reproduzíveis da documentação |

## Documentação acadêmica

O [Word](docs/documentacao-hoshi-calendar.docx) e o [PDF](docs/documentacao-hoshi-calendar.pdf) trazem descrição, requisitos funcionais e não funcionais, regras de negócio, casos de uso e diagrama de classes de projeto. O diagrama descreve a evolução planejada; o código atual usa páginas e funções PHP. A integração com um serviço externo de notificações constitui o segundo ator/sistema externo **planejado**, sem afirmar que o envio automático já funciona.

O vídeo demonstrativo será produzido pelo integrante. A evolução de código está na branch `feat/itens-periodos-categorias`, em commits semânticos. Consulte `docs/notas-projeto.md` para decisões e limites da versão.
