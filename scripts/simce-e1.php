#!/usr/bin/php	
<?php

/**
 * SiMCE E1 Stats
 * 
 * Script responsável por verificar se o status dos links E1
 * 
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @version 1.0
 * @copyright (c) 2013
 */

include_once( __DIR__ . '/../index.php' );

global $app;

// Obtem o status atual dos canais
$e1Status = $app['asterisk']->command("khomp links show");

if (preg_match_all("/Link [\'\"]+(\d+)[\'\"]+: ([^\|]+)\s+\|\n/", $e1Status, $reg)) {
	
	// Faz a verradura em cada um
	foreach( $reg[1] as $index => $link ) {
		
		// Cria o objeto para armazenar o  status
		$stat = new SiMCE\Classes\E1Stats();
		$stat->set('data', date('Y-m-d H:i:00'));
		$stat->set('link', $link);
		
		// Verifica a situação do link
		if ( preg_match("/Up/", $reg[2][$index]) )
			$stat->set('status', 0);
		else
			$stat->set('status', 1);
		
		// Mensagem
		$stat->set('info', trim($reg[2][$index]));
		
		// Faz a gravação
		$stat->save();
		
	}
	
}

?>