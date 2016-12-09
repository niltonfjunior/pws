<?php

class Publicacao extends Diversa{

    private $tarefaAtiva;
    private $acaoAtiva;
    private $classe        = 'Publicacao';
    private $tabela        = 'publicacao';
    private $pk            = 'idPub';      // CHAVE PRIMÁRIA PARA SELECTS
    private $cppe          = 'tituloPub';    // COLUNA PRINCIPAL PARA MÉTODO EXCLUIR
    private $meioUri       = 'publication/';
    private $estiloFormPub = "width: 200px; margin-right: 10px;";

    // CLASS PDF2TXT ----------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------
    // Some settings
	var $multibyte     = 4; // Use setUnicode(TRUE|FALSE)
	var $convertquotes = ENT_QUOTES; // ENT_COMPAT (double-quotes), ENT_QUOTES (Both), ENT_NOQUOTES (None)
	var $showprogress  = true; // TRUE if you have problems with time-out
	// Variables
	var $filename      = '';
	var $decodedtext   = '';
    // ------------------------------------------------------------------------------------------------------
    // CLASS PDF2TXT ----------------------------------------------------------------------------------------


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
            case "t5": // primeiro a sensibilidade, depois as palavras e agrupamentos
                $this->minerar($_GET['k']);
                break;
            case "t6": // recebe as escolhas do usuário
                $this->recebeEscolhas();
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
				    <th style="width: 40%;">Título</th>
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
                    echo "<td><a href='".$this->buscaUriBase().$this->meioUri.$tupla[0]."' target='_blank' />".$this->buscaUriBase().$this->meioUri.$tupla[0]."</a></td>";
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
                       <a class="btn btn-success" href="?a=<?php echo $this->acaoAtiva;?>&k=<?php echo $tupla[0];?>&t=t5">
	                       <i class="icon-search icon-white"></i>
    					   Analisar
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
            <input type="hidden" name="idRdfPub" value="<?php echo $tupla['idRdfPub'];?>" />
            <fieldset>
                <legend><?php echo $titulo;?></legend>
                 <div class="control-group">
					<label class="control-label" for="tipo" style="<?php echo $this->estiloFormPub;?>">Tipo:</label>
					<div class="controls">
					  <select id="tipo" name="tipo">
                      <?php
                        $retorno = $this->searchData('tipo');
                        while($r = mysql_fetch_array($retorno))
                        {
                            echo "<option value='$r[0]'>".strtoupper($r[1])."</option>";
                        }
                      ?>
					  </select>
					</div>
				</div>
                <div class="control-group">
                    <label class="control-label" for="tituloPub" style="<?php echo $this->estiloFormPub;?>">Título:</label>
                    <div class="controls">
                        <input autofocus="autofocus" required="required" type="text" name="tituloPub" style="width: 600px;" maxlength="200" class="span6" id="tituloPub" value="<?php echo $tupla['tituloPub'];?>" />
                    </div>
                </div>
                <div class="control-group">
					<label class="control-label" for="autores" style="<?php echo $this->estiloFormPub;?>">Autor(es):</label>
					<div class="controls">
					  <select id="autores" name="autores[]" multiple data-rel="chosen">
                      <?php
                        $retorno = $this->searchData('autor');
                        while($r = mysql_fetch_array($retorno))
                        {
                            $acha = mysql_query("SELECT * FROM pubautor WHERE idpubpa='$k' AND idautorpa='$r[0]'");
                            if(mysql_num_rows($acha))
                            {
                                echo "<option value='$r[0]' selected='selected'>".strtoupper($r[3]).", ".$r[1]." ".$r[2]."</option>";
                            }else{
                                echo "<option value='$r[0]'>".strtoupper($r[3]).", ".$r[1]." ".$r[2]."</option>";
                            }
                        }
                      ?>
					  </select>
					</div>
				</div>
                <div class="control-group">
                    <label class="control-label" for="resumoPub" style="<?php echo $this->estiloFormPub;?>">Resumo:</label>
                    <div class="controls">
                        <textarea id="resumoPub" required="required" name="resumoPub" rows="7" style="width: 65%;"><?php echo $tupla['resumoPub'];?></textarea>
                    </div>
                </div>

