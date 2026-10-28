# Notas do projeto

## Decisões da evolução

- O banco existente foi preservado: a evolução usa a tabela `compromissos` e não exige migração para funcionar.
- A visão mensal passou a privilegiar leitura rápida: cada célula mostra somente o número do dia e indicadores de status. O detalhe abre em painel lateral.
- Semana e dia compartilham o mesmo intervalo de consulta e agrupamento por data, mantendo a regra de ordenação por horário.
- A criação rápida reaproveita `criar.php` com a data selecionada na URL, reduzindo duplicação de lógica.
- O tema Hoshi usa contraste escuro, acentos rosa/lilás/mint, tipografia geométrica e movimentos discretos para comunicar o conceito de constelação sem prejudicar acessibilidade.
- O sistema continua PHP + MySQL, adequado ao escopo da disciplina e explicável em uma apresentação.

## Requisitos acadêmicos atendidos

- Mais de um ator: usuário principal e serviço externo de notificações/lembretes planejado.
- Múltiplas funcionalidades: CRUD, conclusão, navegação, três visões e criação rápida.
- Classes/conceitos para projeto: Usuário, Compromisso, Calendário/Visão e Serviço de Notificações.
- Histórico: mudanças organizadas em commits `feat`, `docs` e `fix`.

## Próximas melhorias possíveis

- Autenticação simples para separar usuários.
- Preferências de lembrete e integração real com um serviço externo.
- Filtros por status e exportação de agenda.
- Testes automatizados das validações e consultas.
