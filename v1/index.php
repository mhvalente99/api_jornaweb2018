<?php
    header('Content-Type: application/json; charset=utf-8');
    ini_set('display_errors', 1);
    
    require 'config.php';
    require 'funcoes.php';
    $endpoint = getEndpoint();

    $request_method = strtolower($_SERVER['REQUEST_METHOD']);
    $request_method_accept = array("get", "post", "put", "delete");

    if (!in_array($request_method, $request_method_accept, 1)){
        //die("Metodo não suportado");
        die(prepararRetorno(2000, "Metodo não suportado"));
    }

    require 'endpoints/' . $request_method . '.php';
    
    if (function_exists($endpoint)){
        $retorno = $endpoint();
    } else {
        //$retorno = json_encode(array("erro" => "Endpoint não encontrado"));
        $retorno = prepararRetorno(2001, "Endpoint não encontrado");
    }

    print_r($retorno);
    exit;