                <div class="control-group">
					<label class="control-label" for="keywordsCat" style="<?php echo $this->estiloFormPub;?>">Palavras-chave catalogadas:</label>
					<div class="controls">
					  <select id="keywordsCat" name="keywordsCat[]" multiple data-rel="chosen">
                      <?php
                        $retorno = $this->searchData('keywords');
                        while($r = mysql_fetch_array($retorno))
                        {
                            $acha = mysql_query("SELECT * FROM pubpalavra WHERE idpubpp='$k' AND idpalavrapp='$r[0]'");
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
                    <label class="control-label" for="arquivoPub" style="<?php echo $this->estiloFormPub;?>">Arquivo PDF:</label>
                    <div class="controls">
                        <input class="input-file uniform_on" id="arquivoPub" name="arquivoPub" type="file" />
                        <?php
                        $temPdf = false;
                        if(file_exists("pdf/".$tupla['idPub'].".pdf"))
                        {
                            echo "<a href='pdf/".$tupla['idPub'].".pdf' target='_blank'>Arquivo atualmente disponível</a>";
                        }
                    ?>

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
            $tituloPub   = $this->pegaPost('tituloPub');

            if(!$chave)
            {
                $retorno = $this->consultaSql("SELECT $this->pk FROM $this->tabela WHERE tituloPub='$tituloPub'");
                $quantos = mysql_num_rows($retorno);
                if($quantos)
                {
                    $this->alertaAtencao('ATENÇÃO! Esta publicação já está cadastrada');
                    //$k = mysql_fetch_assoc($retorno);
                    //$this->form($k['idTipo']);
                    exit;
                }
            }
            $idRdfPub    = $_POST['idRdfPub'];
            $tipoPub     = $_POST['tipo'];
            $resumoPub   = $this->pegaPost('resumoPub');
            $arquivoPub  = $_FILES['arquivoPub'];
            $keywordsCat = $_POST['keywordsCat'];
            $autores     = $_POST['autores'];

            $rTipo   = $this->consultaSql("SELECT idRdfTipo FROM tipo WHERE idTipo='$tipoPub'");
            $tTipo   = mysql_fetch_assoc($rTipo);
            $rUri    = $this->consultaSql("SELECT modelURI FROM models WHERE modelID='".$tTipo['idRdfTipo']."'");
            $uriTipo = mysql_fetch_assoc($rUri);

            if(!$chave)
            {
                mysql_query("INSERT INTO $this->tabela (tituloPub, resumoPub, idTipoPub) VALUES ('$tituloPub','$resumoPub','$tipoPub')");
                $retorno = $this->consultaSql("SELECT $this->pk FROM $this->tabela WHERE tituloPub='$tituloPub' ORDER BY $this->pk DESC LIMIT 1");
                $r       = mysql_fetch_assoc($retorno);
                $chave   = $r[$this->pk];
                $uriPub  = $this->buscaUriBase().$this->meioUri.$chave;

                move_uploaded_file($arquivoPub["tmp_name"], "pdf/".$chave.".pdf");
                $uriPdf  = $this->buscaUriBase()."pdf/".$chave;

                // gravar triplas

                unset($statement);               


                $statement[] = new Statement(new Resource($uriPub), RDF::TYPE() , DC::BIBLIOGRAPHIC_RESOURCE());
                $statement[] = new Statement(new Resource($uriPub), RDF::TYPE() , DC::TEXT());
                $statement[] = new Statement(new Resource($uriPub), DC::TYPE(), new Resource($uriTipo['modelURI']));
                $statement[] = new Statement(new Resource($uriPub), DC::TITLE(), new Literal($this->tiraAcento($tituloPub),'PT'));
                $statement[] = new Statement(new Resource($uriPub), DC::ABSTRACT_(), new Literal($this->tiraAcento($resumoPub),'PT'));

                for($i=0;$i<count($autores);$i++)
                {
                    mysql_query("INSERT INTO pubautor (idpubpa, idautorpa) VALUES ('$chave','$autores[$i]')");
                    $rAutor     = $this->consultaSql("SELECT idRdfAutor FROM autor WHERE idAutor='$autores[$i]'");
                    $tAutor     = mysql_fetch_assoc($rAutor);
                    $rStatement = $this->consultaSql("SELECT subject FROM statements WHERE modelID='".$tAutor['idRdfAutor']."' LIMIT 1");
                    $tStatement = mysql_fetch_assoc($rStatement);
                    $statement[] = new Statement(new Resource($uriPub), DC::CREATOR(), new Resource($tStatement['subject']));
                }

                for($i=0;$i<count($keywordsCat);$i++)
                {
                    mysql_query("INSERT INTO pubpalavra (idpubpp, idpalavrapp) VALUES ('$chave','$keywordsCat[$i]')");
                    $rPalavra    = $this->consultaSql("SELECT idRdfPalavra FROM palavra WHERE idPalavra='$keywordsCat[$i]'");
                    $tPalavra    = mysql_fetch_assoc($rPalavra);
                    $rStatement  = $this->consultaSql("SELECT subject FROM statements WHERE modelID='".$tPalavra['idRdfPalavra']."' LIMIT 1");
                    $tStatement  = mysql_fetch_assoc($rStatement);
                    $statement[] = new Statement(new Resource($uriPub), DC::SUBJECT(), new Resource($tStatement['subject']));
                }
                
                $statement[] = new Statement(new Resource($uriPub), DC::HAS_VERSION() , new Resource($uriPdf));

                $model = ModelFactory::getDefaultModel();
                for($i=0; $i<count($statement); $i++)
                {
                    $model->add($statement[$i]);
                }

                $mysql_database = ModelFactory::getDbStore();
                $mysql_database->putModel($model,$uriPub);

                // FIM DA GRAVAÇÃO DAS TRIPLAS

                // PRECISO PEGAR A ID DESSE MODEL NA TABELA PARA GRAVAR
                // VOU PROCURAR NA TABELA MODELS PELA URI

                $retorno = $this->consultaSql("SELECT modelID FROM models WHERE modelURI='$uriPub'");
                $qmodel  = mysql_fetch_assoc($retorno);
                mysql_query("UPDATE $this->tabela SET idRdfPub='".$qmodel['modelID']."' WHERE $this->pk='$chave'");

                // FEITO.

                // AGORA GRAVAR AS TRIPLAS ESPECIFICAS PARA O PDF

                unset($statement);
                //$statement[] = new Statement(new Resource($uriPdf), RDF::TYPE() , DC::MEDIA_TYPE());
                $statement[] = new Statement(new Resource($uriPdf), DC::FORMAT(), new Resource("http://www.iana.org/assignments/media-types/application/pdf"));
                $statement[] = new Statement(new Resource($uriPdf), DC::IS_VERSION_OF(), new Resource($uriPub));
                $model       = ModelFactory::getDefaultModel();
                for($i=0; $i<count($statement); $i++)
                {
                    $model->add($statement[$i]);
                }
                $mysql_database = ModelFactory::getDbStore();
                $mysql_database->putModel($model,$uriPdf);

                // FIM DA GRAÇÃO DAS TRIPLAS

            }else{

                mysql_query("DELETE FROM models WHERE modelID='$idRdfPub'");
                mysql_query("DELETE FROM statements WHERE modelID='$idRdfPub'");
                mysql_query("DELETE FROM pubautor WHERE idpubpa='$chave'");
                mysql_query("DELETE FROM pubpalavra WHERE idpubpp='$chave'");

                $uriPub  = $this->buscaUriBase().$this->meioUri.$chave;
                $uriPdf  = $this->buscaUriBase()."pdf/".$chave;
                if($arquivoPub['tmp_name'])
                {
                    move_uploaded_file($arquivoPub["tmp_name"], "pdf/".$chave.".pdf");
                }

                // gravar triplas

                unset($statement);

                $statement[] = new Statement(new Resource($uriPub), RDF::TYPE() , DC::BIBLIOGRAPHIC_RESOURCE());
                $statement[] = new Statement(new Resource($uriPub), RDF::TYPE() , DC::TEXT());
                $statement[] = new Statement(new Resource($uriPub), DC::TYPE(), new Resource($uriTipo['modelURI']));
                $statement[] = new Statement(new Resource($uriPub), DC::TITLE(), new Literal($this->tiraAcento($tituloPub),'PT'));
                $statement[] = new Statement(new Resource($uriPub), DC::ABSTRACT_(), new Literal($this->tiraAcento($resumoPub),'PT'));

                for($i=0;$i<count($autores);$i++)
                {
                    mysql_query("INSERT INTO pubautor (idpubpa, idautorpa) VALUES ('$chave','$autores[$i]')");
                    $rAutor      = $this->consultaSql("SELECT idRdfAutor FROM autor WHERE idAutor='$autores[$i]'");
                    $tAutor      = mysql_fetch_assoc($rAutor);
                    $rStatement  = $this->consultaSql("SELECT subject FROM statements WHERE modelID='".$tAutor['idRdfAutor']."' LIMIT 1");
                    $tStatement  = mysql_fetch_assoc($rStatement);
                    $statement[] = new Statement(new Resource($uriPub), DC::CREATOR(), new Resource($tStatement['subject']));
                }

                for($i=0;$i<count($keywordsCat);$i++)
                {
                    mysql_query("INSERT INTO pubpalavra (idpubpp, idpalavrapp) VALUES ('$chave','$keywordsCat[$i]')");
                    $rPalavra    = $this->consultaSql("SELECT idRdfPalavra FROM palavra WHERE idPalavra='$keywordsCat[$i]'");
                    $tPalavra    = mysql_fetch_assoc($rPalavra);
                    $rStatement  = $this->consultaSql("SELECT subject FROM statements WHERE modelID='".$tPalavra['idRdfPalavra']."' LIMIT 1");
                    $tStatement  = mysql_fetch_assoc($rStatement);
                    $statement[] = new Statement(new Resource($uriPub), DC::SUBJECT(), new Resource($tStatement['subject']));
                }
                
                $statement[] = new Statement(new Resource($uriPub), DC::HAS_VERSION() , new Resource($uriPdf));
                
                $model = ModelFactory::getDefaultModel();
                for($i=0; $i<count($statement); $i++)
                {
                    $model->add($statement[$i]);
                }
                $mysql_database = ModelFactory::getDbStore();
                $mysql_database->putModel($model,$uriPub);

                // FIM DA GRAÇÃO DAS TRIPLAS
                // PRECISO PEGAR A ID DESSE MODEL NA TABELA PARA GRAVAR
                // VOU PROCURAR NA TABELA MODELS PELA URI

                $retorno = $this->consultaSql("SELECT modelID FROM models WHERE modelURI='$uriPub'");
                $qmodel  = mysql_fetch_assoc($retorno);
                mysql_query("UPDATE $this->tabela SET tituloPub='$tituloPub', resumoPub='$resumoPub', idTipoPub='$tipoPub', idRdfPub='".$qmodel['modelID']."' WHERE $this->pk='$chave'");

                // FEITO.

                // FIM DA GRAÇÃO DAS TRIPLAS
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
            $chave = $_POST['chave'];
            mysql_query("DELETE FROM $this->tabela WHERE $this->pk='$chave'");
            $this->gravaLog($this->classe, $this->tarefaAtiva, $chave);
            $this->alertaSucesso('REGISTRO EXCLUÍDO');
        }
        $this->lista();
    }

