<?php

class Palavra extends Diversa{

    private $tarefaAtiva;
    private $acaoAtiva;
    private $classe = 'Palavra';
    private $tabela = 'palavra';
    private $pk     = 'idPalavra';      // CHAVE PRIMÁRIA PARA SELECTS
    private $cppe   = 'textoPalavra';    // COLUNA PRINCIPAL PARA MÉTODO EXCLUIR
    private $uriDbpedia = "http://dbpedia.org/resource/";

    function __construct($acao)
    {
        $this->acaoAtiva=$acao;
    }

    public function defineTarefa($tarefa)
    {
        $this->tarefaAtiva = $tarefa;
        switch($tarefa)
        {
            case "t1": // formulário
                $this->form($_GET['k']);
                break;
            case "t2": // gravar registro
                $this->gravar();
                break;
            case "t3": // confirmar excluir
                $this->confirmarExcluir($_GET['k']);
                break;
            case "t4": // excluir
                $this->excluir();
                break;
            case "t5": // Linked Data com a dbpedia
                $this->dbpedia($_GET['k']);
                break;
            default:   // mesmo que t0
                $this->lista();
                break;
        }
    }

    private function lista()
    {
        ?>
        <div class="left">
            <a class="btn btn-success" href="?a=<?php echo $this->acaoAtiva;?>&k=0&t=t1">
	           <i class="icon-plus icon-white"></i>
               Novo Registro
	        </a>
        </div>
        <br/>

        <table class="table table-striped table-bordered bootstrap-datatable datatable">
            <thead>
                <tr>
                    <th>ID</th>
				    <th>Palavra-chave</th>
                    <th>URI</th>
                    <th>Ações</th>
				</tr>
            </thead>
			<tbody>
            <?php
                $retorno = $this->consultaSql("SELECT * FROM $this->tabela");
                while($tupla = mysql_fetch_array($retorno))
                {
                    echo "<tr>";
				    echo "<td class='center'>$tupla[0]</td>";
					echo "<td class='left'>";
                    if($tupla[2]=='__')
                    {
                        $this->alertaAtencao($tupla[1]);
                    }else{
                        echo $tupla[1];
                    }
                    $linkHref = $this->buscaUriBase()."keyword/".$this->poeUnderline($tupla[1]);                    
                    echo "</td><td><a href='$linkHref' target='_blank' />".$linkHref."</a></td>";
            ?>
					<td class="center">
					   <a class="btn btn-info" href="?a=<?php echo $this->acaoAtiva;?>&k=<?php echo $tupla[0];?>&t=t1">
					       <i class="icon-edit icon-white"></i>
					       Editar
					   </a>
                       <a class="btn btn-danger" href="?a=<?php echo $this->acaoAtiva;?>&k=<?php echo $tupla[0];?>&t=t3">
	                       <i class="icon-trash icon-white"></i>
    					   Excluir
					   </a>
                    <?php
                        if($tupla[7]=='N')
                        {
                    ?>
                       <a class="btn" href="?a=<?php echo $this->acaoAtiva;?>&k=<?php echo $tupla[0];?>&t=t5">
	                       <i class="icon-screenshot icon-black"></i>
    					   Dbpedia
					   </a>
                    <?php
                        }
                    ?>
                    </td>
                </tr>
        <?php } ?>
            </tbody>
        </table>
        <?php
    }

    private function lerTupla($k)
    {
        $retorno = $this->consultaSql("SELECT * FROM $this->tabela WHERE $this->pk='$k'");
        return mysql_fetch_assoc($retorno);
    }

