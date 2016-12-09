<?php
    include_once('classes/classeUsuario.php');
    $usuario = new Usuario($_GET['a']);
?>
<div class="box-header well">
    <h2><i class="icon-info-sign"></i>&nbsp;Usuários do Sistema</h2>						
</div>
<script src="js/formularios/usuario.js"></script>
<div class="box-content">

    <?php
       $usuario->defineTarefa($_GET['t']);
    ?>    
    <div class="clearfix"></div>
</div>