    private function minerar($k)
    {
        if(!isset($_POST['frequencia']))
        {
    ?>
            <form name="sensibilidade" class="form-horizontal" method="post" action="?a=<?php echo $this->acaoAtiva;?>&t=t5">
                <!-- A VARIÁVEL CHAVE É RESPONSÁVEL POR IDENTIFICAR UM NOVO REGISTRO OU UMA ALTERAÇÃO -->
                <input type="hidden" name="chave" value="<?php echo $k;?>"/>
                <!-- -------------------------------------------------------------------------------- -->
                <fieldset>
                    <legend>Definir frequência de palavras no texto</legend>
                    <div class="control-group">
                        <label class="control-label" for="frequencia">Frequência de palavras:</label>
                        <div class="controls">
                            <input autofocus="autofocus" required="required" type="text" name="frequencia" style="width: 50px;" maxlength="2" class="span6" id="frequencia" value="10" />
                        </div>
                    </div>
                    <legend style="margin-top: 40px;">Máximo de palavras agrupadas no resumo</legend>
                    <div class="control-group">
				        <label class="control-label" for="nPalavras">Palavras agrupadas</label>
				        <div class="controls">
                            <select name="nPalavras" id="nPalavras">
				                <option value="2" selected="selected">2 palavras</option>									
								<option value="3">3 palavras</option>
								<option value="4">4 palavras</option>
								<option value="5">5 palavras</option>
		                    </select>
				        </div>
		            </div>
                    <div class="form-actions">
                        <button type="button" class="btn" onclick="mudaPagina('?a=<?php echo $this->acaoAtiva;?>')"> Cancelar </button>
                        &nbsp;&nbsp;&nbsp;&nbsp;
    				    <button type="button" class="btn btn-primary" onclick="submit()"> Processar </button>
                    </div>
                </fieldset>
            </form>
<?php
        }else{

            $nPalavras  = $_POST['nPalavras'];            
            $frequencia = $_POST['frequencia'];
            $chave      = $_POST['chave'];

            // A IDEIA AGORA É FAZER AS FREQUENCIAS PRIMEIRO
            // SERÁ UM ÚNICO FORMULÁRIO PARA ESCOLHA PRIMEIRO DA FREQUENCIA DEPOIS DAS PALAVRAS DO RESUMO
            ?>

            <form name="palavras" class="form-horizontal" method="post" action="?a=<?php echo $this->acaoAtiva;?>&t=t6">
                <input type="hidden" name="chave" value="<?php echo $chave;?>" />
                <h3>Frequências (mínimo de <?php echo $frequencia;?> repetições)</h3>

            <?php
            // PEGA O PDF E TRANSFORMA EM TXT
            $this->setFilename("pdf/$chave.pdf");
            $this->decodePDF();

            // RETIRA CARACTERES E VETORIZA O TEXTO
            $texto = strtolower($this->output());
            $texto = ereg_replace("[.,;:!?*)(}{]","",$texto);
            $texto = explode(' ',$texto);

            // DUPLICA O VETOR PARA REMOVER AS STOPWORDS
            $palavras  = $texto;
            $termosPdf = $texto;
            $retorno   = $this->consultaSql("SELECT palavra FROM stopwords");
            $temStop   = false; // A CHANCE DE NÃO ACHAR NENHUMA É MUITO PEQUENA, PODE SER O CASO DE NÃO CONSEGUIR LER O ARQUIVO
            while($palavra = mysql_fetch_array($retorno))
            {
                $indice = array_keys($palavras,$palavra[0]);
                for($i=0;$i<count($indice);$i++)
                {
                    unset($texto[$indice[$i]]);
                    $temStop = true;
                }
            }

            if($temStop) // LEU O ARQUIVO PDF
            {
                unset($palavras);
                for($i=0;$i<count($texto);$i++)
                {
               	    if(strlen($texto[$i]))
                    {
                        $palavras[] = trim($texto[$i]); // GERANDO UM NOVO VETOR SEM LIXOS
                    }
                }
                sort($palavras);
                $quantas = 0;
                $compara = $palavras[0];
                unset($guardaQuantas);
                for($i=1;$i<count($palavras);$i++) // AGORA QUE ESTÁ ORDENADO, FAZ A CONTAGEM DAS REPETIÇÕES
                {
                    if($palavras[$i]==$compara)
                    {
                        $quantas++;
                    }else{
                        if($quantas>=$frequencia && strlen($compara)>3)
                        {
                            $pFrequencia[]           = $compara;
                            $guardaQuantas[$compara] = $quantas;
                        }
                        $compara = $palavras[$i];
                        $quantas = 1;
                    }
                }
             ?>
             <table class="table">
                <tbody>
                    <tr>
                    <?php
                    for($i=0;$i<count($pFrequencia);$i++)
                    {
                        $retorno = $this->consultaSql("SELECT * FROM palavra WHERE textoPalavra='$pFrequencia[$i]'"); // SE ENCONTRAR ALGUMA PALAVRA-CHAVE, IGNORA A FREQUENCIA
                        $jatem   = mysql_num_rows($retorno);
                        if(!$jatem)
                        {
                            echo "<td><input type='checkbox' name='escolha[]' value='$pFrequencia[$i]' />".$pFrequencia[$i]." [".$guardaQuantas[$pFrequencia[$i]]." ]</td>";
                            $salto++;
                        }
                        if($salto>5)
                        {
                            echo "</tr><tr>";
                            $salto = 0;
                        }
                    }
                    ?>
                    </tr>
                </tbody>
            </table>
            <?php
            }else{
                echo $this->alertaAtencao("NÃO FOI POSSÍVEL EFETUAR A LEITURA NO ARQUIVO PDF.");
            }

            $retorno    = $this->consultaSql("SELECT resumoPub FROM publicacao WHERE idPub='$chave'");
            $resumo     = mysql_fetch_assoc($retorno);
            // PRIMEIRO, RETIRA CARACTERES ORTOGRÁFICOS
            $resumo     = ereg_replace("[.,;:!?*)(}{]","",$resumo['resumoPub']);
            // -----------------------------------------
            $resumo     = explode(" ",strtolower($resumo));
            $resumosw   = $resumo;
            // -----------------------------------------
            // RETIRANDO STOPWORDS DE UM DOS ARRAYS
            $retorno    = $this->consultaSql("SELECT palavra FROM stopwords");
            while($palavra = mysql_fetch_array($retorno))
            {
                $indice = array_keys($resumo,$palavra[0]);
                for($i=0;$i<count($indice);$i++)
                {
                    unset($resumosw[$indice[$i]]);
                }
            }
            // RETIRA AS REPETIÇÕES DO MESMO ARRAY
            if($temStop)
            {
                $resumoaux = array_merge($resumosw, $pFrequencia); // SE JÁ APARECEU NA FREQUÊNCIA, NÃO PRECISA APARECER AQUI
            
                for($i=0;$i<count($resumoaux); $i++)
                {
                    $indice = array_keys($resumosw,$resumoaux[$i]);
                    for($j=1;$j<count($indice);$j++)
                    {
                        unset($resumosw[$indice[$j]]);
                    }
                }
            }
            ?>

            <h3>SUGESTÕES DO RESUMO</h3>
            <table class="table">
                <tbody>
				    <tr>
                    <?php
                    $salto = 0;
                    for($i=0;$i<count($resumosw);$i++)
                    {
                        $palavra = trim($resumosw[$i]);
            			$jatem   = 1;
            			if($palavra != '')
            			{
            				$query   = "SELECT * FROM palavra WHERE textoPalavra='$palavra'"; // SE JÁ TEM NAS PALAVRAS-CHAVE, ENTÃO NÃO PRECISA MOSTRAR
            				$retorno = $this->consultaSql($query);
            				$jatem   = mysql_num_rows($retorno);
            			}
                        if(!$jatem)
                        {
                            echo "<td class='center'><input type='checkbox' name='escolha[]' value='$palavra' />".$palavra."</td>";
                            $salto++;
                        }
                        if($salto>5)
                        {
                            echo "</tr><tr>";
                            $salto = 0;
                        }
                    }

                    ?>

                    </tr>
                </tbody>
            </table>

                <?php
                /* -- NÃO VOU USAR MAIS OS AGRUPAMENTOS, FICOU MUITO CANSATIVO --*/
                // MUDEI DE IDEIA
                
                $limite = $nPalavras-1;
                $salto  = 0;


                ?>
                <h3>AGRUPAMENTOS DO RESUMO</h3>
                <table class="table">
                    <tbody>
					   <tr>
                       <?php

                        for($i=0;$i<(count($resumo)-$limite);$i++)
                        {
                            unset($agrupamento);
                            for($j=$i;$j<($nPalavras+$i);$j++)
                            {
                                $agrupamento .= $resumo[$j]." ";
                            }
                            $agrupamento = trim($agrupamento);
                            $retorno     = $this->consultaSql("SELECT * FROM palavra WHERE textoPalavra='$agrupamento'");
                            $jatem       = mysql_num_rows($retorno);
                            if(!$jatem && strlen($agrupamento)>5)
                            {
                                echo "<td><input type='checkbox' name='escolha[]' value='$agrupamento' />".$agrupamento."</td>";
                                $salto++;
                            }
                            if($salto>(5-$nPalavras))
                            {
                                echo "</tr><tr>";
                                $salto = 0;
                            }
                        }
                        ?>
                        </tr>
                    </tbody>
                 </table>

        <?php
        /**/
        ?>

             <div class="form-actions">
                <button type="button" class="btn" onclick="mudaPagina('?a=<?php echo $this->acaoAtiva;?>')"> Cancelar </button>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <button type="button" class="btn btn-primary" onclick="submit()"> Guardar selecionadas </button>
             </div>
         </form>

        <?php


        }
    }