    private function form($k)
    {
        $titulo = "Incluir novo registro";
        
        if($k)
        {
            $tupla  = $this->lerTupla($k);
            $titulo = "Editar registro";
        }

        // SESSÃO IMPORTANTE PARA A GRAVAÇÃO DO REGISTRO
        $_SESSION['s_gravar'] = true;
        // ---------------------------------------------
        $estilo = "width: 170px; margin-right:10px;";
?>
        <!-- NOVO FORMULÁRIO -->
        <form name="<?php echo $this->classe;?>" class="form-horizontal" method="post" action="?a=<?php echo $this->acaoAtiva;?>&t=t2">
            <!-- A VARIÁVEL CHAVE É RESPONSÁVEL POR IDENTIFICAR UM NOVO REGISTRO OU UMA ALTERAÇÃO -->
            <input type="hidden" name="chave" value="<?php echo $k;?>"/>
            <!-- -------------------------------------------------------------------------------- -->
            <input type="hidden" name="idRdfPalavra" value="<?php echo $tupla['idRdfPalavra'];?>" />
            <input type="hidden" name="resourcePalavra" value="<?php echo $tupla['resourcePalavra'];?>" />            
            <!-- -------------------------------------------------------------------------------- -->
            <fieldset>
                <legend><?php echo $titulo;?></legend>
                <div class="control-group">
                    <label class="control-label" for="textoPalavra" style="<?php echo $estilo;?>">Palavra-chave:</label>
                    <div class="controls">
                        <input autofocus="autofocus" required="required" type="text" name="textoPalavra" style="width: 200px;" maxlength="50" class="span6" id="textoPalavra" value="<?php echo $tupla['textoPalavra'];?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="definicaoPalavra" style="<?php echo $estilo;?>">Definição:</label>
                    <div class="controls">
                        <input autofocus="autofocus" required="required" type="text" name="definicaoPalavra" style="width: 600px;" maxlength="200" class="span6" id="definicaoPalavra" value="<?php echo $tupla['definicaoPalavra'];?>" />
                    </div>
                </div>
                <br/><h5>OFEREÇA MAIS DETALHES PARA ESTA PALAVRA-CHAVE:</h5><br/>
                
                <div class="control-group">
                    <label class="control-label" for="alternativoPalavra" style="<?php echo $estilo;?>">Nome alternativo:</label>
                    <div class="controls" style="float: left; margin-left: 2px; margin-right: 10px;">
                        <?php
                            $retornoJaTem = $this->consultaSql("SELECT idPalavra, textoPalavra, idRdfPalavra FROM $this->tabela WHERE idPalavra NOT LIKE $k ORDER BY textoPalavra"); 
                        ?>
                        <select name="jaTemAlternativo">
                            <option value="0" selected="selected">Selecione existente</option>
                            <?php
                                mysql_data_seek($retornoJaTem,0);
                                while($jaTem = mysql_fetch_array($retornoJaTem))
                                {
                                    if(intval($tupla['alternativoPalavra'])==$jaTem[2])
                                    {
                                        echo "<option value='$jaTem[2]' selected='selected'>$jaTem[1]</option>";
                                        $tupla['alternativoPalavra'] = "";                                        
                                    }else{
                                        if($jaTem[1]==$tupla['alternativoPalavra'])
                                        {
                                            echo "<option value='$jaTem[2]' selected='selected'>$jaTem[1]</option>";
                                        }else{
                                            echo "<option value='$jaTem[2]'>$jaTem[1]</option>";                                            
                                        }                                        
                                    }
                                }
                            ?>                            
                        </select>
                    </div>
                    <div class="controls" style="float: left;margin: 0px 2px 0px 2px; padding-top: 5px;">
                        ou informe um novo termo:
                    </div> 
                    <div class="controls" style="float: left; margin-left: 2px;">
                        <input autofocus="autofocus" type="text" name="alternativoPalavra" style="width: 200px; margin: 0px;" maxlength="50" class="span6" id="alternativoPalavra" value="<?php echo $tupla['alternativoPalavra'];?>" />
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label" for="amploPalavra" style="<?php echo $estilo;?>">Conceito mais amplo:</label>
                    <div class="controls" style="float: left; margin-left: 2px; margin-right: 10px;">                        
                        <select name="jaTemAmplo">
                            <option value="0" selected="selected">Selecione existente</option>
                            <?php
                                mysql_data_seek($retornoJaTem,0);
                                while($jaTem = mysql_fetch_array($retornoJaTem))
                                {
                                    if(intval($tupla['amploPalavra'])==$jaTem[2])
                                    {
                                        echo "<option value='$jaTem[2]' selected='selected'>$jaTem[1]</option>";
                                        $tupla['amploPalavra'] = "";                                        
                                    }else{
                                        if($jaTem[1]==$tupla['amploPalavra'])
                                        {
                                            echo "<option value='$jaTem[2]' selected='selected'>$jaTem[1]</option>";
                                        }else{
                                            echo "<option value='$jaTem[2]'>$jaTem[1]</option>";                                            
                                        }
                                    }
                                }
                            ?>                            
                        </select>
                    </div>
                    <div class="controls" style="float: left;margin: 0px 2px 0px 2px; padding-top: 5px;">
                        ou informe um novo termo:
                    </div> 
                    <div class="controls" style="float: left; margin-left: 2px;">
                        <input autofocus="autofocus" type="text" name="amploPalavra" style="width: 200px;" maxlength="50" class="span6" id="amploPalavra" value="<?php echo $tupla['amploPalavra'];?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="especificoPalavra" style="<?php echo $estilo;?>">Conceito mais específico:</label>
                    <div class="controls" style="float: left; margin-left: 2px; margin-right: 10px;">                        
                        <select name="jaTemEspecifico">
                            <option value="0" selected="selected">Selecione existente</option>
                            <?php
                                mysql_data_seek($retornoJaTem,0);
                                while($jaTem = mysql_fetch_array($retornoJaTem))
                                {
                                    if(intval($tupla['especificoPalavra'])==$jaTem[2])
                                    {
                                        echo "<option value='$jaTem[2]' selected='selected'>$jaTem[1]</option>";
                                        $tupla['especificoPalavra'] = "";                                        
                                    }else{
                                        if($jaTem[1]==$tupla['especificoPalavra'])
                                        {
                                            echo "<option value='$jaTem[2]' selected='selected'>$jaTem[1]</option>";
                                        }else{
                                            echo "<option value='$jaTem[2]'>$jaTem[1]</option>";                                            
                                        }
                                    }
                                }
                            ?>                            
                        </select>
                    </div>
                    <div class="controls" style="float: left;margin: 0px 2px 0px 2px; padding-top: 5px;">
                        ou informe um novo termo:
                    </div> 
                    <div class="controls" style="float: left; margin-left: 2px;">
                        <input autofocus="autofocus" type="text" name="especificoPalavra" style="width: 200px;" maxlength="50" class="span6" id="especificoPalavra" value="<?php echo $tupla['especificoPalavra'];?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="relacionadoPalavra" style="<?php echo $estilo;?>">Conceito relacionado:</label>
                    <div class="controls" style="float: left; margin-left: 2px; margin-right: 10px;">                        
                        <select name="jaTemRelacionado">
                            <option value="0" selected="selected">Selecione existente</option>
                            <?php
                                mysql_data_seek($retornoJaTem,0);
                                while($jaTem = mysql_fetch_array($retornoJaTem))
                                {
                                    if(intval($tupla['relacionadoPalavra'])==$jaTem[2])
                                    {
                                        echo "<option value='$jaTem[2]' selected='selected'>$jaTem[1]</option>";
                                        $tupla['relacionadoPalavra'] = "";                                        
                                    }else{
                                        if($jaTem[1]==$tupla['relacionadoPalavra'])
                                        {
                                            echo "<option value='$jaTem[2]' selected='selected'>$jaTem[1]</option>";
                                        }else{
                                            echo "<option value='$jaTem[2]'>$jaTem[1]</option>";                                            
                                        }
                                    }
                                }
                            ?>                            
                        </select>
                    </div>
                    <div class="controls" style="float: left;margin: 0px 2px 0px 2px; padding-top: 5px;">
                        ou informe um novo termo:
                    </div> 
                    <div class="controls" style="float: left; margin-left: 2px;">
                        <input autofocus="autofocus" type="text" name="relacionadoPalavra" style="width: 200px;" maxlength="50" class="span6" id="relacionadoPalavra" value="<?php echo $tupla['relacionadoPalavra'];?>" />
                    </div>
                </div>
                <br/>
                <?php
                    if($k)
                    {
                ?>
                    <div class="control-group">
    				    <label class="control-label" style="<?php echo $estilo;?>">Permitir busca na Dbpedia:</label>
                        <div class="controls">
                            <label class="checkbox inline">
                                <input type="checkbox" name="temDbpedia" id="inlineCheckbox1" value="N" /> Marque para habilitar a consulta a dados na Dbpedia
                            </label>
                        </div>
                        <?php
                            $rStatements = $this->consultaSql("SELECT object FROM statements WHERE modelID='".$tupla['idRdfPalavra']."' AND predicate='http://www.w3.org/2000/01/rdf-schema#seeAlso'");
                            if(mysql_num_rows($rStatements))
                            {
                                $tStatements = mysql_fetch_assoc($rStatements);
                                echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                                echo "Já possui o recurso <a href='".$tStatements['object']."' target=_blank>".$tStatements['object']."</a>";                                 
                            }
                        ?>
    				</div> 
                <?php
                    }
                ?>               
                <div class="form-actions">
                    <button type="button" class="btn" onclick="mudaPagina('?a=<?php echo $this->acaoAtiva;?>')"> Cancelar </button>
                    &nbsp;&nbsp;&nbsp;&nbsp;
				    <button type="button" class="btn btn-primary" onclick="verificar<?php echo $this->classe;?>()"> Gravar registro </button>
                </div>
            </fieldset>
        </form>
        <!-- FIM DO NOVO FORMULÁRIO -->

<?php
    }

