#!/usr/bin/php	
<?php

/**
 * SiMCE Setup
 * 
 * Script responsável por realizar a configuração inicial do sistema
 * 
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @version 1.0
 * @copyright (c) 2013
 */

include_once( __DIR__ . '/../index.php' );

function createAudioChannels() {

	// Incluindo recursos no sistema
	$start = 4700;
	$count = 60;
	for ( $i = 0; $i < 100; $i++ ) {

		// Cria o novo recurso
		$recurso = new \SiMCE\Classes\Recursos();
		$recurso->set('tipo','A');             // Audio
		$recurso->set('nome','SAF-' . $start); // Nome do canal
		$recurso->set('link',$start);          // Extensão virtual

		// Persiste no banco
		print " > Criando canal " . $recurso->get('nome') . "...\n";
		$recurso->save();

		// Incrementa o canal
		$start++;

	}
	
}

function createGSMChannels() {

	global $app;
	$channels = $app['asterisk']->command("khomp channels show concise");
	
	if (preg_match_all("/B([\d]{2})C([\d]{3}):[^:]+:[^:]+:kgsmIdle/", $channels, $reg)) {
		
		// Faz o loop pelos canais e cria os recursos
		$start = 1;
		foreach( $reg[0] as $index => $expr ) {

			//
			// Cria o recurso de GSM
			//
			$recurso = new \SiMCE\Classes\Recursos();
			$recurso->set('tipo','G');                                                                        // GSM
			$recurso->set('nome','SG-' . str_pad($start,4,"0",STR_PAD_LEFT));                                 // Nome do canal
			$recurso->set('link','Khomp_SMS/B' . (int) $reg[1][$index] . 'C' . (int) $reg[2][$index] . '-0'); // Canal físico
			print " > Criando canal " . $recurso->get('nome') . " - " . $recurso->get('link') . "...\n";
			$recurso->save();
			
			//
			// Cria o recurso de Audio
			//
			$recurso = new \SiMCE\Classes\Recursos();
			$recurso->set('tipo','A');                                                                         // Audio
			$recurso->set('nome','SAC-' . str_pad($start,4,"0",STR_PAD_LEFT));                                 // Nome do canal
			$recurso->set('link','Khomp/B' . (int) $reg[1][$index] . 'C' . (int) $reg[2][$index] . '-0.0');    // Canal físico
			print " > Criando canal " . $recurso->get('nome') . " - " . $recurso->get('link') . "...\n";
			$recurso->save();
			
			$start++;
			
		}
		
	}
	
	
}

function createDataChannels() {

	// Incluindo recursos no sistema
	$base = "/opt/simce/pcap";
	for ( $i = 1; $i <= 100; $i++ ) {
		
		// Cria o novo recurso
		$name = str_pad($i,4,"0",STR_PAD_LEFT);
		$recurso = new \SiMCE\Classes\Recursos();
		$recurso->set('tipo','D');                                             // Dados
		$recurso->set('nome','SD-' . $name . " ($base/SD$name)");             // Nome do canal
		$recurso->set('link','/opt/xplico/pol_' . $i . '/sol_' . $i . '/new'); // Caminho físico
		print " > Criando canal " . $recurso->get('nome') . " - " . $recurso->get('link') . "...\n";
		$recurso->save();
		
	}
	
}

//createDataChannels();
createAudioChannels();

?>