    private function recebeEscolhas()
    {
        $chave        = $_POST['chave'];        
        foreach($_POST['escolha'] as $i)
        {
            $escolhas[] = strtolower($this->tiraAcento(addslashes(trim($i))));
        }
        $retorno   = $this->consultaSql("SELECT idRdfPub FROM publicacao WHERE idPub='$chave'");
        $idRdf     = mysql_fetch_assoc($retorno);        
        $uriPub    = $this->buscaUriBase().$this->meioUri.$chave;
        $Concept   = new Resource("http://www.w3.org/2004/02/skos/core#Concept");
        $prefLabel = new Resource("http://www.w3.org/2004/02/skos/core#prefLabel");
        
        for($i=0;$i<count($escolhas);$i++)
        {
            // IF VIOLENTO ----------
            print "<p><li>".$escolhas[$i]."</li></p>";
            mysql_query("INSERT INTO palavra (textoPalavra, definicaoPalavra) VALUES ('$escolhas[$i]','__')");
            $retorno      = $this->consultaSql("SELECT idPalavra FROM palavra WHERE textoPalavra='$escolhas[$i]'");
            $esteRegistro = mysql_fetch_assoc($retorno);
            $uriPalavra   = $this->buscaUriBase()."keyword/".$this->poeUnderline(strtolower($escolhas[$i]));
            unset($statement);
            $statement[]  = new Statement(new Resource($uriPalavra), RDF::TYPE(), $Concept);
            $statement[]  = new Statement(new Resource($uriPalavra), $prefLabel, new Literal(strtolower($escolhas[$i]),'PT'));
            $model        = ModelFactory::getDefaultModel();
            for($ii=0; $ii<count($statement); $ii++)
            {
                $model->add($statement[$ii]);
            }
            $mysql_database = ModelFactory::getDbStore();
            $mysql_database->putModel($model,$uriPalavra);
            $retorno = $this->consultaSql("SELECT modelID, modelURI FROM models WHERE modelURI='$uriPalavra'");
            $qmodel  = mysql_fetch_assoc($retorno);
            mysql_query("UPDATE palavra SET idRdfPalavra='".$qmodel['modelID']."' WHERE idPalavra='".$esteRegistro['idPalavra']."'");
            mysql_query("INSERT INTO pubpalavra (idpubpp, idpalavrapp) VALUES ('$chave','".$esteRegistro['idPalavra']."')");
            // -----------------------------
            mysql_query("INSERT INTO statements (modelID, subject, predicate, object, subject_is, object_is) VALUES ('".$idRdf['idRdfPub']."','$uriPub','http://purl.org/dc/elements/1.1/subject','$uriPalavra','r','r')");
        }
        

        $this->alertaSucesso("GRAVAÇÃO REALIZADA COM SUCESSO");
    }

