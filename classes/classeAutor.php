<?php

class Autor extends Diversa{

    private $tarefaAtiva;
    private $acaoAtiva;
    private $classe = 'Autor';
    private $tabela = 'autor';
    private $pk     = 'idAutor';      // CHAVE PRIMÁRIA PARA SELECTS
    private $cppe   = 'preNomeAutor';    // COLUNA PRINCIPAL PARA MÉTODO EXCLUIR
    private $meioUri = 'people/';

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
				    <th>Autor</th>
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
                    echo "<td class='left'>$tupla[0]</td>";
				    echo "<td class='center'>".strtoupper($tupla[3]).", ".$tupla[1]." ".$tupla[2]."</td>";
                    echo "<td class='left'>";
                    echo "<a href='".$this->buscaUriBase()."people/".$tupla[5]."' target='_blank' />".$this->buscaUriBase()."people/".$tupla[5];
                    echo "</td>";
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

?>
        <!-- NOVO FORMULÁRIO -->
        <form name="<?php echo $this->classe;?>" class="form-horizontal" method="post" action="?a=<?php echo $this->acaoAtiva;?>&t=t2" enctype="multipart/form-data">
            <!-- A VARIÁVEL CHAVE É RESPONSÁVEL POR IDENTIFICAR UM NOVO REGISTRO OU UMA ALTERAÇÃO -->
            <input type="hidden" name="chave" value="<?php echo $k;?>"/>
            <!-- -------------------------------------------------------------------------------- -->
            <input type="hidden" name="idRdfAutor" value="<?php echo $tupla['idRdfAutor'];?>" />
            <input type="hidden" name="nickAnterior" value="<?php echo $tupla['nickAutor'];?>" />
            <!-- -------------------------------------------------------------------------------- -->
            <fieldset>
                <legend><?php echo $titulo;?></legend>
                <div class="control-group">
                    <label class="control-label" for="preNomeAutor">Prenome:</label>
                    <div class="controls">
                        <input autofocus="autofocus" required="required" type="text" name="preNomeAutor" style="width: 200px;" maxlength="50" class="span6" id="preNomeAutor" value="<?php echo $tupla['preNomeAutor'];?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="meioNomeAutor">Nome do meio:</label>
                    <div class="controls">
                        <input autofocus="autofocus" type="text" name="meioNomeAutor" style="width: 200px;" maxlength="50" class="span6" id="meioNomeAutor" value="<?php echo $tupla['meioNomeAutor'];?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="sobreNomeAutor">Sobrenome:</label>
                    <div class="controls">
                        <input autofocus="autofocus" required="required" type="text" name="sobreNomeAutor" style="width: 200px;" maxlength="50" class="span6" id="sobreNomeAutor" value="<?php echo $tupla['sobreNomeAutor'];?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="emailAutor">E-mail:</label>
                    <div class="controls">
                        <input autofocus="autofocus" required="required" type="text" name="emailAutor" style="width: 400px;" maxlength="200" class="span6" id="emailAutor" value="<?php echo $tupla['emailAutor'];?>" />
                    </div>
                </div>
                <div class="control-group">
					<label class="control-label" for="keywordsCat" >Interesses:</label>
					<div class="controls">
					  <select id="keywordsCat" name="keywordsCat[]" multiple data-rel="chosen">
                      <?php
                        $retorno = $this->searchData('keywords');
                        while($r = mysql_fetch_array($retorno))
                        {
                            $acha = mysql_query("SELECT * FROM autorpalavra WHERE idautorap='$k' AND idpalavraap='$r[0]'");
                            if(mysql_num_rows($acha))
                            {
                                echo "<option value='$r[0]' selected='selected'>".$r[1]."</option>";
                            }else{
                                echo "<option value='$r[0]'>".$r[1]."</option>";
                            }
                        }
                      ?>
					  </select>
					</div>
				</div>
                <div class="control-group">
                    <label class="control-label" for="fotoAutor">Foto</label>
                    <div class="controls">
                    <?php
                        $temFoto = false;
                        if(file_exists("image/".$tupla['idAutor'].".jpg"))
                        {
                            echo "<img src='image/".$tupla['idAutor'].".jpg' alt='Autor(a)' style='margin-right:10px; float:left;' width='160' height='160'/>";
                            $temFoto = true;
                        }else{
                            echo "<img src='image/semfoto.jpg' alt='Autor(a)' style='margin-right:10px; float:left;' />";
                        }
                    ?>
                        <input class="input-file uniform_on" id="fotoAutor" name="fotoAutor" type="file" /> (Recomendados 160x160 pixels)
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
            $chave       = $_POST['chave'];
            $emailAutor  = $this->pegaPost('emailAutor');

