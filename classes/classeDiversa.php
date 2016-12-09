<?php

abstract class Diversa extends Banco
{

    public function buscaUriBase()
    {
        $retorno = $this->consultaSql("SELECT uriBasePar FROM parametros LIMIT 1");
        $uri     = mysql_fetch_assoc($retorno);
        //echo $uri['uriBasePar'];
        //exit;
        return $uri['uriBasePar'];
        
    }

    protected function javaScript($conteudo)
    {
        // VAMOS USAR ESSA FUN��O PARA FACILITAR AS INSER��ES IN LINE DE JAVASCRIPT
        echo "<script type='text/javascript'>$conteudo</script>";
    }

    protected function formatoData($dataRecebida,$dataParaBanco)
    {
        $separador     = "/";
        $novoSeparador = "-";
        if(!$dataParaBanco)
        {
            $separador     = "-";
            $novoSeparador = "/";
        }
        $d  = explode($separador, $dataRecebida);
        $dd = $d[2].$novoSeparador.$d[1].$novoSeparador.$d[0];
        return $dd;
    }

    protected function linhas($n)
    {
        for($i=0;$i<$n;$i++)
        {
            echo "<br/>";
        }
    }

    protected function alertaInfo($msg)
    {
        echo "<div class='alert alert-info'>";
        echo $msg;
        echo "</div>";
    }

    protected function alertaSucesso($msg)
    {
        echo "<div class='alert alert-success'>";
        echo "<strong>".$msg."</strong>";
        echo "</div>";
    }

    protected function alertaErro($msg)
    {
        echo "<div class='alert alert-error'>";
        echo $msg;
        echo "</div>";
    }

    protected function alertaAtencao($msg)
    {
        echo "<div class='alert alert-attention'>";
        echo "<strong>".$msg."</strong>";
        echo "</div>";
    }

    protected function pegaPost($indice)
    {
        $conteudo = addslashes(trim($_POST[$indice]));
        return $conteudo;
    }

    protected function poeUnderline($texto)
    {
        $aux   = explode(" ",$texto);
        $texto = implode("_",$aux);
        return $texto;
    }

    protected function gravaLog($classe, $tarefa, $chave)
    {
        $log = $_SESSION['s_idUsuario'].";".date("Y-m-d H:i:s").";".$classe.";".$tarefa.";".$chave;
        mysql_query("INSERT INTO log (stringLog) VALUES ('$log')");
    }

    protected function tiraAcento($acento)
    {
	    $acento = @ereg_replace("[����]","a",$acento);
	    $acento = @ereg_replace("[����]","A",$acento);
	    $acento = @ereg_replace("[���]","e",$acento);
	    $acento = @ereg_replace("[���]","E",$acento);
	    $acento = @ereg_replace("[���]","i",$acento);
	    $acento = @ereg_replace("[���]","I",$acento);
	    $acento = @ereg_replace("[������]","o",$acento);
	    $acento = @ereg_replace("[����]","O",$acento);
	    $acento = @ereg_replace("[���]","u",$acento);
	    $acento = @ereg_replace("[���]","U",$acento);
	    $acento = str_replace("�","c",$acento);
	    $acento = str_replace("�","C",$acento);
	    return $acento;
	}

