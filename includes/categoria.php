<?php

    include_once('classes/classeCategoria.php');
    $instancia = new Categoria($_GET['a']);
?>
<div class="box-header well">
    <h2><i class="icon-info-sign"></i>&nbsp;Cadastro de Categorias</h2>
</div>
<script src="js/formularios/categoria.js"></script>
<div class="box-content">

    <?php
       $instancia->defineTarefa($_GET['t']);
    ?>
    <div class="clearfix"></div>
</div>