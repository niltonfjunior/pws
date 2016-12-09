<?php

    include_once('classes/classeTipo.php');
    $instancia = new Tipo($_GET['a']);
?>
<div class="box-header well">
    <h2><i class="icon-info-sign"></i>&nbsp;Cadastro de Autores</h2>						
</div>
<script src="js/formularios/tipo.js"></script>
<div class="box-content">

    <?php
       $instancia->defineTarefa($_GET['t']);
    ?>    
    <div class="clearfix"></div>
</div>