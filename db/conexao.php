<?php
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'estoque_farmacia';

$conexao = new mysqli($host, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    die('Erro na conexão: ' . $conn->connect_error);
}
