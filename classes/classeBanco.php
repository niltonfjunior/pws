<?php

abstract class Banco
{
   function __construct()
   {
        @mysql_connect("localhost","root");
        @mysql_select_db("projetows") or die($this->erroSql("Não foi possível acessar o banco de dados."));
   }


   protected function erroSql($msg)
   {
		echo $msg;
        echo " (".mysql_errno().")";
   }

   protected function consultaSql($query)
   {
        $retorno = mysql_query($query) or die($this->erroSql('Erro ao consultar a base de dados.'));
        return $retorno;
   }

   protected function searchData($q)
   {
        switch($q)
        {
            case 'autor':    $query = "SELECT idAutor, preNomeAutor, meioNomeAutor, sobreNomeAutor FROM autor ORDER BY sobreNomeAutor"; break;
            case 'keywords': $query = "SELECT idPalavra, textoPalavra, definicaoPalavra, idRdfPalavra FROM palavra ORDER BY textoPalavra"; break;
            case 'tipo':     $query = "SELECT idTipo, nomeTipo, definicaoTipo FROM tipo ORDER BY nomeTipo"; break;
        }
        $retorno = mysql_query($query) or die($this->erroSql('Erro ao consultar a base de dados.'));
        return $retorno;
   }

   protected function quantos($tabela)
   {
        $quantos = mysql_num_rows(mysql_query("SELECT * FROM $tabela"));
        return $quantos;
   }
   