    protected function converterUtf8Hex($txt)
    {
	    $txt = @ereg_replace("�","%C3%80",$txt);
		$txt = @ereg_replace("�","%C3%81",$txt);
		$txt = @ereg_replace("�","%C3%82",$txt);
		$txt = @ereg_replace("�","%C3%83",$txt);
		$txt = @ereg_replace("�","%C3%84",$txt);
		$txt = @ereg_replace("�","%C3%85",$txt);
		$txt = @ereg_replace("�","%C3%87",$txt);
		$txt = @ereg_replace("�","%C3%88",$txt);
		$txt = @ereg_replace("�","%C3%89",$txt);
		$txt = @ereg_replace("�","%C3%89",$txt);
		$txt = @ereg_replace("�","%C3%8A",$txt);
		$txt = @ereg_replace("�","%C3%8B",$txt);
		$txt = @ereg_replace("�","%C3%8B",$txt);
		$txt = @ereg_replace("�","%C3%8C",$txt);
		$txt = @ereg_replace("�","%C3%8D",$txt);
		$txt = @ereg_replace("�","%C3%8E",$txt);
		$txt = @ereg_replace("�","%C3%92",$txt);
		$txt = @ereg_replace("�","%C3%93",$txt);
		$txt = @ereg_replace("�","%C3%94",$txt);
		$txt = @ereg_replace("�","%C3%95",$txt);
		$txt = @ereg_replace("�","%C3%96",$txt);
		$txt = @ereg_replace("�","%C3%99",$txt);
		$txt = @ereg_replace("�","%C3%9A",$txt);
		$txt = @ereg_replace("�","%C3%9B",$txt);
		$txt = @ereg_replace("�","%C3%9C",$txt);
		$txt = @ereg_replace("�","%C3%9D",$txt);
		$txt = @ereg_replace("�","%C3%A0",$txt);
		$txt = @ereg_replace("�","%C3%A0",$txt);
		$txt = @ereg_replace("�","%C3%A1",$txt);
		$txt = @ereg_replace("�","%C3%A2",$txt);
		$txt = @ereg_replace("�","%C3%A3",$txt);
		$txt = @ereg_replace("�","%C3%A4",$txt);
		$txt = @ereg_replace("�","%C3%A7",$txt);
		$txt = @ereg_replace("�","%C3%A8",$txt);
		$txt = @ereg_replace("�","%C3%A9",$txt);
		$txt = @ereg_replace("�","%C3%AA",$txt);
		$txt = @ereg_replace("�","%C3%AB",$txt);
		$txt = @ereg_replace("�","%C3%AC",$txt);
		$txt = @ereg_replace("�","%C3%AD",$txt);
		$txt = @ereg_replace("�","%C3%AE",$txt);
		$txt = @ereg_replace("�","%C3%AF",$txt);
		$txt = @ereg_replace("�","%C3%B2",$txt);
		$txt = @ereg_replace("�","%C3%B3",$txt);
		$txt = @ereg_replace("�","%C3%B4",$txt);
		$txt = @ereg_replace("�","%C3%B5",$txt);
		$txt = @ereg_replace("�","%C3%B6",$txt);
		$txt = @ereg_replace("�","%C3%B9",$txt);
		$txt = @ereg_replace("�","%C3%BA",$txt);
		$txt = @ereg_replace("�","%C3%BB",$txt);
		$txt = @ereg_replace("�","%C3%BC",$txt);
	    return $txt;
	}

    protected function reverterUtf8Hex($txt)
    {
	    $txt = @ereg_replace("%C3%80","�",$txt);
		$txt = @ereg_replace("%C3%81","�",$txt);
		$txt = @ereg_replace("%C3%82","�",$txt);
		$txt = @ereg_replace("%C3%83","�",$txt);
		$txt = @ereg_replace("%C3%84","�",$txt);
		$txt = @ereg_replace("%C3%85","�",$txt);
		$txt = @ereg_replace("%C3%87","�",$txt);
		$txt = @ereg_replace("%C3%88","�",$txt);
		$txt = @ereg_replace("%C3%89","�",$txt);
		$txt = @ereg_replace("%C3%89","�",$txt);
		$txt = @ereg_replace("%C3%8A","�",$txt);
		$txt = @ereg_replace("%C3%8B","�",$txt);
		$txt = @ereg_replace("%C3%8B","�",$txt);
		$txt = @ereg_replace("%C3%8C","�",$txt);
		$txt = @ereg_replace("%C3%8D","�",$txt);
		$txt = @ereg_replace("%C3%8E","�",$txt);
		$txt = @ereg_replace("%C3%92","�",$txt);
		$txt = @ereg_replace("%C3%93","�",$txt);
		$txt = @ereg_replace("%C3%94","�",$txt);
		$txt = @ereg_replace("%C3%95","�",$txt);
		$txt = @ereg_replace("%C3%96","�",$txt);
		$txt = @ereg_replace("%C3%99","�",$txt);
		$txt = @ereg_replace("%C3%9A","�",$txt);
		$txt = @ereg_replace("%C3%9B","�",$txt);
		$txt = @ereg_replace("%C3%9C","�",$txt);
		$txt = @ereg_replace("%C3%9D","�",$txt);
		$txt = @ereg_replace("%C3%A0","�",$txt);
		$txt = @ereg_replace("%C3%A0","�",$txt);
		$txt = @ereg_replace("%C3%A1","�",$txt);
		$txt = @ereg_replace("%C3%A2","�",$txt);
		$txt = @ereg_replace("%C3%A3","�",$txt);
		$txt = @ereg_replace("%C3%A4","�",$txt);
		$txt = @ereg_replace("%C3%A7","�",$txt);
		$txt = @ereg_replace("%C3%A8","�",$txt);
		$txt = @ereg_replace("%C3%A9","�",$txt);
		$txt = @ereg_replace("%C3%AA","�",$txt);
		$txt = @ereg_replace("%C3%AB","�",$txt);
		$txt = @ereg_replace("%C3%AC","�",$txt);
		$txt = @ereg_replace("%C3%AD","�",$txt);
		$txt = @ereg_replace("%C3%AE","�",$txt);
		$txt = @ereg_replace("%C3%AF","�",$txt);
		$txt = @ereg_replace("%C3%B2","�",$txt);
		$txt = @ereg_replace("%C3%B3","�",$txt);
		$txt = @ereg_replace("%C3%B4","�",$txt);
		$txt = @ereg_replace("%C3%B5","�",$txt);
		$txt = @ereg_replace("%C3%B6","�",$txt);
		$txt = @ereg_replace("%C3%B9","�",$txt);
		$txt = @ereg_replace("%C3%BA","�",$txt);
		$txt = @ereg_replace("%C3%BB","�",$txt);
		$txt = @ereg_replace("%C3%BC","�",$txt);
	    return $txt;
	}

