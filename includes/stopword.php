<?php

    include_once('classes/classeStopword.php');
    $instancia = new Stopword($_GET['a']);
?>
<div class="box-header well">
    <h2><i class="icon-info-sign"></i>&nbsp;Cadastro de Stopwords</h2>
</div>
<script src="js/formularios/stopword.js"></script>
<div class="box-content">

    <?php
       $instancia->defineTarefa($_GET['t']);
    ?>
    <div class="clearfix"></div>
</div>