    public function pesquisar()
    {
        if(!isset($_POST['ppesquisa']))
        {
    ?>
            <form name="pesquisar" class="form-horizontal" method="post" action="?a=<?php echo $this->acaoAtiva;?>">
                <fieldset>
                    <div class="control-group">
					<label class="control-label" for="ppesquisa">Palavras-chave:</label>
					<div class="controls">
					  <select id="ppesquisa" name="ppesquisa[]" multiple data-rel="chosen">
                      <?php
                        $retorno = $this->searchData('keywords');
                        while($r = mysql_fetch_array($retorno))
                        {
                            echo "<option value='$r[0]'>".$r[1]."</option>";
                        }
                      ?>
					  </select>
					</div>
				</div>
                    <div class="form-actions">
                        <button type="button" class="btn" onclick="mudaPagina('?a=<?php echo $this->acaoAtiva;?>')"> Cancelar </button>
                        &nbsp;&nbsp;&nbsp;&nbsp;
    				    <button type="button" class="btn btn-primary" onclick="submit()"> Pesquisar </button>
                    </div>
                </fieldset>
            </form>

    <?php
        }else{
            $ppesquisa   = $_POST['ppesquisa'];
            $peloMenosUm = false;
            for($i=0;$i<count($ppesquisa); $i++)
            {
                $query  = "SELECT p.idPub, p.tituloPub, p.resumoPub, pal.textoPalavra, t.nomeTipo ";
                $query .= "FROM publicacao p, palavra pal, pubpalavra pp, tipo t ";
                $query .= "WHERE pp.idpalavrapp='$ppesquisa[$i]' AND pal.idPalavra=pp.idpalavrapp AND p.idPub=pp.idpubpp AND p.idTipoPub=t.idTipo";
                //echo $query;
                $retorno = mysql_query($query);
                while($pesquisa = mysql_fetch_array($retorno))
                {
                    $peloMenosUm = true;
                    $this->alertaInfo("Resultado para ".$pesquisa[3]);
                    echo "<h3>".$pesquisa[1]." [ ".$pesquisa[4]." ]</h3><br/>";
                    //$retornoAutor = mysql_query("SELECT a.preNomeAutor, a.meioNomeAutor, a.sobreNomeAutor FROM autor a, pubautor pa WHERE pa.idpubpa='$pesquisa[0]' AND pa.idautorpa=a.idAutor");
                    //while($autores = mysql_fetch_array($retornoAutor))
                    //{
                    //    echo strtoupper($autores[2]).", ".$autores[0]." ".$autores[1]."<br/>";
                    //}
                    //echo "<br/><p style='text-align:justify';>".$pesquisa[2]."</p><br/>";
                    echo "<p>VEJA MAIS INFORMAÇÕES EM: <a href='".$this->buscaUriBase().$this->meioUri.$pesquisa[0]."' target='_blank'>";
                    echo "&lt;".$this->buscaUriBase().$this->meioUri.$pesquisa[0]."&gt;</a><hr/></p>";
                }
            }
            if(!$peloMenosUm)
            {
                $this->alertaAtencao("NÃO FORAM ENCONTRAS PUBLICAÇÕES RELACIONADAS");
            }
        }
    }
    
