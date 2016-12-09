<?php

class Tipo extends Diversa{

    private $tarefaAtiva;
    private $acaoAtiva;
    private $classe  = 'Tipo';
    private $tabela  = 'tipo';
    private $pk      = 'idTipo';      // CHAVE PRIMÁRIA PARA SELECTS
    private $cppe    = 'nomeTipo';    // COLUNA PRINCIPAL PARA MÉTODO EXCLUIR
    private $meioUri = 'type/';

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
				    <th>Tipo de publicação</th>
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
					echo "<td class='left'>$tupla[1]</td>";
                    echo "<td><a href='".$this->buscaUriBase().$this->meioUri.$this->poeUnderline($tupla[1])."' target='_blank' />".$this->buscaUriBase().$this->meioUri.$this->poeUnderline($tupla[1])."</a></td>";
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
            <input type="hidden" name="idRdfTipo" value="<?php echo $tupla['idRdfTipo'];?>" />
            <!-- -------------------------------------------------------------------------------- -->
            <fieldset>
                <legend><?php echo $titulo;?></legend>
                <div class="control-group">
                    <label class="control-label" for="nomeTipo" style="<?php echo $estilo;?>">Tipo de publicação:</label>
                    <div class="controls">
                        <input autofocus="autofocus" required="required" type="text" name="nomeTipo" style="width: 200px;" maxlength="50" class="span6" id="nomeTipo" value="<?php echo $tupla['nomeTipo'];?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="definicaoTipo" style="<?php echo $estilo;?>">Definição:</label>
                    <div class="controls">
                        <input autofocus="autofocus" required="required" type="text" name="definicaoTipo" style="width: 600px;" maxlength="200" class="span6" id="definicaoTipo" value="<?php echo $tupla['definicaoTipo'];?>" />
                    </div>
                </div>
                <br/><h5>OFEREÇA MAIS DETALHES PARA ESTE TIPO DE PUBLICAÇÃO:</h5><br/>
                <div class="control-group">
                    <label class="control-label" for="alternativoTipo" style="<?php echo $estilo;?>">Nome alternativo:</label>
                    <div class="controls">
                        <input autofocus="autofocus" type="text" name="alternativoTipo" style="width: 200px;" maxlength="50" class="span6" id="alternativoTipo" value="<?php echo $tupla['alternativoTipo'];?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="amploTipo" style="<?php echo $estilo;?>">Conceito mais amplo:</label>
                    <div class="controls">
                        <input autofocus="autofocus" type="text" name="amploTipo" style="width: 200px;" maxlength="50" class="span6" id="amploTipo" value="<?php echo $tupla['amploTipo'];?>" />
                    </div>
                </div>
				<!--
                <div class="control-group">
                    <label class="control-label" for="especificoTipo" style="<?php echo $estilo;?>">Conceito mais específico:</label>
                    <div class="controls">
                        <input autofocus="autofocus" type="text" name="especificoTipo" style="width: 200px;" maxlength="50" class="span6" id="especificoTipo" value="<?php echo $tupla['especificoTipo'];?>" />
                    </div>
                </div>
				-->
                <div class="control-group">
                    <label class="control-label" for="relacionadoTipo" style="<?php echo $estilo;?>">Conceito relacionado:</label>
                    <div class="controls">
                        <input autofocus="autofocus" type="text" name="relacionadoTipo" style="width: 200px;" maxlength="50" class="span6" id="relacionadoTipo" value="<?php echo $tupla['relacionadoTipo'];?>" />
                    </div>
                </div>
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

