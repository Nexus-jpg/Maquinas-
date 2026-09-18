
CREATE DATABASE IF NOT EXISTS banco_dados;
USE banco_dados;

CREATE TABLE IF NOT EXISTS usuarios (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    usuario      VARCHAR(50) NOT NULL UNIQUE,
    senha        VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('cliente', 'dev', 'gerente_dev', 'socio') NOT NULL DEFAULT 'cliente',
    criado_em    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS Clientes (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id     INT UNIQUE NOT NULL,
    nome_completo  VARCHAR(100) NOT NULL,
    cpf_cnpj       VARCHAR(20) NOT NULL UNIQUE,
    telefone       VARCHAR(20) NOT NULL,
    CONSTRAINT fk_clientes_usuarios
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome         VARCHAR(50) NOT NULL UNIQUE,
    descricao    VARCHAR(255)
);


CREATE TABLE IF NOT EXISTS maquinarios (
    id_maquinario INT AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(100) NOT NULL,
    descricao     TEXT,
    categoria     VARCHAR(50),
    valor_diaria  DECIMAL(10,2) NOT NULL,
    status        ENUM('disponivel', 'alugado', 'manutencao') DEFAULT 'disponivel',
    imagem        VARCHAR(255)
);


CREATE TABLE IF NOT EXISTS pedidos (
    Id_pedidos    INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id    INT NOT NULL,
    maquinario_id INT NOT NULL,
    valor         DECIMAL(10,2) NOT NULL,
    data_inicio   DATE NOT NULL,
    data_fim      DATE NOT NULL,
    status        ENUM('pendente', 'confirmado', 'em_andamento', 'entregue', 'concluido', 'cancelado')
                      NOT NULL DEFAULT 'pendente',
    data_pedido   DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pedidos_usuarios
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_pedidos_maquinarios
        FOREIGN KEY (maquinario_id) REFERENCES maquinarios(id_maquinario)
        ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS avaliacoes (
    id_avaliacao INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id    INT NOT NULL UNIQUE,
    usuario_id   INT NOT NULL,
    nota         DECIMAL(2,1) NOT NULL CHECK (nota >= 0 AND nota <= 5),
    comentario   TEXT,
    criado_em    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_avaliacoes_pedidos
        FOREIGN KEY (pedido_id) REFERENCES pedidos(Id_pedidos)
        ON DELETE CASCADE,
    CONSTRAINT fk_avaliacoes_usuarios
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS contratos_socios (
    id_contrato      INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id       INT NOT NULL,
    maquinario_id    INT NOT NULL,
    tipo_contrato    ENUM('financiamento', 'desconto_preferencial') NOT NULL,
    status           ENUM('pendente', 'aprovado', 'recusado') DEFAULT 'pendente',
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_aprovacao   TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_contratos_socios_usuarios
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_contratos_socios_maquinarios
        FOREIGN KEY (maquinario_id) REFERENCES maquinarios(id_maquinario)
        ON DELETE CASCADE,
    UNIQUE KEY uq_socio_produto (usuario_id, maquinario_id)
);


CREATE TABLE IF NOT EXISTS chamados_suporte (
    id_chamado    INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id    INT NOT NULL,
    nome          VARCHAR(100) NOT NULL,
    sobrenome     VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL,
    mensagem      TEXT NOT NULL,
    status        ENUM('aberto', 'em_atendimento', 'respondido', 'fechado') DEFAULT 'aberto',
    email_enviado TINYINT(1) DEFAULT 0,
    criado_em     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_chamados_usuarios
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS funcionarios (
    id_funcionario   INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id       INT NOT NULL,
    nome_funcionario VARCHAR(100) NOT NULL,
    genero           CHAR(1) NOT NULL CHECK (genero IN ('M', 'F')),
    nacionalidade    VARCHAR(100) NOT NULL,
    data_nascimento  DATE NOT NULL,
    cargo            VARCHAR(67) NOT NULL,
    salario          DECIMAL(10,2) NOT NULL,
    tempo_empresa    DATE NOT NULL,
    CONSTRAINT fk_funcionarios_usuarios
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS pagamentos (
    id_pagamento    INT AUTO_INCREMENT PRIMARY KEY,
    funcionario_id  INT NOT NULL,
    mes_referencia  DATE NOT NULL COMMENT 'Use o dia 01 do mês, ex: 2026-09-01 para setembro/2026',
    salario_base    DECIMAL(10,2) NOT NULL,
    bonus           DECIMAL(10,2) NOT NULL DEFAULT 0,
    descontos       DECIMAL(10,2) NOT NULL DEFAULT 0,
    valor_liquido   DECIMAL(10,2) NOT NULL,
    status          ENUM('pendente', 'pago') DEFAULT 'pendente',
    data_pagamento  DATE,
    comprovante     VARCHAR(255) COMMENT 'Caminho do extrato/holerite em PDF, se tiver',
    CONSTRAINT fk_pagamentos_funcionarios
        FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id_funcionario)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS pagamentos_pedidos (
    id_pagamento    INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id       INT NOT NULL,
    valor_pago      DECIMAL(10,2) NOT NULL,
    forma_pagamento ENUM('pix', 'boleto', 'cartao_credito', 'cartao_debito', 'transferencia') NOT NULL,
    status          ENUM('pendente', 'pago', 'estornado') DEFAULT 'pendente',
    data_pagamento  DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pagamentos_pedidos_pedidos
        FOREIGN KEY (pedido_id) REFERENCES pedidos(Id_pedidos)
        ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS manutencoes (
    id_manutencao INT AUTO_INCREMENT PRIMARY KEY,
    maquinario_id INT NOT NULL,
    tipo          ENUM('preventiva', 'corretiva') NOT NULL,
    descricao     TEXT,
    data_inicio   DATE NOT NULL,
    data_fim      DATE,
    custo         DECIMAL(10,2),
    CONSTRAINT fk_manutencoes_maquinarios
        FOREIGN KEY (maquinario_id) REFERENCES maquinarios(id_maquinario)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS enderecos_entrega (
    id_endereco  INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id    INT NOT NULL,
    cep          VARCHAR(9) NOT NULL,
    logradouro   VARCHAR(150) NOT NULL,
    numero       VARCHAR(10),
    complemento  VARCHAR(100),
    bairro       VARCHAR(100),
    cidade       VARCHAR(100) NOT NULL,
    estado       CHAR(2) NOT NULL,
    CONSTRAINT fk_enderecos_pedidos
        FOREIGN KEY (pedido_id) REFERENCES pedidos(Id_pedidos)
        ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS contratos (
    id_contrato     INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id       INT NOT NULL UNIQUE,
    data_assinatura DATETIME NULL,
    assinado        TINYINT(1) DEFAULT 0,
    arquivo_pdf     VARCHAR(255),
    observacoes     TEXT,
    CONSTRAINT fk_contratos_pedidos
        FOREIGN KEY (pedido_id) REFERENCES pedidos(Id_pedidos)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS devolucoes (
    id_devolucao     INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id        INT NOT NULL,
    data_devolucao   DATETIME DEFAULT CURRENT_TIMESTAMP,
    condicao_maquina ENUM('boa', 'avariada', 'com_defeito') DEFAULT 'boa',
    observacoes      TEXT,
    CONSTRAINT fk_devolucoes_pedidos
        FOREIGN KEY (pedido_id) REFERENCES pedidos(Id_pedidos)
        ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS maquinarios_inutilizados (
    id_inutilizado      INT AUTO_INCREMENT PRIMARY KEY,
    maquinario_id       INT NOT NULL,
    motivo              VARCHAR(255) NOT NULL,
    data_inicio         DATE NOT NULL,
    data_previsao_volta DATE,
    status              ENUM('em_manutencao', 'perda_total', 'vendido_sucata') DEFAULT 'em_manutencao',
    CONSTRAINT fk_inutilizados_maquinarios
        FOREIGN KEY (maquinario_id) REFERENCES maquinarios(id_maquinario)
        ON DELETE CASCADE
);