    public function pesquisaSemantica()
    {
        if(!isset($_POST['termoPesquisa']))
        {
    ?>
            <form name="pesquisaSemantica" class="form-horizontal" method="post" action="?a=<?php echo $this->acaoAtiva;?>">
                <fieldset>
                    <div class="control-group">
    					<label class="control-label" for="termoPesquisa" style="<?php echo $this->estiloFormPub;?>">Termo para pesquisa:</label>
                        <div class="controls">
                            <input autofocus="autofocus" required="required" type="text" name="termoPesquisa" class="span6" id="termoPesquisa"/>
                        </div>
                    </div>
                    <?php
                        $this->alertaInfo("OFEREÇA MAIS OPÇÕES PARA ENRIQUECER A PESQUISA:")
                    ?>
                    <div class="control-group">
    					<label class="control-label" for="termoAmplo" style="<?php echo $this->estiloFormPub;?>">Conceito mais amplo:</label>
                        <div class="controls">
                            <input autofocus="autofocus" type="text" name="termoAmplo" class="span6" id="termoAmplo"/>
                        </div>
                    </div>
                    <!--
                    <div class="control-group">
    					<label class="control-label" for="termoEspecifico" style="<?php echo $this->estiloFormPub;?>">Conceito mais específico:</label>
                        <div class="controls">
                            <input autofocus="autofocus" type="text" name="termoEspecifico" class="span6" id="termoEspecifico"/>
                        </div>
                    </div>
                    -->
                    <div class="control-group">
    					<label class="control-label" for="termoAlternativo" style="<?php echo $this->estiloFormPub;?>">Conceito alternativo:</label>
                        <div class="controls">
                            <input autofocus="autofocus" type="text" name="termoAlternativo" class="span6" id="termoAlternativo"/>
                        </div>
                    </div>
                    <div class="control-group">
    					<label class="control-label" for="termoRelacionado" style="<?php echo $this->estiloFormPub;?>">Conceito relacionado:</label>
                        <div class="controls">
                            <input autofocus="autofocus" type="text" name="termoRelacionado" class="span6" id="termoRelacionado"/>
                        </div>
                    </div>			
                    <div class="form-actions">
                        <button type="button" class="btn" onclick="mudaPagina('?a=<?php echo $this->acaoAtiva;?>')"> Cancelar </button>
                        &nbsp;&nbsp;&nbsp;&nbsp;
    				    <button type="button" class="btn btn-primary" onclick="verificarPesquisa()"> Pesquisar </button>
                    </div>
                </fieldset>
            </form>

    <?php
        }else{
            $termoPesquisa    = $this->pegaPost('termoPesquisa');     // prefLabel
            $termoAmplo       = $this->pegaPost('termoAmplo');        // broader
            //$termoEspecifico  = $this->pegaPost('termoEspecifico');   // narrower
            $termoAlternativo = $this->pegaPost('termoAlternativo');  // altLabel
            $termoRelacionado = $this->pegaPost('termoRelacionado');  // related
            
            $prefix = "http://www.w3.org/2004/02/skos/core#"; 
            $query  = "
                PREFIX skos: <$prefix>
                
                SELECT ?r WHERE 
                {
                    ?r skos:prefLabel ?termo.\n";
            
            $filtro = "FILTER (regex(str(?termo),'$termoPesquisa')\n";
                    
            if($termoAmplo!="")
            {
                $query  .= "?r skos:broader ?amplo.\n";
                $filtro .= "|| regex(str(?amplo),'$termoAmplo')\n";
                
            }
            if($termoEspecifico!="")
            {
                $query  .= "?r skos:narrower ?especifico.\n";
                $filtro .= "|| regex(str(?especifico),'$termoEspecifico')\n";
            }
            if($termoAlternativo!="")
            {
                $query  .= "?r skos:altLabel ?alternativo.\n";
                $filtro .= "|| regex(str(?alternativo),'$termoAlternativo')\n";
            }
            if($termoRelacionado!="")
            {
                $query  .= "?r skos:related ?relacionado.\n";
                $filtro .= "|| regex(str(?relacionado),'$termoRelacionado')\n";
            }
            
            $filtro .= ")";
            $query  .= $filtro."}";
            
            //var_dump($query);
            $this->skosExpSparql($query);          
            
        }
        
    }

    public function apresentarPublicacao($qual)
    {
        $query  = "SELECT p.idPub, p.tituloPub, p.resumoPub, t.nomeTipo ";
        $query .= "FROM publicacao p, tipo t ";
        $query .= "WHERE p.idPub='$qual' AND p.idTipoPub=t.idTipo";

        $retorno = $this->consultaSql($query);
        $quantos = mysql_num_rows($retorno);
        if(!$quantos)
        {
            header("Location: ../");
        }
        $tupla = mysql_fetch_assoc($retorno);

        echo "<h2>".strtoupper($tupla['tituloPub'])."</h2>";
        echo "<h4>[ ".$tupla['nomeTipo']." ]</h4><br/>";
        echo "<p>&lt;".$this->buscaUriBase().$this->meioUri.$qual."&gt;</p>";
        echo "<p><b>RESUMO:</b></p>";
        echo "<p style='text-align: justify;'>".$tupla['resumoPub']."</p>";
        echo "<p><b>PALAVRAS-CHAVE:</b></p>";
        
        // BUSCANDO PALAVRAS-CHAVE DA PUBLICAÇÃO
        echo "<ul><li>";
        unset($mashupId); // id para buscar model RDF
        unset($mashupKw); // evita precisar do RDFS#ABOUT
        $retorno = $this->searchData('keywords');
        while($r = mysql_fetch_array($retorno))
        {
            $acha = mysql_query("SELECT * FROM pubpalavra WHERE idpubpp='$qual' AND idpalavrapp='$r[0]'");
            if(mysql_num_rows($acha))
            {
                if($mashupId){echo ",&nbsp;&nbsp;";}
                echo "<a href='".$this->buscaUriBase()."keyword/".$this->poeUnderline($r[1])."' target='_blank' />".$r[1]."</a>";
                $mashupKw[] = $r[1];
                $mashupId[] = $r[3];
            }
        }
        echo "</li></ul><br/>";
        
        // -------------------------------------

        for($i=0;$i<count($mashupId);$i++)
        {
            $query ="SELECT object FROM statements WHERE modelID='".$mashupId[$i]."' AND predicate='http://www.w3.org/2000/01/rdf-schema#seeAlso' ORDER BY modelID";
            
            
            $rStatements = $this->consultaSql($query);
            
            if(mysql_num_rows($rStatements))
            {
                
                echo "<div class='alert alert-info' style='height:150px;background:#ffffff;'>";
                echo "<h4>Conteúdo relacionado a ".strtoupper($mashupKw[$i])."</h4>";
                $uriMashup   = mysql_fetch_assoc($rStatements);
                $uriConsulta = $uriMashup['object'];
                
                // CONSULTA SPARQL
                require_once("../sparqllib/sparqllib.php");
                $db = sparql_connect("http://dbpedia.org/sparql"); //conectando no sparql endpoint da dbpedia    
                //testando se a conexão foi bem sucedida
            	if(!$db)
                {
            		$i = count($mashupId);
            	}else{
            	   
            	   $sparql = "
                    PREFIX foaf:<http://xmlns.com/foaf/0.1/>                    
                    SELECT ?imagem 
                    WHERE
                    {          
                        <$uriConsulta> foaf:depiction ?imagem 
                        
                    }";               
                   //execultando a consulta
        	       $result = sparql_query($sparql);
                   
                   if($result)
               	   {               	       
                        $fields = sparql_field_array($result);
                       
            	        while($row = sparql_fetch_array($result))
            	        {    
           		           foreach($fields as $field)
                           {
           			           echo "<img src='".utf8_decode($row[$field])."' alt='".strtoupper($mashupKw[$i])."' style='float:left; margin-right:10px;height:120px;'/>";
                                               
         		           }
            	        }
                        
                   }
                   
            	   $sparql = "
                    PREFIX rdfs:<http://www.w3.org/2000/01/rdf-schema#>                    
                    SELECT ?b 
                    WHERE
                    {          
                        <$uriConsulta> rdfs:label ?a .
                        <$uriConsulta> rdfs:comment ?b .                       
                        FILTER(langMatches(lang(?a), 'PT') && langMatches(lang(?b), 'PT'))
                    }";               
               
                   //execultando a consulta
        	       $result = sparql_query($sparql);
                   if($result)
               	   {               	       
                        $fields = sparql_field_array($result);
                       
            	        while($row = sparql_fetch_array($result))
            	        {    
           		           foreach($fields as $field)
                           {
           			           echo "<p style='text-align:justify;margin-top:10px;'>".utf8_decode($row[$field])."</p>";                        
         		           }
            	        }                       
                   }
                   
                   
                   echo "<p>Veja mais informações em: ";
                   echo "<a href='$uriConsulta' target='_blank'>$uriConsulta</a></p>";
                   
                   
                }
                
                
                // -- FIM DA CONSULTA SPARQL
                
                echo "</div>";
                                                 
            }            
        }
        
        
        echo "<hr/><p style='margin-left:10px;'>";
        $uri1 = $_SERVER['REQUEST_URI'];
	    $uri1 = str_replace("/page/","/data/",$uri1);
        $uri2 = str_replace("/publication/","/pdf/",$uri1);

        echo "<a href='$uri1' title='Obter RDF da publicação' style='margin-right:40px;'><img border='0' src='http://www.w3.org/RDF/icons/rdf_w3c_button.32' alt='RDF Resource Description Framework Icon' /></a>";
        echo "<a href='../../pdf/$qual.pdf' title='Obter arquivo PDF da publicação' target='_blank' style='margin-right:40px;'><img border='0' src='../../img/Pdf-256.png' alt='Arquivo PDF' /></a>";
        echo "<a href='$uri2' title='Obter RDF do arquivo PDF'><img border='0' src='http://www.w3.org/RDF/icons/rdf_w3c_button.32' alt='RDF Resource Description Framework Icon' /></a></p>";
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

            if(in_array($q,$qual) && in_array($saida,$qual))
            {
                //echo $qual[6]." - ".$k;
                $dbModel->saveAs($q.".rdf");
            }
        }
    }




// CLASS PDF2TXT ----------------------------------------------------------------------------------------------------------------

function setFilename($filename) {
		// Reset
		$this->decodedtext = '';
		$this->filename = $filename;
	}

