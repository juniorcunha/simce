#!/usr/bin/php
<?php

include_once( '/opt/simce/index.php' );

global $app;

// Obtem a fila
$count = SiMCE\Classes\Registros::getCount(
	" attr REGEXP :attr ",
	array( 'attr' => 'voiceid":0' )
);

// Obtem os objetos em processamento
$rows = SiMCE\Classes\Registros::getByQuery(
	" attr REGEXP :attr ",
	array( 'attr' => 'voiceid":1' )
);
$runningStr = array();
foreach( $rows as $row ) {
	$runningStr[] = $row['id'];
}
if (count($runningStr))
	$runningStr = implode(",",$runningStr);
else
	$runningStr = "Nenhum";

print "OK - $count processo(s) na fila. Processando: $runningStr | Processos=$count;;;0;\n";

?>