            if(!$chave)
            {
                $retorno = $this->consultaSql("SELECT $this->pk FROM $this->tabela WHERE emailAutor='$emailAutor'");
                $quantos = mysql_num_rows($retorno);
                if($quantos)
                {
                    $this->alertaAtencao('ATENÇÃO! Este autor já está cadastrado');
                    //$k = mysql_fetch_assoc($retorno);
                    //$this->form($k['idAutor']);
                    exit;
                }
            }

            $preNomeAutor   = $this->pegaPost('preNomeAutor');
            $meioNomeAutor  = $this->pegaPost('meioNomeAutor');
            $sobreNomeAutor = $this->pegaPost('sobreNomeAutor');

            $idRdfAutor     = $_POST['idRdfAutor'];
            $keywordsCat    = $_POST['keywordsCat'];

            $nomeCompleto   = $preNomeAutor." ";
            if($meioNomeAutor<>'')
            {
                $nomeCompleto .= $meioNomeAutor." ";
            }
            $nomeCompleto .= $sobreNomeAutor;

            $foto    = $_FILES['fotoAutor'];

            if(!$chave)
            {
                mysql_query("INSERT INTO $this->tabela (preNomeAutor, meioNomeAutor, sobreNomeAutor, emailAutor) VALUES ('$preNomeAutor', '$meioNomeAutor', '$sobreNomeAutor', '$emailAutor')");
                $retorno      = $this->consultaSql("SELECT $this->pk FROM $this->tabela WHERE emailAutor='$emailAutor'");
                $esteRegistro = mysql_fetch_assoc($retorno);
                $nickAutor    = $this->tiraAcento($preNomeAutor).$this->tiraAcento($sobreNomeAutor);
                $nick         = explode(" ",$nickAutor);
                $nickAutor    = strtolower(implode("",$nick).$esteRegistro[$this->pk]);
                $uriAutor     = $this->buscaUriBase().$this->meioUri.$nickAutor;

                $uriFoto = false;
                if($foto['tmp_name'])
                {
                    move_uploaded_file($foto["tmp_name"], "image/".$esteRegistro[$this->pk].".jpg");
                    $uriFoto = $this->buscaUriBase()."image/".$esteRegistro[$this->pk].".jpg";
                }


                // INÍCIO DA GRAVAÇÃO DAS TRIPLAS
                /*
                    Pensei em usar mais propriedades de FOAF. Então, adicionei FIRSTNAME, LASTNAME E FAMILYNAME
                    para, futuramente, oferecer modelo de referência segundo ABNT. Mas vou manter o FOAF::NAME
                    por enquanto
                */

                unset($statement);

                $statement[] = new Statement(new Resource($uriAutor), RDF::TYPE() , FOAF::PERSON());
                $statement[] = new Statement(new Resource($uriAutor), FOAF::NAME(), new Literal($this->tiraAcento($nomeCompleto),'PT'));
                $statement[] = new Statement(new Resource($uriAutor), FOAF::FIRST_NAME(),new Literal($this->tiraAcento($preNomeAutor),'PT'));

                if($meioNomeAutor<>'')
                {
                    $statement[] = new Statement(new Resource($uriAutor), FOAF::FAMILY_NAME(),new Literal($this->tiraAcento($meioNomeAutor),'PT'));
                }

                $statement[] = new Statement(new Resource($uriAutor), FOAF::LAST_NAME(),new Literal($this->tiraAcento($sobreNomeAutor),'PT'));
                $statement[] = new Statement(new Resource($uriAutor), FOAF::MBOX(), new Literal($emailAutor));

                if($uriFoto)
                {
                    $statement[] = new Statement(new Resource($uriAutor), FOAF::IMG(), new Resource($uriFoto));
                }

                for($i=0;$i<count($keywordsCat);$i++)
                {
                    mysql_query("INSERT INTO autorpalavra (idautorap, idpalavraap) VALUES ('".$esteRegistro[$this->pk]."','$keywordsCat[$i]')");
                    $rPalavra    = $this->consultaSql("SELECT idRdfPalavra FROM palavra WHERE idPalavra='$keywordsCat[$i]'");
                    $tPalavra    = mysql_fetch_assoc($rPalavra);
                    $rStatement  = $this->consultaSql("SELECT subject FROM statements WHERE modelID='".$tPalavra['idRdfPalavra']."' LIMIT 1");
                    $tStatement  = mysql_fetch_assoc($rStatement);
                    $statement[] = new Statement(new Resource($uriAutor), FOAF::TOPIC_INTEREST(), new Resource($tStatement['subject']));
                }

                // INÍCIO DA GRAVAÇÃO DAS TRIPLAS
                $model = ModelFactory::getDefaultModel();
                for($i=0; $i<count($statement); $i++)
                {
                    $model->add($statement[$i]);
                }
                $mysql_database = ModelFactory::getDbStore();
                $mysql_database->putModel($model,$uriAutor);
                // FIM DA GRAÇÃO DAS TRIPLAS

                // PRECISO PEGAR A ID DESSE MODEL NA TABELA PARA GRAVAR EM AUTOR, ALÉM DE GRAVAR SEU NICK
                // VOU PROCURAR NA TABELA MODELS PELA URI

                $retorno = $this->consultaSql("SELECT modelID FROM models WHERE modelURI='$uriAutor'");
                $qmodel  = mysql_fetch_assoc($retorno);
                mysql_query("UPDATE $this->tabela SET nickAutor='$nickAutor', idRdfAutor='".$qmodel['modelID']."' WHERE $this->pk='".$esteRegistro[$this->pk]."'");

                // FEITO.

                // AGORA GRAVAR AS TRIPLAS ESPECIFICAS SE TIVER FOTO

                if($uriFoto)
                {
                    unset($statement);
                    $statement[] = new Statement(new Resource($uriFoto), RDF::TYPE() , FOAF::IMAGE());
                    $statement[] = new Statement(new Resource($uriFoto), FOAF::DEPICTION(), new Resource($uriAutor));

                    $model = ModelFactory::getDefaultModel();
                    for($i=0; $i<count($statement); $i++)
                    {
                        $model->add($statement[$i]);
                    }
                    $mysql_database = ModelFactory::getDbStore();
                    $mysql_database->putModel($model,$uriFoto);
                }

                // FIM DA GRAÇÃO DAS TRIPLAS

            }else{

                mysql_query("DELETE FROM models WHERE modelID='$idRdfAutor'");
                mysql_query("DELETE FROM statements WHERE modelID='$idRdfAutor'");

                $nickAnterior = $_POST['nickAnterior'];
                $nickAutor    = $this->tiraAcento($preNomeAutor).$this->tiraAcento($sobreNomeAutor);
                $nick         = explode(" ",$nickAutor);
                $nickAutor    = strtolower(implode("",$nick).$chave);
                $uriAutor     = $this->buscaUriBase().$this->meioUri.$nickAutor;
                $foto         = $_FILES['fotoAutor'];
                $uriFoto      = false;

                if($foto['tmp_name'])
                {
                    move_uploaded_file($foto["tmp_name"], "image/".$chave.".jpg");
                    $uriFoto = $this->buscaUriBase()."image/".$chave;
                }

                mysql_query("UPDATE $this->tabela SET preNomeAutor='$preNomeAutor', meioNomeAutor='$meioNomeAutor', sobreNomeAutor='$sobreNomeAutor', emailAutor='$emailAutor', nickAutor='$nickAutor' WHERE $this->pk='$chave'");


                // INÍCIO DA GRAVAÇÃO DAS TRIPLAS
                unset($statement);
                $statement[] = new Statement(new Resource($uriAutor), RDF::TYPE() , FOAF::PERSON());
                $statement[] = new Statement(new Resource($uriAutor), RDF::TYPE() , FOAF::AGENT());

                $statement[] = new Statement(new Resource($uriAutor), FOAF::NAME(), new Literal($this->tiraAcento($nomeCompleto),'PT'));
                $statement[] = new Statement(new Resource($uriAutor), FOAF::FIRST_NAME(),new Literal($this->tiraAcento($preNomeAutor),'PT'));
                if($meioNomeAutor<>'')
                {
                    $statement[] = new Statement(new Resource($uriAutor), FOAF::FAMILY_NAME(),new Literal($this->tiraAcento($meioNomeAutor),'PT'));
                }
                $statement[] = new Statement(new Resource($uriAutor), FOAF::LAST_NAME(),new Literal($this->tiraAcento($sobreNomeAutor),'PT'));
                $statement[] = new Statement(new Resource($uriAutor), FOAF::MBOX(), new Literal($emailAutor));
                if($uriFoto)
                {
                    $statement[] = new Statement(new Resource($uriAutor), FOAF::IMG(), new Resource($uriFoto));
                }

                mysql_query("DELETE FROM autorpalavra WHERE idautorap='$chave'");

                for($i=0;$i<count($keywordsCat);$i++)
                {
                    mysql_query("INSERT INTO autorpalavra (idautorap, idpalavraap) VALUES ('$chave','$keywordsCat[$i]')");
                    $rPalavra    = $this->consultaSql("SELECT idRdfPalavra FROM palavra WHERE idPalavra='$keywordsCat[$i]'");
                    $tPalavra    = mysql_fetch_assoc($rPalavra);
                    $rStatement  = $this->consultaSql("SELECT subject FROM statements WHERE modelID='".$tPalavra['idRdfPalavra']."' LIMIT 1");
                    $tStatement  = mysql_fetch_assoc($rStatement);
                    $statement[] = new Statement(new Resource($uriAutor), FOAF::TOPIC_INTEREST(), new Resource($tStatement['subject']));
                }

                $model = ModelFactory::getDefaultModel();
                for($i=0; $i<count($statement); $i++)
                {
                    $model->add($statement[$i]);

                }
                $mysql_database = ModelFactory::getDbStore();
                $mysql_database->putModel($model,$uriAutor);
                // FIM DA GRAÇÃO DAS TRIPLAS

                $retorno = $this->consultaSql("SELECT modelID, modelURI FROM models WHERE modelURI='$uriAutor'");
                $qmodel  = mysql_fetch_assoc($retorno);
                mysql_query("UPDATE $this->tabela SET nickAutor='$nickAutor', idRdfAutor='".$qmodel['modelID']."' WHERE $this->pk='$chave'");

                if(!file_exists($uriFoto.".jpg"))
                {
                    unset($statement);
                    $statement[] = new Statement(new Resource($uriFoto), RDF::TYPE() , FOAF::IMAGE());
                    $statement[] = new Statement(new Resource($uriFoto), FOAF::DEPICTION(), new Resource($uriAutor));

                    $model = ModelFactory::getDefaultModel();
                    for($i=0; $i<count($statement); $i++)
                    {
                        $model->add($statement[$i]);
                    }
                    $mysql_database = ModelFactory::getDbStore();
                    $mysql_database->putModel($model,$uriFoto);
                }
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
                <?php $this->alertaAtencao("CONFIRMA EXCLUIR O REGISTRO: ".strtoupper($tupla[$this->cppe])." ?");?>
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
            $chave      = $_POST['chave'];
            $tupla      = $this->lerTupla($chave);
            $idRdfAutor = $tupla['idRdfAutor'];
            $nickAutor  = $tupla['nickAutor'];
            mysql_query("DELETE FROM $this->tabela WHERE $this->pk='$chave'");
            mysql_query("DELETE FROM models WHERE modelID='$idRdfAutor'");
            mysql_query("DELETE FROM statements WHERE modelID='$idRdfAutor'");
            mysql_query("DELETE FROM autorpalavra WHERE idautorap='$chave'");
            @unlink("img/fotos/".$nickAutor.".jpg");
            $this->gravaLog($this->classe, $this->tarefaAtiva, $chave);
            $this->alertaSucesso('REGISTRO EXCLUÍDO');
        }
        $this->lista();
    }


