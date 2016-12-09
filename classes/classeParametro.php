<?php

class Parametro extends Diversa{

    private $acaoAtiva;
    private $classe = 'Parametro';
    private $tabela = 'parametros';
    private $pk     = 'idPar';      // CHAVE PRIMÁRIA PARA SELECTS
    private $cppe   = 'uriBase';    // COLUNA PRINCIPAL PARA MÉTODO EXCLUIR 
    private $tarefaAtiva;

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
        <table class="table table-striped table-bordered bootstrap-datatable datatable">
            <thead>
                <tr>
                    <th>ID</th>
				    <th>URI Base</th>
					<th>Título Principal</th>
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
                    echo "<td class='left'>$tupla[2]</td>";
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
        $retorno = mysql_query("SELECT * FROM $this->tabela WHERE $this->pk='$k'");
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
        <form name="<?php echo $this->classe;?>" class="form-horizontal" method="post" action="?a=<?php echo $this->acaoAtiva;?>&t=t2">
            <!-- A VARIÁVEL CHAVE É RESPONSÁVEL POR IDENTIFICAR UM NOVO REGISTRO OU UMA ALTERAÇÃO -->
            <input type="hidden" name="chave" value="<?php echo $k;?>"/>
            <!-- -------------------------------------------------------------------------------- -->
            <fieldset>
                <legend><?php echo $titulo;?></legend>
                <div class="control-group">
                    <label class="control-label" for="uriBase">URI Base:</label>
                    <div class="controls">
                        <input autofocus="autofocus" required="required" type="text" name="uriBase" style="width: 600px;" maxlength="200" class="span6" id="uriBase" value="<?php echo $tupla['uriBasePar'];?>" />
                    </div>
                </div>
                
                <div class="control-group">
                    <label class="control-label" for="tituloPrincipal">Título Principal:</label>
                    <div class="controls">
                        <input autofocus="autofocus" required="required" type="text" name="tituloPrincipal" style="width: 600px;" maxlength="50" class="span6" id="tituloPrincipal" value="<?php echo $tupla['tituloPrincipalPar'];?>" />
                    </div>
                </div>       
                
                <div class="control-group">
                    <label class="control-label" for="contador">Publicações:</label>
                    <div class="controls">
                        <input autofocus="autofocus" required="required" type="text" name="contador" style="width: 50px;" maxlength="6" class="span6" id="contador" value="<?php echo $tupla['contadorPar'];?>" />
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
            $chave           = $_POST['chave'];
            $uriBase         = $this->pegaPost('uriBase');
            $tituloPrincipal = $this->pegaPost('tituloPrincipal');            

            if(!$chave){
                mysql_query("INSERT INTO $this->tabela (uriBasePar, tituloPrincipalPar) VALUES ('$uriBase', '$tituloPrincipal')");
            }else{
                mysql_query("UPDATE $this->tabela SET uriBasePar='$uriBase', tituloPrincipalPar='$tituloPrincipal' WHERE $this->pk='$chave'");
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

   
}
?>