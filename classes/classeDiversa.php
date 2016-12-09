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
        // VAMOS USAR ESSA FUNÇÃO PARA FACILITAR AS INSERÇÕES IN LINE DE JAVASCRIPT
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
	    $acento = @ereg_replace("[áàâãª]","a",$acento);
	    $acento = @ereg_replace("[ÁÀÂÃ]","A",$acento);
	    $acento = @ereg_replace("[éèê]","e",$acento);
	    $acento = @ereg_replace("[ÉÈÊ]","E",$acento);
	    $acento = @ereg_replace("[íìî]","i",$acento);
	    $acento = @ereg_replace("[ÍÌÎ]","I",$acento);
	    $acento = @ereg_replace("[óòôõº°]","o",$acento);
	    $acento = @ereg_replace("[ÓÒÔÕ]","O",$acento);
	    $acento = @ereg_replace("[úùû]","u",$acento);
	    $acento = @ereg_replace("[ÚÙÛ]","U",$acento);
	    $acento = str_replace("ç","c",$acento);
	    $acento = str_replace("Ç","C",$acento);
	    return $acento;
	}

    protected function converterUtf8Hex($txt)
    {
	    $txt = @ereg_replace("À","%C3%80",$txt);
		$txt = @ereg_replace("Á","%C3%81",$txt);
		$txt = @ereg_replace("Â","%C3%82",$txt);
		$txt = @ereg_replace("Ã","%C3%83",$txt);
		$txt = @ereg_replace("Ä","%C3%84",$txt);
		$txt = @ereg_replace("Å","%C3%85",$txt);
		$txt = @ereg_replace("Ç","%C3%87",$txt);
		$txt = @ereg_replace("È","%C3%88",$txt);
		$txt = @ereg_replace("É","%C3%89",$txt);
		$txt = @ereg_replace("É","%C3%89",$txt);
		$txt = @ereg_replace("Ê","%C3%8A",$txt);
		$txt = @ereg_replace("Ë","%C3%8B",$txt);
		$txt = @ereg_replace("Ë","%C3%8B",$txt);
		$txt = @ereg_replace("Ì","%C3%8C",$txt);
		$txt = @ereg_replace("Í","%C3%8D",$txt);
		$txt = @ereg_replace("Î","%C3%8E",$txt);
		$txt = @ereg_replace("Ò","%C3%92",$txt);
		$txt = @ereg_replace("Ó","%C3%93",$txt);
		$txt = @ereg_replace("Ô","%C3%94",$txt);
		$txt = @ereg_replace("Õ","%C3%95",$txt);
		$txt = @ereg_replace("Ö","%C3%96",$txt);
		$txt = @ereg_replace("Ù","%C3%99",$txt);
		$txt = @ereg_replace("Ú","%C3%9A",$txt);
		$txt = @ereg_replace("Û","%C3%9B",$txt);
		$txt = @ereg_replace("Ü","%C3%9C",$txt);
		$txt = @ereg_replace("Ý","%C3%9D",$txt);
		$txt = @ereg_replace("à","%C3%A0",$txt);
		$txt = @ereg_replace("à","%C3%A0",$txt);
		$txt = @ereg_replace("á","%C3%A1",$txt);
		$txt = @ereg_replace("â","%C3%A2",$txt);
		$txt = @ereg_replace("ã","%C3%A3",$txt);
		$txt = @ereg_replace("ä","%C3%A4",$txt);
		$txt = @ereg_replace("ç","%C3%A7",$txt);
		$txt = @ereg_replace("è","%C3%A8",$txt);
		$txt = @ereg_replace("é","%C3%A9",$txt);
		$txt = @ereg_replace("ê","%C3%AA",$txt);
		$txt = @ereg_replace("ë","%C3%AB",$txt);
		$txt = @ereg_replace("ì","%C3%AC",$txt);
		$txt = @ereg_replace("í","%C3%AD",$txt);
		$txt = @ereg_replace("î","%C3%AE",$txt);
		$txt = @ereg_replace("ï","%C3%AF",$txt);
		$txt = @ereg_replace("ò","%C3%B2",$txt);
		$txt = @ereg_replace("ó","%C3%B3",$txt);
		$txt = @ereg_replace("ô","%C3%B4",$txt);
		$txt = @ereg_replace("õ","%C3%B5",$txt);
		$txt = @ereg_replace("ö","%C3%B6",$txt);
		$txt = @ereg_replace("ù","%C3%B9",$txt);
		$txt = @ereg_replace("ú","%C3%BA",$txt);
		$txt = @ereg_replace("û","%C3%BB",$txt);
		$txt = @ereg_replace("ü","%C3%BC",$txt);
	    return $txt;
	}

    protected function reverterUtf8Hex($txt)
    {
	    $txt = @ereg_replace("%C3%80","À",$txt);
		$txt = @ereg_replace("%C3%81","Á",$txt);
		$txt = @ereg_replace("%C3%82","Â",$txt);
		$txt = @ereg_replace("%C3%83","Ã",$txt);
		$txt = @ereg_replace("%C3%84","Ä",$txt);
		$txt = @ereg_replace("%C3%85","Å",$txt);
		$txt = @ereg_replace("%C3%87","Ç",$txt);
		$txt = @ereg_replace("%C3%88","È",$txt);
		$txt = @ereg_replace("%C3%89","É",$txt);
		$txt = @ereg_replace("%C3%89","É",$txt);
		$txt = @ereg_replace("%C3%8A","Ê",$txt);
		$txt = @ereg_replace("%C3%8B","Ë",$txt);
		$txt = @ereg_replace("%C3%8B","Ë",$txt);
		$txt = @ereg_replace("%C3%8C","Ì",$txt);
		$txt = @ereg_replace("%C3%8D","Í",$txt);
		$txt = @ereg_replace("%C3%8E","Î",$txt);
		$txt = @ereg_replace("%C3%92","Ò",$txt);
		$txt = @ereg_replace("%C3%93","Ó",$txt);
		$txt = @ereg_replace("%C3%94","Ô",$txt);
		$txt = @ereg_replace("%C3%95","Õ",$txt);
		$txt = @ereg_replace("%C3%96","Ö",$txt);
		$txt = @ereg_replace("%C3%99","Ù",$txt);
		$txt = @ereg_replace("%C3%9A","Ú",$txt);
		$txt = @ereg_replace("%C3%9B","Û",$txt);
		$txt = @ereg_replace("%C3%9C","Ü",$txt);
		$txt = @ereg_replace("%C3%9D","Ý",$txt);
		$txt = @ereg_replace("%C3%A0","à",$txt);
		$txt = @ereg_replace("%C3%A0","à",$txt);
		$txt = @ereg_replace("%C3%A1","á",$txt);
		$txt = @ereg_replace("%C3%A2","â",$txt);
		$txt = @ereg_replace("%C3%A3","ã",$txt);
		$txt = @ereg_replace("%C3%A4","ä",$txt);
		$txt = @ereg_replace("%C3%A7","ç",$txt);
		$txt = @ereg_replace("%C3%A8","è",$txt);
		$txt = @ereg_replace("%C3%A9","é",$txt);
		$txt = @ereg_replace("%C3%AA","ê",$txt);
		$txt = @ereg_replace("%C3%AB","ë",$txt);
		$txt = @ereg_replace("%C3%AC","ì",$txt);
		$txt = @ereg_replace("%C3%AD","í",$txt);
		$txt = @ereg_replace("%C3%AE","î",$txt);
		$txt = @ereg_replace("%C3%AF","ï",$txt);
		$txt = @ereg_replace("%C3%B2","ò",$txt);
		$txt = @ereg_replace("%C3%B3","ó",$txt);
		$txt = @ereg_replace("%C3%B4","ô",$txt);
		$txt = @ereg_replace("%C3%B5","õ",$txt);
		$txt = @ereg_replace("%C3%B6","ö",$txt);
		$txt = @ereg_replace("%C3%B9","ù",$txt);
		$txt = @ereg_replace("%C3%BA","ú",$txt);
		$txt = @ereg_replace("%C3%BB","û",$txt);
		$txt = @ereg_replace("%C3%BC","ü",$txt);
	    return $txt;
	}

    protected function nomeMes($mes)
    {
        switch($mes)
        {
            case 1: return "Janeiro";break;
            case 2: return "Fevereiro";break;
            case 3: return "Março";break;
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
            "AP"=>"AMAPÁ",
            "AM"=>"AMAZONAS",
            "BA"=>"BAHIA",
            "CE"=>"CEARÁ",
            "DF"=>"DISTRITO FEDERAL",
            "ES"=>"ESPÍRITO SANTO",
            "GO"=>"GOIÁS",
            "MT"=>"MATO GROSSO",
            "MS"=>"MATO GROSSO DO SUL",
            "MA"=>"MARANHÃO",
            "MG"=>"MINAS GERAIS",
			"PA"=>"PARÁ",
			"PB"=>"PARAÍBA",
            "PR"=>"PARANÁ",
            "PE"=>"PERNAMBUCO",
            "PI"=>"PIAUÍ",
			"RJ"=>"RIO DE JANEIRO",
			"RN"=>"RIO GRANDE DO NORTE",
			"RS"=>"RIO GRANDE DO SUL",
			"RO"=>"RONDÔNIA",
			"RR"=>"RORAIMA",
			"SC"=>"SANTA CATARINA",
            "SP"=>"SÃO PAULO",
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