	function output($echo = false) {
		if($echo) echo $this->decodedtext;
		else return $this->decodedtext;
	}

	function setUnicode($input) {
		// 4 for unicode. But 2 should work in most cases just fine
		if($input == true) $this->multibyte = 4;
		else $this->multibyte = 2;
	}

	function decodePDF() {
		// Read the data from pdf file
        
		$infile = @file_get_contents($this->filename, FILE_BINARY);
		if (empty($infile))
			return "";

		// Get all text data.
		$transformations = array();
		$texts = array();

		// Get the list of all objects.
		preg_match_all("#obj[\n|\r](.*)endobj[\n|\r]#ismU", $infile . "endobj\r", $objects);
		$objects = @$objects[1];

		// Select objects with streams.
		for ($i = 0; $i < count($objects); $i++) {
			$currentObject = $objects[$i];

			// Prevent time-out
			@set_time_limit ();
			if($this->showprogress) {
//				echo ". ";
				flush(); ob_flush();
			}

			// Check if an object includes data stream.
			if (preg_match("#stream[\n|\r](.*)endstream[\n|\r]#ismU", $currentObject . "endstream\r", $stream )) {
				$stream = ltrim($stream[1]);
				// Check object parameters and look for text data.
				$options = $this->getObjectOptions($currentObject);

				if (!(empty($options["Length1"]) && empty($options["Type"]) && empty($options["Subtype"])) )
//				if ( $options["Image"] && $options["Subtype"] )
//				if (!(empty($options["Length1"]) &&  empty($options["Subtype"])) )
					continue;

				// Hack, length doesnt always seem to be correct
				unset($options["Length"]);

				// So, we have text data. Decode it.
				$data = $this->getDecodedStream($stream, $options);

				if (strlen($data)) {
	                if (preg_match_all("#BT[\n|\r](.*)ET[\n|\r]#ismU", $data . "ET\r", $textContainers)) {
						$textContainers = @$textContainers[1];
						$this->getDirtyTexts($texts, $textContainers);
					} else
						$this->getCharTransformations($transformations, $data);
				}
			}
		}

		// Analyze text blocks taking into account character transformations and return results.
		$this->decodedtext = $this->getTextUsingTransformations($texts, $transformations);
	}


	function decodeAsciiHex($input) {
		$output = "";

		$isOdd = true;
		$isComment = false;

		for($i = 0, $codeHigh = -1; $i < strlen($input) && $input[$i] != '>'; $i++) {
			$c = $input[$i];

			if($isComment) {
				if ($c == '\r' || $c == '\n')
					$isComment = false;
				continue;
			}

			switch($c) {
				case '\0': case '\t': case '\r': case '\f': case '\n': case ' ': break;
				case '%':
					$isComment = true;
				break;

				default:
					$code = hexdec($c);
					if($code === 0 && $c != '0')
						return "";

					if($isOdd)
						$codeHigh = $code;
					else
						$output .= chr($codeHigh * 16 + $code);

					$isOdd = !$isOdd;
				break;
			}
		}

		if($input[$i] != '>')
			return "";

		if($isOdd)
			$output .= chr($codeHigh * 16);

		return $output;
	}

	function decodeAscii85($input) {
		$output = "";

		$isComment = false;
		$ords = array();

		for($i = 0, $state = 0; $i < strlen($input) && $input[$i] != '~'; $i++) {
			$c = $input[$i];

			if($isComment) {
				if ($c == '\r' || $c == '\n')
					$isComment = false;
				continue;
			}

			if ($c == '\0' || $c == '\t' || $c == '\r' || $c == '\f' || $c == '\n' || $c == ' ')
				continue;
			if ($c == '%') {
				$isComment = true;
				continue;
			}
			if ($c == 'z' && $state === 0) {
				$output .= str_repeat(chr(0), 4);
				continue;
			}
			if ($c < '!' || $c > 'u')
				return "";

			$code = ord($input[$i]) & 0xff;
			$ords[$state++] = $code - ord('!');

			if ($state == 5) {
				$state = 0;
				for ($sum = 0, $j = 0; $j < 5; $j++)
					$sum = $sum * 85 + $ords[$j];
				for ($j = 3; $j >= 0; $j--)
					$output .= chr($sum >> ($j * 8));
			}
		}
		if ($state === 1)
			return "";
		elseif ($state > 1) {
			for ($i = 0, $sum = 0; $i < $state; $i++)
				$sum += ($ords[$i] + ($i == $state - 1)) * pow(85, 4 - $i);
			for ($i = 0; $i < $state - 1; $i++) {
				try {
					if(false == ($o = chr($sum >> ((3 - $i) * 8)))) {
						throw new Exception('Error');
					}
					$output .= $o;
				} catch (Exception $e) { /*Dont do anything*/ }
			}
		}

		return $output;
	}

	function decodeFlate($data) {
		return @gzuncompress($data);
	}

	function getObjectOptions($object) {
		$options = array();

		if (preg_match("#<<(.*)>>#ismU", $object, $options)) {
			$options = explode("/", $options[1]);
			@array_shift($options);

			$o = array();
			for ($j = 0; $j < @count($options); $j++) {
				$options[$j] = preg_replace("#\s+#", " ", trim($options[$j]));
				if (strpos($options[$j], " ") !== false) {
					$parts = explode(" ", $options[$j]);
					$o[$parts[0]] = $parts[1];
				} else
					$o[$options[$j]] = true;
			}
			$options = $o;
			unset($o);
		}

		return $options;
	}