    private function dbpedia($k)
    {
        echo "<fieldset><legend>Recursos da Dbpedia</legend>";
        
        if($_POST['chave'])
        {
            $chave  = $_POST['chave'];
            mysql_query("UPDATE $this->tabela SET resourcePalavra='S' WHERE $this->pk='$chave'");
            $tupla  = $this->lerTupla($chave);
            
            $rModel = $this->consultaSql("SELECT modelURI FROM models WHERE modelID='".$tupla['idRdfPalavra']."'");
            $tModel = mysql_fetch_assoc($rModel);
                        
            mysql_query("INSERT INTO statements (modelID, subject, predicate, object, subject_is, object_is) VALUES ('".$tupla['idRdfPalavra']."','".$tModel['modelURI']."','http://www.w3.org/2000/01/rdf-schema#seeAlso','".$this->uriDbpedia.$this->poeUnderline($tupla['textoPalavra'])."','r','r')");
            
            $this->alertaSucesso('Registro gravado com sucesso');
            
        }else{
            
            require_once("sparqllib/sparqllib.php");
            //conectando no sparql endpoint da dbpedia
            $db = sparql_connect("http://dbpedia.org/sparql");     
            //testando se a conexão foi bem sucedida
        	if(!$db)
            {
        		print sparql_errno() . ": " . sparql_error(). "\n"; exit;
        	}    
            $tupla      = $this->lerTupla($k);
            $consulta   = $this->poeUnderline($tupla['textoPalavra']);
            $consulta   = $this->converterUtf8Hex($consulta);
            //consulta sparql
            
            $sparql = "
            PREFIX rdfs:<http://www.w3.org/2000/01/rdf-schema#>
            SELECT ?d
            WHERE
            {          
                <$this->uriDbpedia$consulta> rdfs:label ?c .
                <$this->uriDbpedia$consulta> rdfs:comment ?d .
                FILTER(langMatches(lang(?c), 'PT') && langMatches(lang(?d), 'PT'))
            }";            
            //execultando a consulta
            //echo "<pre>";
            //var_dump(htmlentities($sparql));
            //echo "</pre>";
            
            
    	    $result = sparql_query($sparql);
            
            if(!$result)
        	{
        		print sparql_errno() . ": " . sparql_error(). "\n"; exit;
        	}
    
            $fields = sparql_field_array($result);
            $tem    = false;
            
            echo "<form name='recursoDbpedia' class='form-horizontal' method='post' action='?a=$this->acaoAtiva&t=t5'>";            
            echo "<input type='hidden' name='chave' value='$k'/>"; 
            echo "<ul>";
        	while($row = sparql_fetch_array($result))
        	{
        		foreach($fields as $field)
        		{
        			echo "<li>".utf8_decode($row[$field])."<br/>Disponível em: ";
                    echo "<a href='".$this->uriDbpedia.$consulta."' target='_blank'>".$this->uriDbpedia.$this->poeUnderline($tupla['textoPalavra']);
                    echo "</a></li>";                    
                    $tem = true;
        		}
        	}
            echo "</ul>";
        	if(!$tem)
        	{
        	   echo "Não foram encontrados recursos para ".$tupla['textoPalavra'];
        	}else{
        	   ?>
               <div class="form-actions" style="padding-left: 20px;">
                    <button type="button" class="btn" onclick="mudaPagina('?a=<?php echo $this->acaoAtiva;?>')"> Cancelar </button>
                    &nbsp;&nbsp;&nbsp;&nbsp;                    
				    <button type="button" class="btn btn-primary" onclick="submit()"> Associar este recurso à palavra-chave <i><?php echo $tupla['textoPalavra'];?></i></button>
                </div>
               <?php        	   
        	}
            echo "</form>";
        }
        echo "</fieldset>";
    }