   protected function skosExpSparql($query)
   {                
        // ENCONTRAR AS PROPRIEDADES CONSULTADAS NA QUERY
        $prefix    = "http://www.w3.org/2004/02/skos/core#";
        $tempQuery = explode("{",$query);
        $tempQuery = $tempQuery[1];
        $tempQuery = explode("skos:",$tempQuery);        
        for($i=1;$i<count($tempQuery);$i++)
        {            
            $prop[] = $prefix.trim(substr($tempQuery[$i],0,strpos($tempQuery[$i],"?")));
        }
        // --------------------------------------------------------------------------------------
        
        // ENCONTAR OS FILTROS USADOS NA QUERY
        $tempQuery = explode("regex",$query);
        for($i=1;$i<count($tempQuery);$i++)
        {
            $filtro[] = substr($tempQuery[$i],strpos($tempQuery[$i],"'")+1,strrpos($tempQuery[$i],"'")-strpos($tempQuery[$i],"'")-1);
        }
        // --------------------------------------------------------------------------------------
        
        $tempQuery  = "SELECT DISTINCT modelID FROM statements WHERE ";
        if(count($prop)==1)
        {
            $tempQuery .= "predicate = '".$prop[0]."' AND object LIKE '".$filtro[0]."' ";
        }else{
            /*
                se veio com mais de uma propriedade, então eu tenho que achar o mesmo model
                para todas essas propriedades, só assim eu vou pegar o registro correto.
                Então vou ter que consultar esse model antes pra colocar ele na nova query
            */
            
            $queryParaPegarModel = $tempQuery .= "predicate = '".$prop[0]."' AND object LIKE '%".$filtro[0]."%' ";
            //echo "<br>Pegando o Model: ".$queryParaPegarModel;
            $retornoModel = mysql_query($queryParaPegarModel);
            $peloMenosUm = false;
            $salvaRegistro="";
            while($qualModel = mysql_fetch_array($retornoModel))
            {
                $tempQuery  = "SELECT DISTINCT modelID FROM statements WHERE modelID = '".$qualModel[0]."' AND ";
            
            
                for($i=1;$i<count($prop);$i++)
                {            
                    if($i>1)
                    {
                        $tempQuery .= "OR ";
                    }
                    //$tempQuery .= "predicate = '".$prop[$i]."' AND object = '".$filtro[$i]."' "; 
                    $tempQuery .= "predicate = '".$prop[$i]."' AND object like '%".$filtro[$i]."%' ";
                }
                //echo $tempQuery;
                $retorno = mysql_query($tempQuery);  
                
                             
                while($pesquisa = mysql_fetch_array($retorno))
                {
                    $peloMenosUm = true;
                    $tempQuery  = "SELECT subject, object FROM statements WHERE modelID='".$pesquisa[0]."' AND predicate='".$prefix."prefLabel"."'";
                    //echo $tempQuery;
                    $resultado  = mysql_query($tempQuery);
                    $registro   = mysql_fetch_assoc($resultado);
                    $queryPub   = "SELECT modelID FROM statements WHERE predicate='http://purl.org/dc/terms/subject' AND object='".$registro['subject']."'";
                    
                    //echo $queryPub;
                    $retornoPub = mysql_query($queryPub);
                    if(mysql_num_rows($retornoPub))
                    {
                        if($salvaRegistro!=$registro['subject'])
                        {
                            echo "<h3>RESULTADO:</h3><br/>";
                            $queryPublicacao  = "SELECT p.tituloPub, t.nomeTipo, s.subject ";
                            $queryPublicacao .= "FROM publicacao p, palavra pal, pubpalavra pp, tipo t, statements s ";
                            $queryPublicacao .= "WHERE pal.idRdfPalavra = '".$pesquisa[0]."' AND pal.idPalavra = pp.idPalavrapp AND p.idPub = pp.idPubpp AND p.idRdfPub=s.modelID LIMIT 1";
                            $retornoPublicacao = mysql_query($queryPublicacao);
                            $publicacaoRelacionada = mysql_fetch_assoc($retornoPublicacao);
                            echo "<li>".$registro['object'];
                            echo "&nbsp;&nbsp;&lt;<a href='".$registro['subject']."' target='_blank'>".$registro['subject']."</a>&gt;</li>";
                            echo "<li> UTILIZADA EM: ";
                            echo "<a href='".$publicacaoRelacionada['subject']."' target='_blank'>";
                            echo $publicacaoRelacionada['tituloPub']."</a><hr/></li>";
                        }
                        $salvaRegistro = $registro['subject'];             
                    }                             
                }                
            }
            if(!$peloMenosUm)
            {
                $this->alertaAtencao("NÃO FORAM ENCONTRAS PUBLICAÇÕES RELACIONADAS");
                return false;               
            }
            return true;
        }
        //echo "Query: ".$tempQuery;
        $retorno = mysql_query($tempQuery);
        $quantos = mysql_num_rows($retorno);
        if(!$quantos)
        {
            $this->alertaAtencao("NÃO FORAM ENCONTRAS PUBLICAÇÕES RELACIONADAS");
            return false;
        }
        echo "<h3>RESULTADOS:</h3><br/>";
        $achouUm = false;
        while($pesquisa = mysql_fetch_array($retorno))
        {
            $tempQuery  = "SELECT subject, object FROM statements WHERE modelID='".$pesquisa[0]."' AND predicate='".$prefix."prefLabel"."'";
            //echo $tempQuery;
            $resultado  = mysql_query($tempQuery);
            $registro   = mysql_fetch_assoc($resultado);
            $queryPub   = "SELECT modelID FROM statements WHERE predicate='http://purl.org/dc/terms/subject' AND object='".$registro['subject']."'";
            
            //echo $queryPub;
            $retornoPub = mysql_query($queryPub);
            if(mysql_num_rows($retornoPub))
            {
                $achouUm = true;
                //echo "<li>".$registro['object'];
                //echo "&nbsp;&nbsp;&lt;<a href='".$registro['subject']."' target='_blank'>".$registro['subject']."</a>&gt;</li><hr/>";
                
                $queryPublicacao  = "SELECT p.tituloPub, t.nomeTipo, s.subject ";
                $queryPublicacao .= "FROM publicacao p, palavra pal, pubpalavra pp, tipo t, statements s ";
                $queryPublicacao .= "WHERE pal.idRdfPalavra = '".$pesquisa[0]."' AND pal.idPalavra = pp.idPalavrapp AND p.idPub = pp.idPubpp AND p.idRdfPub=s.modelID LIMIT 1";
                $retornoPublicacao = mysql_query($queryPublicacao);
                $publicacaoRelacionada = mysql_fetch_assoc($retornoPublicacao);
                echo "<li>".$registro['object'];
                echo "&nbsp;&nbsp;&lt;<a href='".$registro['subject']."' target='_blank'>".$registro['subject']."</a>&gt;</li>";
                echo "<li> UTILIZADA EM: ";
                echo "<a href='".$publicacaoRelacionada['subject']."' target='_blank'>";
                echo $publicacaoRelacionada['tituloPub']."</a><hr/></li>";                
            }            
        }
        if(!$achouUm)
        {
            $this->alertaAtencao("NÃO FORAM ENCONTRAS PUBLICAÇÕES RELACIONADAS");
            return false;
        }
        return true;
        
   }
}
?>
