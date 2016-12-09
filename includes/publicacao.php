<?php

    include_once('classes/classePublicacao.php');
    $instancia = new Publicacao($_GET['a']);
?>
<div class="box-header well">
    <h2><i class="icon-info-sign"></i>&nbsp;Cadastro de Publicações</h2>
</div>
<script src="js/formularios/publicacao.js"></script>
<div class="box-content">

    <?php
       $instancia->defineTarefa($_GET['t']);
    ?>
    <div class="clearfix"></div>
</div>