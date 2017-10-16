#!/usr/bin/php	
<?php

/**
 * SiMCE Stats
 * 
 * Script responsável por coletar variáveis de sistema ( cpu, memória e disco )
 * e armazenar em banco de dados
 * 
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @version 1.0
 * @copyright (c) 2013
 */

include_once( __DIR__ . '/../index.php' );

/**
 * Obtem a utilização de CPU
 * 
 * @return float
 */
function getCpu() {
	
	// Faz a leitura do arquivo /proc/stat
	$proc_stat = file( "/proc/stat" );
	
	// Obtem a primeira linha que é da CPU
	$line = array_shift( $proc_stat );
	
	// Faz a quebra por espaços em branco e descarta a primeira label
	$cpu = preg_split("/\s+/", $line);
	array_shift( $cpu );
	
	// Obtem o uso e o idle da cpu
	$idle = $cpu[3]; unset($cpu[3]);
	$used = array_sum( $cpu );
	
	// Calcula o percentual
	$perc = sprintf("%d", ( $used * 100 ) / ( $used + $idle ));
	
	return $perc;
	
}

/**
 * Obtem a utilização de Swap
 * 
 * @return float
 */
function getSwap() {
	
	// Faz a leitura do arquivo /proc/swaps
	$proc_swaps = file( "/proc/swaps" );
	
	// Obtem a última linha sobre o swap
	$line = array_pop( $proc_swaps );
	
	// Faz a quebra por espaços em branco
	$swap = preg_split("/\s+/", $line);
	
	// Calcula o percentual
	$perc = sprintf("%d", ( $swap[3] * 100 ) / ( $swap[2] ));
	
	return $perc;
	
}

/**
 * Retorna o tamanho e o percentual de uso
 * 
 * @return float
 */
function getDisk() {
	
	// Executa o comando df -m
	exec( "/bin/df -m", $output );
	
	// Busca a raiz do sistema
	foreach( $output as $line ) {
		
		// Quebra por espaço em branco
		$disk = preg_split("/\s+/", $line);
		//if ($disk[5] == "/opt/simce") {
		if (preg_match("/^\/opt/", $disk[5])) {
			
			// Calcula o percentual
			$perc = sprintf("%d", ( $disk[2] * 100 ) / ( $disk[2] + $disk[3] ));
			
			return $perc;
			
		}
		
	}
	
}

// Executa as verificações e armazena os dados em banco
$stat = new \SiMCE\Classes\SystemStats();
$stat->set('data', date('Y-m-d H:i:00'));
$stat->set('cpu', getCpu());
$stat->set('memoria', getSwap());
$stat->set('disco', getDisk());
$stat->save();

?>
