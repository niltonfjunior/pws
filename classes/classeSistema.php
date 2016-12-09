<?php

class Sistema extends Diversa
{
    private $msgErroSql = "N�O FOI POSS�VEL CONSULTAR O BANCO DE DADOS.";

    public function nomePrincipal()
    {
        $retorno = $this->consultaSql("SELECT tituloPrincipalPar FROM parametros LIMIT 1");
        $nomePrincipal = mysql_fetch_assoc($retorno);
        print $nomePrincipal['tituloPrincipalPar'];
    }

    public function incluirCodigo($codigo)
    {
        include_once($codigo);
    }

    public function verificaLogin($usuario, $senha, $logado)
    {
        if($usuario=="" || $senha=="")
        {
            // SE AS VARI�VEIS DO POST EST�O VAZIAS, TERMINA O M�TODO AQUI
            $this->alertaInfo('Informe seu nome de usu�rio e sua senha');
            return;
        }

        // VARI�VEIS TEM CONTE�DO. AGORA, ADICIONAMOS BARRAS NA STRING DO USU�RIO
        $nusuario = strtolower(addslashes($usuario));
        // ESSAS BARRAS V�O EVITAR UMA INJE��O DE SQL ---
        $nsenha   = strtolower(addslashes($senha));
        // ----------------------------------------------

        // A QUERY VAI RETORNAR A QUANTIDADE DE REGISTROS ENCONTRADOS E O NOME DO USU�RIO ------------------------------------------
        $query   = "SELECT count(*) as quant, idUsuario, nomeUsuario FROM usuario WHERE loginUsuario='$nusuario' AND senhaUsuario='$nsenha'";

        /*
            NA QUERY, O COUNT(*) SER� ASSOCIADO � VARI�VEL QUANT
            ESSA VARI�VEL SER� USADA NO IF. SE ENCONTRAR O USU�RIO
            NA TABELA, RETORNAR� 1 (TRUE)
        */

        // PROIBIDO ESQUECER: MYSQL_QUERY EXECUTA A SENTEN�A DENTRO DO BANCO
        $retorno = $this->consultaSql($query);
        // A VARI�VEL $RETORNO RECEBE O RESULTADO DESSA EXECU��O -----------

        // PROIBIDO ESQUECER: MYSQL_FETCH_ASSOC VAI TRANSFORMAR A VARI�VEL $RETORNO EM UM VETOR DE �NDICES ASSOCIATIVOS
        $achou   = mysql_fetch_assoc($retorno);
        // A NOVA VARI�VEL $ACHOU SER� USADA PARA ACESSAR OS DADOS DA CONSULTA FEITA ----------------------------------

        if(!$achou['quant']) // SE A QUANTIDADE DE REGISTROS FOR ZERO (FALSE)
        {
            $this->alertaErro('Nome de usu�rio ou senha inv�lidos. Tente novamente.');
        }else{  // SE RETORNOU 1 EM QUANT
            $this->alertaInfo('Informe seu nome de usu�rio e sua senha');
            $_SESSION['s_logado']       = true;
            $_SESSION['s_nomeUsuario']  = $achou['nomeUsuario'];
            $_SESSION['s_idUsuario']    = $achou['idUsuario'];
            $this->javaScript("document.location.href='adm.php?a=1';");
        }
    }

    private function mataSessoes()
    {
        session_unset();    // LIMPA O BUFFER DE MEM�RIA DAS VARI�VEIS DE SESS�O
        session_destroy();  // APAGA O ARQUIVO F�SICO DA SESS�O NO SERVIDOR
    }

    public function verificaLogado()
    {
        if(isset($_GET['a']) && $_GET['a']=='0')
        {
            $this->mataSessoes();
        }
        // SE A VARI�VEL DE SESS�O S_LOGADO N�O EXISTIR, O TESTE RETORNAR� FALSO
        if(!$_SESSION['s_logado'])
        {
            // AVISA PARA O USU�RIO
            //$this->javaScript("alert('VOC� N�O EST� LOGADO NO SISTEMA')");

            // REDIRECIONA O NAVEGADOR PARA UMA PASTA ACIMA DO ADMINISTRATIVO, OU SEJA, PARA O PR�PRIO SITE
            $this->javaScript("document.location.href='../'");
        }
    }

    // Defesa contra injection -------------------------------------------------------------
    public function defesaPHP()
    {
        /*
            VOC� VAI PRECISAR TER CONTROLE TOTAL SOBRE AS PASSAGENS DE GET. CADA VARI�VEL
            ENVIADA DEVE SER TESTADA AQUI. POR ISSO, RECOMENDO QUE USE POUCAS PASSASGENS GET.
            SE N�O FOR POSS�VEL TRABALHAR COM POUCOS GETS, FA�A UM OUTRO M�TODO PARA TRATAR
            QUALQUER PASSAGEM DESSE TIPO, COMO FOI FEITO COM O M�TODO PEGAPOST()
        */

        $parametro = "";
        if(isset($_GET['a'])) // A��O
        {
            $parametro .= $_GET['a'];
        }
        if(isset($_GET['k'])) // CHAVE DE REGISTRO
        {
            $parametro .= $_GET['k'];
        }
        if(isset($_GET['t'])) // TAREFA OU P�GINA DO SITE
        {
            $parametro .= $_GET['t'];
        }
        $problemas = '/(http|www|.cgi|.txt|.gif|wget|ftp|.com|.br|.net|.org|database|tables)/i';
        if(preg_match($problemas, $parametro))
        {
            unset($_GET['a']);
            unset($_GET['k']);
            unset($_GET['t']);
            $this->mataSessoes(); // SE TEM PROBLEMA J� TERMINA O ACESSO DO SUJEITO MAL�FICO
        }
    }

    public function selecionarAcao($acao)
    {
        switch($acao){
            case 1:
                return "inicio.php";
                break;
            case 2:
                return "usuario.php";
                break;
            case 3:
                return "autor.php";
                break;
            case 4:
                return "palavra.php";
                break;
            case 5:
                return "tipo.php";
                break;
            case 6:
                return "publicacao.php";
                break;
            case 7:
                return "stopword.php";
                break;
            case 8:
                return "pesquisa.php";
                break;
            case 9:
                return "semantica.php";
                break;
            default:
                return "inicio.php";
                break;
        }
    }

    public function verificarPalavras()
    {
        $retorno = $this->consultaSql("SELECT * FROM palavra WHERE definicaoPalavra='__'");
        $quantos = mysql_num_rows($retorno);
        if($quantos)
        {
            $this->alertaAtencao('ATEN��O. EXISTEM PALAVRAS-CHAVE INCOMPLETAS NO SISTEMA.');
        }
    }
}

?>