    /*

        // DBPEDIA LOOKUP -----------------------
        $simple = file_get_contents ("http://lookup.dbpedia.org/api/search.asmx/KeywordSearch?QueryString=$consulta");
        if($simple)
        {
            $xml   = simplexml_load_string($simple);
            $json  = json_encode($xml);
            $array = json_decode($json,TRUE);
            for($i=0;$i<count($xml);$i++)
            {
            	echo $array['Result'][$i]['Label']."<br/>";
            	echo $array['Result'][$i]['URI']."<br/>";
            	if(!is_array($array['Result'][$i]['Description']))
            	echo $array['Result'][$i]['Description']."<br/>";
            	echo "<hr/>";
            }
        }
        //---------------------------------------

    */
    
    private function gravar()
    {
        if(isset($_POST['chave']) && $_SESSION['s_gravar']) // EVITA PROBLEMA COM F5
        {
            $chave        = $_POST['chave'];
            $textoPalavra = $this->pegaPost('textoPalavra');

            if(!$chave)
            {
                $retorno = $this->consultaSql("SELECT $this->pk FROM $this->tabela WHERE textoPalavra='$textoPalavra'");
                $quantos = mysql_num_rows($retorno);
                if($quantos)
                {
                    $this->alertaAtencao('ATENÇÃO! Esta palavra-chave já está cadastrada');
                    //$k = mysql_fetch_assoc($retorno);
                    //$this->form($k['idTipo']);
                    exit;
                }
            }

            $definicaoPalavra   = $this->pegaPost('definicaoPalavra');
            $alternativoPalavra = $this->pegaPost('alternativoPalavra');
            $amploPalavra       = $this->pegaPost('amploPalavra');
            $especificoPalavra  = $this->pegaPost('especificoPalavra');
            $relacionadoPalavra = $this->pegaPost('relacionadoPalavra');
            $idRdfPalavra       = $_POST['idRdfPalavra'];
            $temDbpedia         = $_POST['temDbpedia'];
            if($temDbpedia!="N")
            {
                $temDbpedia = "S";
            }
            
            $jaTemAlternativo   = $_POST['jaTemAlternativo'];
            $jaTemAmplo         = $_POST['jaTemAmplo'];
            $jaTemEspecifico    = $_POST['jaTemEspecifico'];
            $jaTemRelacionado   = $_POST['jaTemRelacionado'];            

            $uriPalavra   = $this->buscaUriBase()."keyword/".$this->poeUnderline($textoPalavra);

            unset($statement);
            $prefLabel  = new Resource("http://www.w3.org/2004/02/skos/core#prefLabel");
            $definition = new Resource("http://www.w3.org/2004/02/skos/core#definition");
            $altLabel   = new Resource("http://www.w3.org/2004/02/skos/core#altLabel");
            $broader    = new Resource("http://www.w3.org/2004/02/skos/core#broader");
            $narrower   = new Resource("http://www.w3.org/2004/02/skos/core#narrower");
            $related    = new Resource("http://www.w3.org/2004/02/skos/core#related");
            $Concept    = new Resource("http://www.w3.org/2004/02/skos/core#Concept");

            if(!$chave)
            {
                mysql_query("INSERT INTO $this->tabela (textoPalavra, definicaoPalavra, alternativoPalavra, amploPalavra, especificoPalavra, relacionadoPalavra) VALUES ('$textoPalavra', '$definicaoPalavra', '$alternativoPalavra', '$amploPalavra', '$especificoPalavra', '$relacionadoPalavra')");
                $retorno      = $this->consultaSql("SELECT $this->pk FROM $this->tabela WHERE textoPalavra='$textoPalavra'");
                $esteRegistro = mysql_fetch_assoc($retorno);

                // INÍCIO DA GRAVAÇÃO DAS TRIPLAS
                unset($statement);
                $statement[] = new Statement(new Resource($uriPalavra), RDF::TYPE(), $Concept);
                $statement[] = new Statement(new Resource($uriPalavra), $prefLabel, new Literal($textoPalavra,'PT'));
                $statement[] = new Statement(new Resource($uriPalavra), $definition, new Literal($definicaoPalavra,'PT'));
                
                if($jaTemAlternativo)
                {
                    $retornoJaTem       = mysql_query("SELECT modelURI FROM models WHERE modelID='$jaTemAlternativo'");
                    $uriJaTem           = mysql_fetch_assoc($retornoJaTem);
                    $statement[]        = new Statement(new Resource($uriPalavra), $altLabel, new Resource($uriJaTem['modelURI']));
                    $alternativoPalavra = $jaTemAlternativo;                    
                }else{
                    if($alternativoPalavra!="")
                    {
                        $statement[] = new Statement(new Resource($uriPalavra), $altLabel, new Literal($alternativoPalavra,'PT'));
                    }                    
                }
                
                if($jaTemAmplo)
                {
                    $retornoJaTem = mysql_query("SELECT modelURI FROM models WHERE modelID='$jaTemAmplo'");
                    $uriJaTem     = mysql_fetch_assoc($retornoJaTem);
                    $statement[]  = new Statement(new Resource($uriPalavra), $broader, new Resource($uriJaTem['modelURI']));
                    $amploPalavra = $jaTemAmplo;                    
                }else{
                    if($amploPalavra!="")
                    {
                        $statement[] = new Statement(new Resource($uriPalavra), $broader, new Literal($amploPalavra,'PT'));
                    }
                }
                
                if($jaTemEspecifico)
                {
                    $retornoJaTem      = mysql_query("SELECT modelURI FROM models WHERE modelID='$jaTemEspecifico'");
                    $uriJaTem          = mysql_fetch_assoc($retornoJaTem);
                    $statement[]       = new Statement(new Resource($uriPalavra), $narrower, new Resource($uriJaTem['modelURI']));
                    $especificoPalavra = $jaTemEspecifico;
                }else{                    
                    if($especificoPalavra!="")
                    {
                        $statement[] = new Statement(new Resource($uriPalavra), $narrower, new Literal($especificoPalavra,'PT'));
                    }
                }
                
                if($jaTemRelacionado)
                {
                    $retornoJaTem       = mysql_query("SELECT modelURI FROM models WHERE modelID='$jaTemRelacionado'");
                    $uriJaTem           = mysql_fetch_assoc($retornoJaTem);
                    $statement[]        = new Statement(new Resource($uriPalavra), $related, new Resource($uriJaTem['modelURI']));
                    $relacionadoPalavra = $jaTemRelacionado;
                }else{
                    if($relacionadoPalavra!="")
                    {
                        $statement[] = new Statement(new Resource($uriPalavra), $related, new Literal($relacionadoPalavra,'PT'));
                    }
                }

                $model       = ModelFactory::getDefaultModel();
                for($i=0; $i<count($statement); $i++)
                {
                    $model->add($statement[$i]);
                }
                $mysql_database = ModelFactory::getDbStore();
                $mysql_database->putModel($model,$uriPalavra);
                // FIM DA GRAÇÃO DAS TRIPLAS

                // VOU PROCURAR NA TABELA MODELS PELA URI

                $retorno = $this->consultaSql("SELECT modelID FROM models WHERE modelURI='$uriPalavra'");
                $qmodel  = mysql_fetch_assoc($retorno);
                mysql_query("UPDATE $this->tabela SET idRdfPalavra='".$qmodel['modelID']."', alternativoPalavra='$alternativoPalavra', amploPalavra='$amploPalavra', especificoPalavra='$especificoPalavra', relacionadoPalavra='$relacionadoPalavra' WHERE $this->pk='".$esteRegistro[$this->pk]."'");

            }else{

                mysql_query("DELETE FROM models WHERE modelID='$idRdfPalavra'");
                mysql_query("DELETE FROM statements WHERE modelID='$idRdfPalavra'");

                // INÍCIO DA GRAVAÇÃO DAS TRIPLAS
                $statement[] = new Statement(new Resource($uriPalavra), RDF::TYPE(), $Concept);
                $statement[] = new Statement(new Resource($uriPalavra), $prefLabel, new Literal($textoPalavra,'PT'));
                $statement[] = new Statement(new Resource($uriPalavra), $definition, new Literal($definicaoPalavra,'PT'));
              
                if($jaTemAlternativo)
                {
                    $retornoJaTem = mysql_query("SELECT modelURI FROM models WHERE modelID='$jaTemAlternativo'");
                    $uriJaTem     = mysql_fetch_assoc($retornoJaTem);
                    $statement[]  = new Statement(new Resource($uriPalavra), $altLabel, new Resource($uriJaTem['modelURI']));
                    $alternativoPalavra = $jaTemAlternativo;
                }else{
                    if($alternativoPalavra!="")
                    {
                        $statement[] = new Statement(new Resource($uriPalavra), $altLabel, new Literal($alternativoPalavra,'PT'));
                    }                    
                }
                
                if($jaTemAmplo)
                {
                    $retornoJaTem = mysql_query("SELECT modelURI FROM models WHERE modelID='$jaTemAmplo'");
                    $uriJaTem     = mysql_fetch_assoc($retornoJaTem);
                    $statement[]  = new Statement(new Resource($uriPalavra), $broader, new Resource($uriJaTem['modelURI']));
                    $amploPalavra = $jaTemAmplo;                    
                }else{
                    if($amploPalavra!="")
                    {
                        $statement[] = new Statement(new Resource($uriPalavra), $broader, new Literal($amploPalavra,'PT'));
                    }
                }
                
                if($jaTemEspecifico)
                {
                    $retornoJaTem = mysql_query("SELECT modelURI FROM models WHERE modelID='$jaTemEspecifico'");
                    $uriJaTem     = mysql_fetch_assoc($retornoJaTem);
                    $statement[]  = new Statement(new Resource($uriPalavra), $narrower, new Resource($uriJaTem['modelURI']));
                    $especificoPalavra = $jaTemEspecifico;
                }else{                    
                    if($especificoPalavra!="")
                    {
                        $statement[] = new Statement(new Resource($uriPalavra), $narrower, new Literal($especificoPalavra,'PT'));
                    }
                }
                
                if($jaTemRelacionado)
                {
                    $retornoJaTem = mysql_query("SELECT modelURI FROM models WHERE modelID='$jaTemRelacionado'");
                    $uriJaTem     = mysql_fetch_assoc($retornoJaTem);
                    $statement[]  = new Statement(new Resource($uriPalavra), $related, new Resource($uriJaTem['modelURI']));
                    $relacionadoPalavra = $jaTemRelacionado;
                }else{
                    if($relacionadoPalavra!="")
                    {
                        $statement[] = new Statement(new Resource($uriPalavra), $related, new Literal($relacionadoPalavra,'PT'));
                    }
                }

                $model       = ModelFactory::getDefaultModel();
                for($i=0; $i<count($statement); $i++)
                {
                    $model->add($statement[$i]);
                }
                $mysql_database = ModelFactory::getDbStore();
                $mysql_database->putModel($model,$uriPalavra);
                // FIM DA GRAÇÃO DAS TRIPLAS
                
                mysql_query("UPDATE $this->tabela SET textoPalavra='$textoPalavra', definicaoPalavra='$definicaoPalavra', alternativoPalavra='$alternativoPalavra', amploPalavra='$amploPalavra', especificoPalavra='$especificoPalavra', relacionadoPalavra='$relacionadoPalavra', resourcePalavra='$temDbpedia' WHERE $this->pk='$chave'");

                $retorno = $this->consultaSql("SELECT modelID FROM models WHERE modelURI='$uriPalavra'");
                $qmodel  = mysql_fetch_assoc($retorno);
                mysql_query("UPDATE $this->tabela SET idRdfPalavra='".$qmodel['modelID']."' WHERE $this->pk='$chave'");

            }
            $this->gravaLog($this->classe, $this->tarefaAtiva, $chave);
            $this->alertaSucesso('REGISTRO GRAVADO COM SUCESSO');
            $_SESSION['s_gravar'] = false; // EVITA PROBLEMA COM F5
        }
        $this->lista();
    }

