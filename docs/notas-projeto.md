# Notas do projeto

## Decisões desta versão

- A tabela `compromissos` foi mantida. A migração `001_periodos_e_tipos.sql` acrescenta uma data final opcional e o tipo, com `compromisso` como valor padrão para os registros antigos.
- Uma marcação de vários dias permanece **um registro**. A consulta busca períodos que cruzam a visão aberta; o agrupamento a apresenta em cada dia abrangido.
- As seis categorias são um catálogo fechado em PHP. Valores desconhecidos são rejeitados no cadastro e na edição.
- O mês usa pontos coloridos para leitura rápida e painel lateral para detalhes. A semana resume cada dia; o dia mostra uma agenda em ordem de horário.
- Concluir e reabrir alteram o mesmo estado. Excluir pede confirmação e remove definitivamente o registro.
- Formulários que alteram dados usam token de sessão e consultas parametrizadas. Não há contas de usuário nesta versão.
- A interface Hoshi usa contrastes escuros, cores por tipo, animações discretas, foco visível e regra `prefers-reduced-motion`.
- A implementação continua em PHP e MySQL, com dependências pequenas e escopo explicável na apresentação.

## Coerência com o trabalho acadêmico

- **Atores e sistemas:** a pessoa que organiza a agenda é o ator implementado. O serviço externo de notificações aparece no projeto como extensão futura; o envio automático não faz parte da versão executável.
- **Funcionalidades distintas:** cadastro, consulta em três visões, filtros, edição, alteração de status e exclusão.
- **Classes de projeto:** o diagrama documenta `Usuario`, `Calendario`, `Marcacao`, `TipoMarcacao`, `Notificacao` e `ServicoNotificacao`, com atributos, operações, relacionamentos e multiplicidades. As classes são conceituais; a implementação inicial é procedural.
- **Histórico:** desenvolvimento na branch `feat/itens-periodos-categorias`, com commits pequenos por mudança funcional, visual e documental.

## Limites e evolução futura

- Implementar contas individuais antes de permitir agendas de vários usuários.
- Implementar o serviço externo de notificações previsto no diagrama e no RF11.
- Adicionar testes automatizados e uma exportação da agenda se o projeto continuar após a entrega inicial.
- O vídeo demonstrativo será preparado por Felipe Melo dos Santos.