// ----------------------------------------------------------------------------------------------------------------

    
    public function apresentarAutor($quem)
    {
        $retorno = $this->consultaSql("SELECT * FROM autor WHERE nickAutor='$quem' OR idAutor='$quem'");
        $quantos = mysql_num_rows($retorno);
        if(!$quantos)
        {
            header("Location: ../");
        }
        $autor = mysql_fetch_assoc($retorno);
        if(file_exists("../image/".$autor['idAutor'].".jpg"))
        {
            echo "<img src='../../image/".$autor['idAutor'].".jpg' alt='Autor(a)' style='margin-right:10px; float:left;' width='160' height='160'/>";
        }else{
            echo "<img src='../../image/semfoto.jpg' alt='Autor(a)' style='margin-right:10px; float:left;' />";
        }
        echo "<h2>".$autor['preNomeAutor'];
        if($autor['meioNomeAutor']!='')
        {
            echo " ".$autor['meioNomeAutor'];
        }

        echo " ".$autor['sobreNomeAutor']."</h2>";
        echo "<p>".$autor['emailAutor']."</p>";
        echo "<ul style='float:left;' >";
        echo "<li>".strtoupper($autor['sobreNomeAutor']).", ".$autor['preNomeAutor']." ".$autor['meioNomeAutor']."</li>";
        echo "<li>".$this->buscaUriBase().$this->meioUri.$quem."</li>";

        echo "</ul><br clear='all'><hr/>";
        $uri1 = $_SERVER['REQUEST_URI'];
	    $uri1 = str_replace("/page/","/data/",$uri1);
        $uri2 = str_replace("/people/","/image/",$uri1);
        $uri2 = str_replace($autor['nickAutor'],$autor['idAutor'].".jpg",$uri2);
        echo "<p><a href='$uri1' title='Obter RDF da pessoa'><img border='0' src='http://www.w3.org/RDF/icons/rdf_w3c_button.32' alt='RDF Resource Description Framework Icon' style='margin-right:40px;' /></a>";
        echo "<a href='$uri2' title='Obter RDF da imagem'><img border='0' src='http://www.w3.org/RDF/icons/rdf_w3c_button.32' alt='RDF Resource Description Framework Icon' /></a></p>";

    }

    public function gerarRDF($q, $saida)
    {    
        $mysql_database = ModelFactory::getDbStore();
        $list           = $mysql_database->listModels();
        unset($mmodel);
        foreach ($list as $model)
        {
            $dbModel = $mysql_database->getModel($model['modelURI']);
            $qual = explode("/",$model['modelURI']);
            //echo "<pre>";
            //print_r($qual);
            //echo "</pre>";
            if(in_array($q,$qual) && in_array($saida,$qual))
            {
                //echo $q." dentro do if ".$saida;
                //echo $qual[6]." - ".$k;
                $dbModel->saveAs($q.".rdf");
            }
        }
    }


}
?>