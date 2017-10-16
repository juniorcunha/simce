#!/usr/bin/php
<?php

include_once( '/opt/simce/index.php' );

global $app;

$count = \SiMCE\Classes\Alocacoes::getCount(
	" status = :status ",
	array( 'status' => 'busy' )
);

print "OK - $count chamada(s) ativa(s) | 'Chamadas ativas'=$count;;;0;\n";

?>