    private function gravar()
    {
        if(isset($_POST['chave']) && $_SESSION['s_gravar']) // EVITA PROBLEMA COM F5
        {
            $chave    = $_POST['chave'];
            $nomeTipo = strtolower($this->tiraAcento($this->pegaPost('nomeTipo')));
            
            if(!$chave)
            {
                $retorno = $this->consultaSql("SELECT $this->pk FROM $this->tabela WHERE $this->cppe='$nomeTipo'");
                $quantos = mysql_num_rows($retorno);
                if($quantos)
                {
                    $this->alertaAtencao('ATENÇÃO! Este tipo de publicação já está cadastrado');
                    //$k = mysql_fetch_assoc($retorno);
                    //$this->form($k['idTipo']);
                    exit;
                }
            }           
            
            $definicaoTipo   = strtolower($this->tiraAcento($this->pegaPost('definicaoTipo')));
            $alternativoTipo = strtolower($this->tiraAcento($this->pegaPost('alternativoTipo')));
            $amploTipo       = strtolower($this->tiraAcento($this->pegaPost('amploTipo')));
            $especificoTipo  = strtolower($this->tiraAcento($this->pegaPost('especificoTipo')));
            $relacionadoTipo = strtolower($this->tiraAcento($this->pegaPost('relacionadoTipo')));
            $idRdfTipo       = $_POST['idRdfTipo'];
            
            /*
            // DBPEDIA LOOKUP -----------------------
            $simple = file_get_contents ("http://lookup.dbpedia.org/api/search.asmx/KeywordSearch?QueryString=$textoPalavra");
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
            $uriTipo     = $this->buscaUriBase().$this->meioUri.$this->poeUnderline($nomeTipo);
            
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
                mysql_query("INSERT INTO $this->tabela (nomeTipo, definicaoTipo, alternativoTipo, amploTipo, especificoTipo, relacionadoTipo) VALUES ('$nomeTipo', '$definicaoTipo', '$alternativoTipo', '$amploTipo', '$especificoTipo', '$relacionadoTipo')");
                $retorno      = $this->consultaSql("SELECT $this->pk FROM $this->tabela WHERE $this->cppe='$nomeTipo'");
                $esteRegistro = mysql_fetch_assoc($retorno);
                
                // INÍCIO DA GRAVAÇÃO DAS TRIPLAS
                                            
                $statement[] = new Statement(new Resource($uriTipo), RDF::TYPE(), $Concept);
                $statement[] = new Statement(new Resource($uriTipo), $prefLabel, new Literal($nomeTipo,'PT'));
                $statement[] = new Statement(new Resource($uriTipo), $definition, new Literal($definicaoTipo,'PT'));
                if($alternativoTipo!="")
                {
                    $statement[] = new Statement(new Resource($uriTipo), $altLabel, new Literal($alternativoTipo,'PT'));                    
                }
                if($amploTipo!="")
                {
                    $statement[] = new Statement(new Resource($uriTipo), $broader, new Literal($amploTipo,'PT'));                    
                }
                if($especificoTipo!="")
                {
                    $statement[] = new Statement(new Resource($uriTipo), $narrower, new Literal($especificoTipo,'PT'));                    
                }
                if($relacionadoTipo!="")
                {
                    $statement[] = new Statement(new Resource($uriTipo), $related, new Literal($relacionadoTipo,'PT'));                    
                }
                
                $model       = ModelFactory::getDefaultModel();
                for($i=0; $i<count($statement); $i++)
                {
                    $model->add($statement[$i]);
                }
                $mysql_database = ModelFactory::getDbStore();
                $mysql_database->putModel($model,$uriTipo);
                // FIM DA GRAÇÃO DAS TRIPLAS

                // VOU PROCURAR NA TABELA MODELS PELA URI
                
                $retorno = $this->consultaSql("SELECT modelID FROM models WHERE modelURI='$uriTipo'");
                $qmodel  = mysql_fetch_assoc($retorno);
                mysql_query("UPDATE $this->tabela SET idRdfTipo='".$qmodel['modelID']."' WHERE $this->pk='".$esteRegistro[$this->pk]."'");               
                
            }else{
                
                mysql_query("DELETE FROM models WHERE modelID='$idRdfTipo'");
                mysql_query("DELETE FROM statements WHERE modelID='$idRdfTipo'");
                
                mysql_query("UPDATE $this->tabela SET nomeTipo='$nomeTipo', definicaoTipo='$definicaoTipo', alternativoTipo='$alternativoTipo', amploTipo='$amploTipo', especificoTipo='$especificoTipo', relacionadoTipo='$relacionadoTipo' WHERE $this->pk='$chave'");
                
                // INÍCIO DA GRAVAÇÃO DAS TRIPLAS               
                $statement[] = new Statement(new Resource($uriTipo), RDF::TYPE(), $Concept);
                $statement[] = new Statement(new Resource($uriTipo), $prefLabel, new Literal($nomeTipo,'PT'));
                $statement[] = new Statement(new Resource($uriTipo), $definition, new Literal($definicaoTipo,'PT'));
                if($alternativoTipo!="")
                {
                    $statement[] = new Statement(new Resource($uriTipo), $altLabel, new Literal($alternativoTipo,'PT'));                    
                }
                if($amploTipo!="")
                {
                    $statement[] = new Statement(new Resource($uriTipo), $broader, new Literal($amploTipo,'PT'));                    
                }
                if($especificoTipo!="")
                {
                    $statement[] = new Statement(new Resource($uriTipo), $narrower, new Literal($especificoTipo,'PT'));                    
                }
                if($relacionadoTipo!="")
                {
                    $statement[] = new Statement(new Resource($uriTipo), $related, new Literal($relacionadoTipo,'PT'));                    
                }
                
                $model       = ModelFactory::getDefaultModel();
                for($i=0; $i<count($statement); $i++)
                {
                    $model->add($statement[$i]);
                }
                $mysql_database = ModelFactory::getDbStore();
                $mysql_database->putModel($model,$uriTipo);
                // FIM DA GRAÇÃO DAS TRIPLAS
                
                $retorno = $this->consultaSql("SELECT modelID FROM models WHERE modelURI='$uriTipo'");
                $qmodel  = mysql_fetch_assoc($retorno);
                mysql_query("UPDATE $this->tabela SET idRdfTipo='".$qmodel['modelID']."' WHERE $this->pk='$chave'");               

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
            $chave     = $_POST['chave'];
            $tupla     = $this->lerTupla($chave);
            $idRdfTipo = $tupla['idRdfTipo'];                        
            mysql_query("DELETE FROM $this->tabela WHERE $this->pk='$chave'");            
            mysql_query("DELETE FROM models WHERE modelID='$idRdfTipo'");
            mysql_query("DELETE FROM statements WHERE modelID='$idRdfTipo'");                      
            $this->gravaLog($this->classe, $this->tarefaAtiva, $chave);
            $this->alertaSucesso('REGISTRO EXCLUÍDO');
        }
        $this->lista();
    }

// ----------------------------------------------------------------------------------------------------------------

    public function apresentarTipo($qual)
    {
        $p = explode("_",$qual);
        $p = implode(" ",$p);
        $retorno = $this->consultaSql("SELECT * FROM $this->tabela WHERE $this->cppe='$p'");
        $quantos = mysql_num_rows($retorno);
        if(!$quantos)
        {
            header("Location: ../");
        }
        $tipo = mysql_fetch_assoc($retorno);
        
        echo "<h2>".strtoupper($tipo['nomeTipo'])."</h2>";        
        echo "<p>".$this->buscaUriBase().$this->meioUri.$this->poeUnderline($p)."</p>";
        echo "<p>Definição: ".$tipo['definicaoTipo']."</p><ul>";
        if($tipo['alternativoTipo']!="")
        {
            echo "<li>Nome alternativo: ".$tipo['alternativoTipo']."</li>";            
        }
        if($tipo['amploTipo']!="")
        {
            echo "<li>Conceito mais amplo: ".$tipo['amploTipo']."</li>";            
        }
        if($tipo['especificoTipo']!="")
        {
            echo "<li>Conceito mais específico: ".$tipo['especificoTipo']."</li>";            
        }
        if($tipo['relacionadoTipo']!="")
        {
            echo "<li>Conceito relacionado: ".$tipo['relacionadoTipo']."</li>";            
        }        
       
        echo "</ul><hr/>";  
        $uri1 = $_SERVER['REQUEST_URI'];
	    $uri1 = str_replace("/page/","/data/",$uri1);	    
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
                $dbModel->saveAs($q.".rdf");                
            }            
        }               
    }
    
// ----------------------------------------------------------------------------------------------------------------


}
?>