<?php

    include_once('classes/classeAutor.php');
    $autor = new Autor($_GET['a']);
?>
<div class="box-header well">
    <h2><i class="icon-info-sign"></i>&nbsp;Cadastro de Autores</h2>						
</div>
<script src="js/formularios/autor.js"></script>
<div class="box-content">

    <?php
       $autor->defineTarefa($_GET['t']);
    ?>    
    <div class="clearfix"></div>
</div>