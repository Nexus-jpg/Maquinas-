CREATE DATABASE IF NOT EXISTS banco_dados;
USE banco_dados;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS Clientes (
id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNIQUE NOT NULL, 
    nome_completo VARCHAR(100) NOT NULL,
    cpf_cnpj VARCHAR(20) NOT NULL UNIQUE,
    telefone VARCHAR(20) NOT NULL,
    CONSTRAINT fk_clientes_usuarios 
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) 
        ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS pedidos (
    Id_pedidos INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    maquinario_id INT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_inicio DATE NOT NULL, 
    data_fim DATE NOT NULL,
    data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pedidos_usuarios 
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_pedidos_maquinarios 
        FOREIGN KEY (maquinario_id) REFERENCES maquinarios(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS maquinarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    categoria VARCHAR(50),
    valor_diaria DECIMAL(10,2) NOT NULL,
    status ENUM('disponivel', 'alugado', 'manutencao') DEFAULT 'disponivel',
    imagem VARCHAR(255)
);




CREATE TABLE IF NOT EXISTS funcionários (
id_funcionario PRIMARY KEY INT AUTO_INCREMENT,
nome_funcionario VARCHAR(100) NOT NULL,
genero CHAR(1) NOT NULL CHECK (genero IN ('M', 'F'))
nacionalidade VARCHAR(100) NOT NULL,
dataNasce DATETIME DEFAULT CURRENT_TIMESTAMP,
cargo VARCHAR(67) NOT NULL,
salario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
tempoEmpresa DATETIME DEFAULT CURRENT_TIMESTAMP

);

CREATE TABLE IF NOT EXISTS pagamentos (
extrato
salario01 DECIMAL (10,2) NOT NULL,
);
CREATE TABLE IF NOT EXISTS categorias (

);
CREATE TABLE IF NOT EXISTS manutencoes (

);









CREATE TABLE IF NOT EXISTS enderecos_entrega (

);
CREATE TABLE IF NOT EXISTS contratos (

);


CREATE TABLE IF NOT EXISTS devolucoes (

);



CREATE TABLE IF NOT EXISTS maquinarios_inutilizados (
motivo VARCHAR(255) NOT NULL, 
data_inicio DATE NOT NULL,    
data_previsao_volta DATE,     
status ENUM('em_manutencao', 'per)
);



    




