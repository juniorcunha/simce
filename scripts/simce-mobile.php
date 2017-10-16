#!/usr/bin/php	
<?php

/**
 * SiMCE Mobile Stats
 * 
 * Script responsável por coletar variáveis da rede GSM
 * e armazenar em banco de dados
 * 
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @version 1.0
 * @copyright (c) 2013
 */

include_once( __DIR__ . '/../index.php' );

global $app;

// Obtem o status atual dos canais
$channelStatus = $app['asterisk']->command("khomp channels show concise");

// Obtem a lista de recursos de telefonia móvel
$recursos = \SiMCE\Classes\Recursos::getByQuery(
	" tipo = :tipo AND nome like 'SAC-%' ",
	array( "tipo" => "A "),
	true
);

// Verifica cada um dos recursos para ajustar a operadora e obter o nível de sinal
foreach( $recursos as $recurso ) {
	
	list ( $vendor, $longName ) = explode("/", $recurso->get('link') );
	list ( $channel, $junk ) = explode("-", $longName );
	
	// Faz a captura da placa e do canal
	if (preg_match("/B(\d+)C(\d+)/", $channel, $regs)) {
		
		// Monta o novo nome
		$channelName = "B" . str_pad( $regs[1], 2, "0", STR_PAD_LEFT ) . "C" . str_pad( $regs[2], 3, "0", STR_PAD_LEFT );
		
		// Verifica a operadora e o sinal do canal
		// <K> B00C000:unused:kcsFree:kgsmIdle:77%:TIM
		if (preg_match("/$channelName:[^:]+:[^:]+:[^:]+:(\d+)\%:([^\n]+)\n/", $channelStatus, $matches)) {
			
			// Armazena o nível de sinal do recurso
			$stat = new \SiMCE\Classes\MobileStats();
			$stat->set('recursos', $recurso->bean);
			$stat->set('data', date('Y-m-d H:i:00'));
			$stat->set('sinal', $matches[1]);
			$stat->save();
			
			// Ajusta o nome do canal
			if (!preg_match("/$matches[2]/", $recurso->get('nome'))) {
				
				$recurso->set('nome', $recurso->get('nome')  . " ({$matches[2]})");
				$recurso->set('nome', str_replace(' (<none>)','',$recurso->get('nome')));
				$recurso->save();
				
			}
			
			
		}

	}
	
	
}

//print $channelStatus;

// Executa as verificações e armazena os dados em banco
//$stat = new \SiMCE\Classes\SystemStats();
//$stat->set('data', date('Y-m-d H:i:00'));
//$stat->set('cpu', getCpu());
//$stat->set('memoria', getSwap());
//$stat->set('disco', getDisk());
//$stat->save();

?>