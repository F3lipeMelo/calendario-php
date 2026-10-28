# Hoshi Calendar

Calendário pessoal responsivo desenvolvido em PHP e MySQL para organizar compromissos com uma interface inspirada em anime e constelações. A implementação mantém o escopo acadêmico do projeto original e amplia a experiência de uso sem trocar a stack.

## Funcionalidades

- Cadastro, edição, exclusão e conclusão de compromissos.
- Visão de mês com indicadores por bolinhas para cada dia.
- Painel lateral com os compromissos do dia selecionado.
- Visão de semana com resumo por dia e atalho de criação.
- Visão de dia com agenda cronológica.
- Navegação por períodos, botão Hoje e criação rápida já preenchida.
- Layout responsivo, estados vazios, microinterações e feedback visual.

## Execução local

1. Instale PHP 8+ e MySQL/MariaDB.
2. Crie um banco chamado `calendario`.
3. Crie a tabela `compromissos` com as colunas usadas pelo projeto:

```sql
CREATE TABLE compromissos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    data_compromisso DATE NOT NULL,
    hora_compromisso TIME NOT NULL,
    concluido TINYINT(1) NOT NULL DEFAULT 0
);
```

4. Copie `config.example.php` para `config.php` e informe as credenciais locais.
5. Execute `php -S localhost:8000` na raiz do projeto e acesse `http://localhost:8000`.

## Estrutura

- `index.php`: consultas e três modos de visualização.
- `criar.php`, `editar.php`: formulários de compromissos.
- `alterar_status.php`, `excluir.php`: ações de manutenção.
- `css/style.css`: tema visual, responsividade e estados de interação.
- `js/app.js`: pequenos feedbacks de interação.
- `docs/notas-projeto.md`: decisões, escopo acadêmico e próximos passos.

## Escopo acadêmico

O ator principal é o usuário que organiza sua agenda. Como extensão simples e justificável, o sistema prevê integração futura com um serviço externo de notificações/lembretes, que funciona como segundo ator/sistema externo. As funcionalidades implementadas já cobrem manutenção de compromissos, acompanhamento de status e consulta em diferentes perspectivas; o histórico Git registra a evolução em commits semânticos.
