#!/usr/bin/php
<?php

include_once( '/opt/simce/index.php' );

global $app;

// Obtem a fila
$count = SiMCE\Classes\VoiceID::getCount(
	" contatos_id is not NULL AND voicedb IS NULL ",
	array()
);

// Obtem os objetos em processamento
$rows = SiMCE\Classes\VoiceID::getByQuery(
	" contatos_id is not NULL AND voicedb = :attr ",
	array( 'attr' => 1 )
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
