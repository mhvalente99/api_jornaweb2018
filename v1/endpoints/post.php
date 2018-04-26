<?php
function usuarios() {
    $con = getConnection();
    
	if (isset($_POST['email'])) {
	   	$email = $_POST['email'];
	} else {
		return prepararRetorno(2000, "O parâmetro email é obrigatório.");
	}

	if (isset($_POST['senha'])) {
		$senha = $_POST['senha'];	
	} else {
		return prepararRetorno(2001, "O parâmetro senha é obrigatório.");
	}

	if (isset($_POST['nome'])) {
		$nome = $_POST['nome'];
	} else {
		return prepararRetorno(2002, "O parâmetro nome é obrigatório.");
	}

	$query = $con->prepare("insert into usuarios (nome, email, senha) values (:nome, :email, :senha)");
	$query->bindValue(":nome", $nome);
	$query->bindValue(":email", $email);
	$query->bindValue(":senha", $senha);
	$usuarioCadastrado = $query->execute();

	if ($usuarioCadastrado) {
		return prepararRetorno(null, null, array('id' => $con->lastInsertId()));
	} else {
		return prepararRetorno(2003, "Erro para cadastrar o usuário.");
	}
}

function token() {
	$con = getConnection();
	if (isset($_POST['email'])) {
    	$email = $_POST['email'];
	} else {
    	return prepararRetorno(2001, "O parâmetro email é obrigatório.");
	}
	if (isset($_POST['senha'])) {
    	$senha = $_POST['senha'];
	} else {
    	return prepararRetorno(2002, "O parâmetro senha é obrigatório.");
	}
	$sql = "select u.* from usuarios u where u.email = :email and u.senha = :senha";
	$query = $con->prepare($sql);
	$query->bindValue(":email", $email);
	$query->bindValue(":senha", $senha);
    $query->execute();

	$row = $query->fetch(PDO::FETCH_ASSOC);
	if (!$row) {
    	return prepararRetorno(2003, "Usuário não encontrado.");
	}
    $token = md5(time() . rand(1000, 9999));
    
    $con->prepare("delete from usuarios_token where id_usuario = :id_usuario")->execute(array(":id_usuario" => $row['id']));
	$query = $con->prepare("insert into usuarios_token (id_usuario, token, validade) values (:id_usuario, :token, :validade)");
	$query->bindValue(":id_usuario", $row['id']);
	$query->bindValue(":token", $token);
	$query->bindValue(":validade", date("Y-m-d H:i:s", strtotime("+12 hours")));
	$tokenGravado = $query->execute();
	if ($tokenGravado) {
    	return prepararRetorno(null, null, array("token" => $token));
	} else {
    	return prepararRetorno(2004, "Erro para gerar o token.");
	}
}


function favoritos() {
	$con = getConnection();
	$token = (isset($_SERVER['HTTP_TOKEN'])) ? $_SERVER['HTTP_TOKEN'] : null;
	if (!$token) {
    		return prepararRetorno(2001, "Token não enviado");
	}
	try {
    		$id_usuario = pegarUsuarioPeloToken($token);
	} catch (Exception $ex) {
    		return prepararRetorno($ex->getCode(), $ex->getMessage());
    }
    
    if (!isset($_POST['id_filme'])) {
        return prepararRetorno(2002, "O parâmetro 'id_filme' é obrigatório.");
    } else {
        $id_filme = (int) $_POST['id_filme'];
    }

    $query = $con->prepare("insert into usuarios_filmes (id_usuario, id_filme, favorito) values (:id_usuario, :id_filme, :favorito)");
    $insert_ok = $query->execute(array(
        ":id_usuario" => $id_usuario,
        ":id_filme" => $id_filme,
        ":favorito" => 1,
    ));

    if ($insert_ok) {
        return prepararRetorno(null, null);
    } else {
        return prepararRetorno(2004, "Erro para marcar favorito.");
    }

}







