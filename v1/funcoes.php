<?php
    function getEndpoint(){
        $endpoint = null;
        $result = array();
        //Retira as barras da variavel
        $request_uri = trim($_SERVER['REQUEST_URI'], "/");
    
        //Pega o que vem depois de api v1 desde que nao seja caracteres especiais (/ ?)
        if (preg_match("#^api\/v1\/([^\/\?]+)#", $request_uri, $result)) {
            $endpoint = $result[1];
        }

        return $endpoint;
    }

    function getConnection() {
	    $db = DB_DATABASE;
	    $host = DB_HOST;
	    $charset = DB_CHARSET;
	    $user = DB_USER;
	    $password = DB_PASSWORD;
        return new PDO("mysql:dbname={$db};host={$host};charset={$charset}", $user, $password);
    }

    function prepararRetorno($cd_erro, $msg_erro, $dados = array()) {
        $retorno = array(
            "erro" => array(
                "cd" => $cd_erro,
                "msg" => $msg_erro,
            ),
            "dados" => $dados,
        );
        
        return json_encode($retorno);
    }

    function pegarUsuarioPeloToken($token) {
	    $con = getConnection();
	    $sql = "select * from usuarios_token where token = :token";
	    $query = $con->prepare($sql);
	    $query->bindValue(":token", $token);
        $query->execute();
        
	    $row = $query->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
    	    throw new Exception("Token não encontrado.", 1000);
	    } else {
    	    $validade = strtotime($row['validade']);
    	        if ($validade < time()) {
        	        throw new Exception("Token expirado.", 1001);
    	        }
	    }   
	return $row['id_usuario'];
}



