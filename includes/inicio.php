<?php
    include_once('classes/classeParametro.php');
    $parametro = new Parametro($_GET['a']);
?>
<div class="box-header well">
    <h2><i class="icon-info-sign"></i>&nbsp;Parâmetros do Sistema</h2>						
</div>
<script src="js/formularios/parametro.js"></script>
<div class="box-content">

    <?php
       $parametro->defineTarefa($_GET['t']);
    ?>    
    <div class="clearfix"></div>
</div>