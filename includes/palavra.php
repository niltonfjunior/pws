<?php

    include_once('classes/classePalavra.php');
    $instancia = new Palavra($_GET['a']);
?>
<div class="box-header well">
    <h2><i class="icon-info-sign"></i>&nbsp;Cadastro de Palavras-chave</h2>
</div>
<script src="js/formularios/palavra.js"></script>
<div class="box-content">

    <?php
       $instancia->defineTarefa($_GET['t']);
    ?>
    <div class="clearfix"></div>
</div>