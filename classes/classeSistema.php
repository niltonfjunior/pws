<?php

class Sistema extends Diversa
{
    private $msgErroSql = "NÃO FOI POSSÍVEL CONSULTAR O BANCO DE DADOS.";

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
            // SE AS VARIÁVEIS DO POST ESTÃO VAZIAS, TERMINA O MÉTODO AQUI
            $this->alertaInfo('Informe seu nome de usuário e sua senha');
            return;
        }

        // VARIÁVEIS TEM CONTEÚDO. AGORA, ADICIONAMOS BARRAS NA STRING DO USUÁRIO
        $nusuario = strtolower(addslashes($usuario));
        // ESSAS BARRAS VÃO EVITAR UMA INJEÇÃO DE SQL ---
        $nsenha   = strtolower(addslashes($senha));
        // ----------------------------------------------

        // A QUERY VAI RETORNAR A QUANTIDADE DE REGISTROS ENCONTRADOS E O NOME DO USUÁRIO ------------------------------------------
        $query   = "SELECT count(*) as quant, idUsuario, nomeUsuario FROM usuario WHERE loginUsuario='$nusuario' AND senhaUsuario='$nsenha'";

        /*
            NA QUERY, O COUNT(*) SERÁ ASSOCIADO À VARIÁVEL QUANT
            ESSA VARIÁVEL SERÁ USADA NO IF. SE ENCONTRAR O USUÁRIO
            NA TABELA, RETORNARÁ 1 (TRUE)
        */

        // PROIBIDO ESQUECER: MYSQL_QUERY EXECUTA A SENTENÇA DENTRO DO BANCO
        $retorno = $this->consultaSql($query);
        // A VARIÁVEL $RETORNO RECEBE O RESULTADO DESSA EXECUÇÃO -----------

        // PROIBIDO ESQUECER: MYSQL_FETCH_ASSOC VAI TRANSFORMAR A VARIÁVEL $RETORNO EM UM VETOR DE ÍNDICES ASSOCIATIVOS
        $achou   = mysql_fetch_assoc($retorno);
        // A NOVA VARIÁVEL $ACHOU SERÁ USADA PARA ACESSAR OS DADOS DA CONSULTA FEITA ----------------------------------

        if(!$achou['quant']) // SE A QUANTIDADE DE REGISTROS FOR ZERO (FALSE)
        {
            $this->alertaErro('Nome de usuário ou senha inválidos. Tente novamente.');
        }else{  // SE RETORNOU 1 EM QUANT
            $this->alertaInfo('Informe seu nome de usuário e sua senha');
            $_SESSION['s_logado']       = true;
            $_SESSION['s_nomeUsuario']  = $achou['nomeUsuario'];
            $_SESSION['s_idUsuario']    = $achou['idUsuario'];
            $this->javaScript("document.location.href='adm.php?a=1';");
        }
    }

    private function mataSessoes()
    {
        session_unset();    // LIMPA O BUFFER DE MEMÓRIA DAS VARIÁVEIS DE SESSÃO
        session_destroy();  // APAGA O ARQUIVO FÍSICO DA SESSÃO NO SERVIDOR
    }

    public function verificaLogado()
    {
        if(isset($_GET['a']) && $_GET['a']=='0')
        {
            $this->mataSessoes();
        }
        // SE A VARIÁVEL DE SESSÃO S_LOGADO NÃO EXISTIR, O TESTE RETORNARÁ FALSO
        if(!$_SESSION['s_logado'])
        {
            // AVISA PARA O USUÁRIO
            //$this->javaScript("alert('VOCÊ NÃO ESTÁ LOGADO NO SISTEMA')");

            // REDIRECIONA O NAVEGADOR PARA UMA PASTA ACIMA DO ADMINISTRATIVO, OU SEJA, PARA O PRÓPRIO SITE
            $this->javaScript("document.location.href='../'");
        }
    }

    // Defesa contra injection -------------------------------------------------------------
    public function defesaPHP()
    {
        /*
            VOCÊ VAI PRECISAR TER CONTROLE TOTAL SOBRE AS PASSAGENS DE GET. CADA VARIÁVEL
            ENVIADA DEVE SER TESTADA AQUI. POR ISSO, RECOMENDO QUE USE POUCAS PASSASGENS GET.
            SE NÃO FOR POSSÍVEL TRABALHAR COM POUCOS GETS, FAÇA UM OUTRO MÉTODO PARA TRATAR
            QUALQUER PASSAGEM DESSE TIPO, COMO FOI FEITO COM O MÉTODO PEGAPOST()
        */

        $parametro = "";
        if(isset($_GET['a'])) // AÇÃO
        {
            $parametro .= $_GET['a'];
        }
        if(isset($_GET['k'])) // CHAVE DE REGISTRO
        {
            $parametro .= $_GET['k'];
        }
        if(isset($_GET['t'])) // TAREFA OU PÁGINA DO SITE
        {
            $parametro .= $_GET['t'];
        }
        $problemas = '/(http|www|.cgi|.txt|.gif|wget|ftp|.com|.br|.net|.org|database|tables)/i';
        if(preg_match($problemas, $parametro))
        {
            unset($_GET['a']);
            unset($_GET['k']);
            unset($_GET['t']);
            $this->mataSessoes(); // SE TEM PROBLEMA JÁ TERMINA O ACESSO DO SUJEITO MALÉFICO
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
            $this->alertaAtencao('ATENÇÃO. EXISTEM PALAVRAS-CHAVE INCOMPLETAS NO SISTEMA.');
        }
    }
}

?>