<?php

    include_once('classes/classePublicacao.php');
    $instancia = new Publicacao($_GET['a']);
?>
<div class="box-header well">
    <h2><i class="icon-info-sign"></i>&nbsp;Pesquisa relacional de palavras-chave utilizadas em publicações</h2>
</div>
<script src="js/formularios/publicacao.js"></script>
<div class="box-content">

    <?php
       $instancia->pesquisar();
    ?>
    <div class="clearfix"></div>
</div>