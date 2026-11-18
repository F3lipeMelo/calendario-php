-- Execute uma vez em bancos criados antes desta versão.
ALTER TABLE compromissos
    ADD COLUMN data_fim DATE NULL AFTER data_compromisso,
    ADD COLUMN tipo VARCHAR(20) NOT NULL DEFAULT 'compromisso' AFTER hora_compromisso,
    ADD INDEX idx_compromissos_periodo (data_compromisso, data_fim);
