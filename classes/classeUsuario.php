<?php

class Usuario extends Diversa{
    
    private $acaoAtiva;
    private $classe = "Usuario";
    private $tabela = "usuario";
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
					<th>Nome</th>
					<th>Login</th>					
					<th>Ações</th>
				</tr>
            </thead>   
			<tbody>
            <?php
                $retorno = $this->consultaSql("SELECT * FROM usuario ORDER BY nomeUsuario");
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
                    <?php
                        if($this->quantos($this->tabela)>1)
                        {
                    ?>
    					   <a class="btn btn-danger" href="?a=<?php echo $this->acaoAtiva;?>&k=<?php echo $tupla[0];?>&t=t3">
    					       <i class="icon-trash icon-white"></i> 
    					       Excluir
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
        $retorno = mysql_query("SELECT nomeUsuario, loginUsuario, senhaUsuario FROM usuario WHERE idUsuario='$k'");
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
                    <label class="control-label" for="nomeUsuario">Nome:</label>
                    <div class="controls">
                        <input autofocus="autofocus" required="required" type="text" name="nomeUsuario" style="width: 200px;" maxlength="30" class="span6 nomeUsuario" id="nomeUsuario" value="<?php echo $tupla['nomeUsuario'];?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="loginUsuario">Login:</label>
                    <div class="controls">
                        <input required="required" type="text" name="loginUsuario" style="width: 100px;" maxlength="12" class="span6 loginUsuario" id="loginUsuario" value="<?php echo $tupla['loginUsuario'];?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="senhaUsuario">Senha:</label>
                    <div class="controls">
                        <input required="required" type="password" name="senhaUsuario" style="width: 100px;" maxlength="12" class="span6 senhaUsuario" id="senhaUsuario" value="<?php echo $tupla['senhaUsuario'];?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="confirmarSenha">Confirme sua senha:</label>
                    <div class="controls">
                        <input type="password" name="confirmarSenha" style="width: 100px;" maxlength="12" class="span6 confirmarSenha" id="confirmarSenha" />
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
            $chave        = $_POST['chave'];
            $nomeUsuario  = $this->pegaPost('nomeUsuario');        
            $loginUsuario = strtolower($this->pegaPost('loginUsuario'));
            $senhaUsuario = strtolower($this->pegaPost('senhaUsuario'));
            
            // VERIFICANDO SE LOGIN E SENHA JÁ EXISTEM
            
            $retorno = mysql_query("SELECT COUNT(*) as quantos FROM usuario WHERE loginUsuario='$loginUsuario' AND senhaUsuario='$senhaUsuario' AND idUsuario<>'$chave'");
            $quantos = mysql_fetch_assoc($retorno);
            
            if($quantos['quantos'])
            {
                $this->alertaErro("OUTRO USUÁRIO JÁ ESCOLHEU ESTE LOGIN E/OU SENHA");
                $this->form($chave);            
            }else{
                if(!$chave){
                    mysql_query("INSERT INTO usuario (nomeUsuario, loginUsuario, senhaUsuario) VALUES ('$nomeUsuario', '$loginUsuario', '$senhaUsuario')");            
                }else{
                    mysql_query("UPDATE usuario SET nomeUsuario='$nomeUsuario', loginUsuario='$loginUsuario', senhaUsuario='$senhaUsuario' WHERE idUsuario='$chave'");            
                }
                $this->gravaLog($this->classe, $this->tarefaAtiva, $chave);        
                $this->alertaSucesso('REGISTRO GRAVADO COM SUCESSO');                
            }
            $_SESSION['s_gravar'] = false; // EVITA PROBLEMA COM O F5        
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
                <?php $this->alertaAtencao("CONFIRMA EXCLUIR O USUÁRIO ".strtoupper($tupla['nomeUsuario'])."?");?>
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
            mysql_query("DELETE FROM usuario WHERE idUsuario='$chave'");            
            $this->gravaLog($this->classe, $this->tarefaAtiva, $chave);
            $this->alertaSucesso('REGISTRO EXCLUÍDO');            
        }
        $this->lista();
    }    
}
?>