    private function confirmarExcluir($k)
    {
        $tupla = $this->lerTupla($k);
?>
        <form name="<?php echo $this->classe;?>" class="form-horizontal" method="post" action="?a=<?php echo $this->acaoAtiva;?>&t=t4">
            <input type="hidden" name="chave" value="<?php echo $k;?>"/>
            <fieldset>
                <legend>Excluir registro</legend>
                <?php $this->alertaAtencao("CONFIRMA EXCLUIR O REGISTRO: ".$tupla[$this->cppe]."?");?>
                <div class="form-actions">
                    <button type="button" class="btn" onclick="mudaPagina('?a=<?php echo $this->acaoAtiva;?>')"> Cancelar </button>
                    &nbsp;&nbsp;&nbsp;&nbsp;
				    <button type="submit" class="btn btn-primary"> Excluir </button>
                </div>
            </fieldset>
        </form>
<?php
    }

    private function excluir()
    {
        if(isset($_POST['chave']))
        {
            $chave        = $_POST['chave'];
            $tupla        = $this->lerTupla($chave);
            $idRdfPalavra = $tupla['idRdfPalavra'];
            mysql_query("DELETE FROM $this->tabela WHERE $this->pk='$chave'");
            mysql_query("DELETE FROM models WHERE modelID='$idRdfPalavra'");
            mysql_query("DELETE FROM statements WHERE modelID='$idRdfPalavra'");
            mysql_query("DELETE FROM pubpalavra WHERE idpalavrapp='$chave'");
            $this->gravaLog($this->classe, $this->tarefaAtiva, $chave);
            $this->alertaSucesso('REGISTRO EXCLUÍDO');
        }
        $this->lista();
    }

// ----------------------------------------------------------------------------------------------------------------

