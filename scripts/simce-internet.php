#!/usr/bin/php	
<?php

/**
 * SiMCE Internet Stats
 * 
 * Script responsável por verificar se a internet
 * está no ar e o tempo de reposta do link
 * 
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @version 1.0
 * @copyright (c) 2013
 */

include_once( __DIR__ . '/../index.php' );

global $app;

// Efetua o ping em direção ao www.google.com.br
exec( "/bin/ping -c 10 www.google.com.br", $output );

// Cria o objeto para salvar o status da internet
$stat = new SiMCE\Classes\InternetStats();
$stat->set('data', date('Y-m-d H:i:00'));
$stat->set('status',0);

// Faz a verificação do retorno
foreach( $output as $line ) {
	
	// Obtem o status e a perda de pacotes
	if (preg_match("/(\d+)\% packet loss/", $line, $reg)) {
		
		// Verifica se perdeu todos os pacotes
		if ($reg[1] == 100)
			$stat->set('status',1);
		
		$stat->set('perda_pacotes',$reg[1]);
	}
	
	// Obtem a latencia média do link
	if (preg_match("/rtt min\/avg\/max\/mdev = [\d\.]+\/([\d\.]+)\/[\d\.]+\/[\d\.]+/", $line, $reg)) {
		$stat->set('latencia',$reg[1]);
	}
	
}

// Armazena o resultado da consulta
$stat->save();

//print $channelStatus;

// Executa as verificações e armazena os dados em banco
//$stat = new \SiMCE\Classes\SystemStats();
//$stat->set('data', date('Y-m-d H:i:00'));
//$stat->set('cpu', getCpu());
//$stat->set('memoria', getSwap());
//$stat->set('disco', getDisk());
//$stat->save();

?>