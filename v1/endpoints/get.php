<?php 
    function generos(){
        $con = getConnection();

        $sql = "SELECT * FROM generos";

        //Busca pelo id ou nome
        $where = array();
        if (isset($_GET['id'])){
            $id = $con->quote($_GET['id'], PDO::PARAM_INT);
            $where[] = "(id = {$id})";
        }

        if (isset($_GET['nome'])) {
            $nome = $con->quote($_GET['nome'], PDO::PARAM_STR);
            $nomeLike = $con->quote("%{$_GET['nome']}%", PDO::PARAM_STR);
            $where[] = "((nome = {$nome}) or (nome like {$nomeLike}))";
        }

        if ($where) {
            $sql .= " where " . join(" and ", $where);
        }
        
        $query = $con->query($sql);
    
        $generos =  $query->fetchAll(PDO::FETCH_ASSOC);
        //return json_encode($generos);

        return prepararRetorno(null, null, $generos);
    }


    function filmes() {
        $con = getConnection();
        $sql = "select f.*, g.nome as nome_genero, round(avg(nota)) as nota, sum(if(nota is not null, 1, 0)) qtde_avaliacoes, sum(if(comentario is not null, 1, 0)) qtde_comentarios from filmes as f ";
        $sql .= " left join filmes_imagens fi on (fi.id_filme = f.id) ";
        $sql .= " left join generos g on (g.id = f.id_genero) ";
        $sql .= " left join usuarios_filmes uf on (uf.id_filme = f.id) ";

        $where = array();
        if (isset($_GET['id'])) {
            $id = (int) $_GET['id'];
            $where[] = " (f.id = " . $con->quote($id, PDO::PARAM_INT) . ") ";
        }

        if (isset($_GET['titulo'])) {
            $titulo = trim($_GET['titulo']);
            $where[] = " ((f.titulo = " . $con->quote($titulo, PDO::PARAM_STR) . ") or (f.titulo like " . $con->quote("%{$titulo}%", PDO::PARAM_STR) . ")) ";
        }

        if (isset($_GET['id_genero'])) {
            $id_genero = (int) $_GET['id_genero'];
            $where[] = " (g.id = " . $con->quote($id_genero, PDO::PARAM_INT) . ") ";
        }

        if (isset($_GET['nome_genero'])) {
            $nome_genero = trim($_GET['nome_genero']);
            $where[] = " ((g.nome = " . $con->quote($nome_genero, PDO::PARAM_STR) . ") or (g.nome like " . $con->quote("%{$nome_genero}%", PDO::PARAM_STR) . ")) ";
        }

        if ($where) {
            $sql .= " where" . join(" and ", $where);
        }

        $sql .= " group by f.id ";
        $sql .= " order by f.id , fi.ordem";

        $query = $con->query($sql);

        $retorno = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $filme = array(
                "id" => (int) $row['id'],
                "titulo" => $row['titulo'],
                "lancamento" => $row['lancamento'],
                "sinopse" => $row['sinopse'],
                "id_genero" => (int) $row['id_genero'],
                "nome_genero" => $row['nome_genero'],
                "nota" => ($row['nota']) ? (int) $row['nota'] : null,
                "qtde_avaliacoes" => (int) $row['qtde_avaliacoes'],
                "qtde_comentarios" => (int) $row['qtde_comentarios'],
                "filmes" => array(),
            );

            $sql_imagens = "select url from filmes_imagens where id_filme = " . $row['id'] . " order by ordem ASC";
    	    $imagens = $con->query($sql_imagens)->fetchAll(PDO::FETCH_COLUMN);
            
            if ($imagens) {
        	    $filme["imagens"] = $imagens;
    	    }

            $retorno[] = $filme;
        }
        return prepararRetorno(null, null, $retorno);
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
    
        //return prepararRetorno(null, null, array("id_usuario" => $id_usuario));
    
        $sql = "select f.*, uf.* from filmes as f";
        $sql .= " inner join usuarios_filmes as uf on (uf.id_filme = f.id) ";
        
        $where = array(
            "uf.favorito = 1",
            "(id_usuario = " . $con->quote($id_usuario, PDO::PARAM_INT) . ")"
        );
    
        $sql .= " where " . join(' and ', $where);
        $query = $con->query($sql);
    
        $retorno = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $retorno[] = array(
                    "id" => $row['id'],
                    "titulo" => $row['titulo'],
                    "lancamento" => $row['lancamento'],
                    "sinopse" => $row['sinopse'],
                    "favorito" => (bool) $row['favorito'],
                    "assistido" => (bool) $row['assistido'],
                    "nota" => (int) $row['nota'],
                    "comentario" => $row['comentario'],
            );
        }
        return prepararRetorno(null, null, $retorno);
    
    }