    public function apresentarPalavra($qual)
    {
        $p = explode("_",$qual);
        $p = implode(" ",$p);
        
        $retorno = $this->consultaSql("SELECT * FROM $this->tabela WHERE $this->cppe='$p'");
        $quantos = mysql_num_rows($retorno);
        if(!$quantos)
        {
            header("Location: ../");
        }
        $palavra = mysql_fetch_assoc($retorno);

        echo "<h2>".strtoupper($palavra['textoPalavra'])."</h2>";
        echo "<p>".$this->buscaUriBase()."keyword/".$this->poeUnderline($palavra['textoPalavra'])."</p>";
        echo "<p>Definição: ".$palavra['definicaoPalavra']."</p><ul>";
        
        if(intval($palavra['alternativoPalavra']))
        {
            $retornoRecurso = mysql_query("SELECT modelURI FROM models WHERE modelID='".intval($palavra['alternativoPalavra'])."'");
            $uriRecurso     = mysql_fetch_assoc($retornoRecurso);
            echo "<li>Nome alternativo: <a href='".$uriRecurso['modelURI']."' target='_blank'>".$uriRecurso['modelURI']."</a></li>";
        }else{
            if($palavra['alternativoPalavra']!="")
            {
                echo "<li>Nome alternativo: ".$palavra['alternativoPalavra']."</li>";
            }
        }
        
        if(intval($palavra['amploPalavra']))
        {
            $retornoRecurso = mysql_query("SELECT modelURI FROM models WHERE modelID='".intval($palavra['amploPalavra'])."'");
            $uriRecurso     = mysql_fetch_assoc($retornoRecurso);
            echo "<li>Conceito mais amplo: <a href='".$uriRecurso['modelURI']."' target='_blank'>".$uriRecurso['modelURI']."</a></li>";
        }else{
            if($palavra['amploPalavra']!="")
            {
                echo "<li>Conceito mais amplo: ".$palavra['amploPalavra']."</li>";
            }
        }
        
        if(intval($palavra['especificoPalavra']))
        {
            $retornoRecurso = mysql_query("SELECT modelURI FROM models WHERE modelID='".intval($palavra['especificoPalavra'])."'");
            $uriRecurso     = mysql_fetch_assoc($retornoRecurso);
            echo "<li>Conceito mais específico: <a href='".$uriRecurso['modelURI']."' target='_blank'>".$uriRecurso['modelURI']."</a></li>";
        }else{
            if($palavra['especificoPalavra']!="")
            {
                echo "<li>Conceito mais específico: ".$palavra['especificoPalavra']."</li>";
            }
        }
        
        if(intval($palavra['relacionadoPalavra']))
        {
            $retornoRecurso = mysql_query("SELECT modelURI FROM models WHERE modelID='".intval($palavra['relacionadoPalavra'])."'");
            $uriRecurso     = mysql_fetch_assoc($retornoRecurso);
            echo "<li>Conceito relacionado: <a href='".$uriRecurso['modelURI']."' target='_blank'>".$uriRecurso['modelURI']."</a></li>";
        }else{
            if($palavra['relacionadoPalavra']!="")
            {
                echo "<li>Conceito relacionado: ".$palavra['relacionadoPalavra']."</li>";
            }                
        }
        

        echo "</ul>";
        
        $query = "SELECT DISTINCT p.tituloPub, s.subject FROM publicacao p, statements s, pubpalavra pp WHERE pp.idpalavrapp='".$palavra['idPalavra']."' AND pp.idpubpp=p.idpub AND p.idRdfPub=s.modelID";
        $retorno = mysql_query($query);
        if(mysql_num_rows($retorno))
        {
            echo "<h3>Publicações relacionadas:</h3><ul>";
            while($relacionadas = mysql_fetch_array($retorno))
            {
                echo "<li><a href='".$relacionadas[1]."'>".$relacionadas[0]."</a></li>";
            }
            echo "</ul>";
        }
        echo "<hr/>";
        $uri1 = $_SERVER['REQUEST_URI'];
        //echo "<pre>";
        //var_dump($_SERVER['REQUEST_URI']);
        //echo "</pre>";
        
	    $uri1 = str_replace("/page/","/data/",$uri1);
        $uri1 = str_replace($p,$this->poeUnderline($this->converterUtf8Hex($palavra['textoPalavra'])),$uri1);
        //echo $uri1;
        echo "<p><a href='$uri1' title='Obter arquivo RDF'><img border='0' src='http://www.w3.org/RDF/icons/rdf_w3c_button.32' alt='RDF Resource Description Framework Icon' /></a></p>";
    }

    public function gerarRDF($q)
    {        
        $mysql_database = ModelFactory::getDbStore();
        $list           = $mysql_database->listModels();
        unset($mmodel);
        foreach ($list as $model)
        {
            $dbModel = $mysql_database->getModel($model['modelURI']);
            $qual = explode("/",$model['modelURI']);

            if(in_array($q,$qual))
            {
                //echo $qual[6]." - ".$k;
                $dbModel->saveAs($this->tiraAcento($q).".rdf");
            }
        }        
    }

// ----------------------------------------------------------------------------------------------------------------


}
?>