	function getDecodedStream($stream, $options) {
		$data = "";
		if (empty($options["Filter"]))
			$data = $stream;
		else {
			$length = !empty($options["Length"]) ? $options["Length"] : strlen($stream);
			$_stream = substr($stream, 0, $length);

			foreach ($options as $key => $value) {
				if ($key == "ASCIIHexDecode")
					$_stream = $this->decodeAsciiHex($_stream);
				elseif ($key == "ASCII85Decode")
					$_stream = $this->decodeAscii85($_stream);
				elseif ($key == "FlateDecode")
					$_stream = $this->decodeFlate($_stream);
				elseif ($key == "Crypt") { // TO DO
				}
			}
			$data = $_stream;
		}
		return $data;
	}

	function getDirtyTexts(&$texts, $textContainers) {
		for ($j = 0; $j < count($textContainers); $j++) {
			if (preg_match_all("#\[(.*)\]\s*TJ[\n|\r]#ismU", $textContainers[$j], $parts))
				$texts = array_merge($texts, array(@implode('', $parts[1])));
			elseif (preg_match_all("#T[d|w|m|f]\s*(\(.*\))\s*Tj[\n|\r]#ismU", $textContainers[$j], $parts))
				$texts = array_merge($texts, array(@implode('', $parts[1])));
			elseif (preg_match_all("#T[d|w|m|f]\s*(\[.*\])\s*Tj[\n|\r]#ismU", $textContainers[$j], $parts))
				$texts = array_merge($texts, array(@implode('', $parts[1])));
		}

	}

	function getCharTransformations(&$transformations, $stream) {
		preg_match_all("#([0-9]+)\s+beginbfchar(.*)endbfchar#ismU", $stream, $chars, PREG_SET_ORDER);
		preg_match_all("#([0-9]+)\s+beginbfrange(.*)endbfrange#ismU", $stream, $ranges, PREG_SET_ORDER);

		for ($j = 0; $j < count($chars); $j++) {
			$count = $chars[$j][1];
			$current = explode("\n", trim($chars[$j][2]));
			for ($k = 0; $k < $count && $k < count($current); $k++) {
				if (preg_match("#<([0-9a-f]{2,4})>\s+<([0-9a-f]{4,512})>#is", trim($current[$k]), $map))
					$transformations[str_pad($map[1], 4, "0")] = $map[2];
			}
		}
		for ($j = 0; $j < count($ranges); $j++) {
			$count = $ranges[$j][1];
			$current = explode("\n", trim($ranges[$j][2]));
			for ($k = 0; $k < $count && $k < count($current); $k++) {
				if (preg_match("#<([0-9a-f]{4})>\s+<([0-9a-f]{4})>\s+<([0-9a-f]{4})>#is", trim($current[$k]), $map)) {
					$from = hexdec($map[1]);
					$to = hexdec($map[2]);
					$_from = hexdec($map[3]);

					for ($m = $from, $n = 0; $m <= $to; $m++, $n++)
						$transformations[sprintf("%04X", $m)] = sprintf("%04X", $_from + $n);
				} elseif (preg_match("#<([0-9a-f]{4})>\s+<([0-9a-f]{4})>\s+\[(.*)\]#ismU", trim($current[$k]), $map)) {
					$from = hexdec($map[1]);
					$to = hexdec($map[2]);
					$parts = preg_split("#\s+#", trim($map[3]));

					for ($m = $from, $n = 0; $m <= $to && $n < count($parts); $m++, $n++)
						$transformations[sprintf("%04X", $m)] = sprintf("%04X", hexdec($parts[$n]));
				}
			}
		}
	}
	function getTextUsingTransformations($texts, $transformations) {
		$document = "";
		for ($i = 0; $i < count($texts); $i++) {
			$isHex = false;
			$isPlain = false;

			$hex = "";
			$plain = "";
			for ($j = 0; $j < strlen($texts[$i]); $j++) {
				$c = $texts[$i][$j];
				switch($c) {
					case "<":
						$hex = "";
						$isHex = true;
                        $isPlain = false;
					break;
					case ">":
						$hexs = str_split($hex, $this->multibyte); // 2 or 4 (UTF8 or ISO)
						for ($k = 0; $k < count($hexs); $k++) {

							$chex = str_pad($hexs[$k], 4, "0"); // Add tailing zero
							if (isset($transformations[$chex]))
								$chex = $transformations[$chex];
							$document .= html_entity_decode("&#x".$chex.";");
						}
						$isHex = false;
					break;
					case "(":
						$plain = "";
						$isPlain = true;
                        $isHex = false;
					break;
					case ")":
						$document .= $plain;
						$isPlain = false;
					break;
					case "\\":
						$c2 = $texts[$i][$j + 1];
						if (in_array($c2, array("\\", "(", ")"))) $plain .= $c2;
						elseif ($c2 == "n") $plain .= '\n';
						elseif ($c2 == "r") $plain .= '\r';
						elseif ($c2 == "t") $plain .= '\t';
						elseif ($c2 == "b") $plain .= '\b';
						elseif ($c2 == "f") $plain .= '\f';
						elseif ($c2 >= '0' && $c2 <= '9') {
							$oct = preg_replace("#[^0-9]#", "", substr($texts[$i], $j + 1, 3));
							$j += strlen($oct) - 1;
							$plain .= html_entity_decode("&#".octdec($oct).";", $this->convertquotes);
						}
						$j++;
					break;

					default:
						if ($isHex)
							$hex .= $c;
						elseif ($isPlain)
							$plain .= $c;
					break;
				}
			}
			$document .= "\n";
		}

		return $document;
	}

    // CLASS PDF2TXT ----------------------------------------------------------------------------------------------------------------
}





            /*

             ISSO AQUI SERIA PRA PEGAR TERMOS DENTRO DO PDF, MAS FICOU LENTO DEMAIS

             $quantas = 0;
             unset($compara);
             //echo "<pre>";
             //print_r($termosPdf);
             //echo "</pre>";

             for($k=0; $k<count($termosPdf)-$nPalavras; $k++)
             {

                 for($i=$k; $i<$nPalavras; $i++)
                 {
                    $compara .= $termosPdf[$i]." ";
                 }
                 $compara = trim($compara);

                 for($j=$i; $j<count($termosPdf)-$nPalavras; $j++)
                 {
                    unset($termos);
                    for($l=0;$l<$nPalavras;$l++)
                    {
                        $termos .= $termosPdf[$j]." ";
                    }
                    $termos = trim($termos);
                    echo $compara." - ".$termos;
                    if($compara==$termos)
                    {
                        $quantas++;
                    }
                 }
                 echo " - ".$quantas."<br/>";
             }
             */
?>