    protected function nomeMes($mes)
    {
        switch($mes)
        {
            case 1: return "Janeiro";break;
            case 2: return "Fevereiro";break;
            case 3: return "Mar�o";break;
            case 4: return "Abril";break;
            case 5: return "Maio";break;
            case 6: return "Junho";break;
            case 7: return "Julho";break;
            case 8: return "Agosto";break;
            case 9: return "Setembro";break;
            case 10: return "Outubro";break;
            case 11: return "Novembro";break;
            case 12: return "Dezembro";break;
        }
    }

    protected function arrayEstados()
    {
        $estados = array(
            "AC"=>"ACRE",
            "AL"=>"ALAGOAS",
            "AP"=>"AMAP�",
            "AM"=>"AMAZONAS",
            "BA"=>"BAHIA",
            "CE"=>"CEAR�",
            "DF"=>"DISTRITO FEDERAL",
            "ES"=>"ESP�RITO SANTO",
            "GO"=>"GOI�S",
            "MT"=>"MATO GROSSO",
            "MS"=>"MATO GROSSO DO SUL",
            "MA"=>"MARANH�O",
            "MG"=>"MINAS GERAIS",
			"PA"=>"PAR�",
			"PB"=>"PARA�BA",
            "PR"=>"PARAN�",
            "PE"=>"PERNAMBUCO",
            "PI"=>"PIAU�",
			"RJ"=>"RIO DE JANEIRO",
			"RN"=>"RIO GRANDE DO NORTE",
			"RS"=>"RIO GRANDE DO SUL",
			"RO"=>"ROND�NIA",
			"RR"=>"RORAIMA",
			"SC"=>"SANTA CATARINA",
            "SP"=>"S�O PAULO",
			"SE"=>"SERGIPE",
			"TO"=>"TOCANTINS"
        );
        return $estados;
    }

    protected function optionEstados($e)
    {

        $estados = $this->arrayEstados();
        foreach(array_keys($estados) as $indice)
        {
            if($e==$indice)
            {
                echo "<option value='$indice' selected='selected'>&nbsp;$estados[$indice]&nbsp;</option>";
            }else{
                echo "<option value='$indice'>&nbsp;$estados[$indice]&nbsp;</option>";
            }
        }
    }

    protected function gerarSenha($tamanho)
    {
        $alfabeto   = str_split("ABCDEFGHJKLMNPRTUVWXYZ");
        $caracteres = str_split("123456789@#$%");
        for($i=0;$i<$tamanho;$i++)
        {
            if($i<($tamanho/2))
            {
                $senha .= $alfabeto[rand(0,22)];
            }else{
                $senha .= $caracteres[rand(0,13)];
            }
        }
		return str_shuffle($senha);
    }

    public function calendarioPopup($nome_form,$nome_input){
        echo "<a href='javascript:void(0)' onclick='if(self.gfPop)gfPop.fPopCalendar(document.$nome_form.$nome_input);return false;' HIDEFOCUS><img class='PopcalTrigger' align='absmiddle' src='js/calendario/calbtn.gif' width='34' height='22' border='0' alt='' style='margin-left:3px;' /></a>";
    }
}
?>