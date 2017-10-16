<?php

/**
 * Classe para armazenar o status do sistema
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class SystemStats extends DataRecord {

	public static $beanName = "sysstats";
	
	/**
	 * Obtem a atual performance do sistema
	 * 
	 * @return mixed
	 */
	public static function getCurrentPerformance() {
		
		$row = self::getByQuery( " ORDER BY `data` DESC LIMIT 1", array() );
		return array_pop($row);
		
	}
	
	/**
	 * Obtem a disponibilidade no período
	 * 
	 * @param string $inicio
	 * @param string $fim
	 * @return void
	 */
	public static function getAvail( $inicio, $fim ) {
		
		$avail = array();
		
		// Obtem as estatisticas de sistema no periodo
		$rows = self::getByQuery(
			" data BETWEEN :inicio AND :fim ",
			array( "inicio" => $inicio, "fim" => $fim )
		);

		// Cria o array de retorno para o template
		foreach( $rows as $row ) {
			
			// Labels
			$avail['labels'][] = date('d/m H:i', strtotime($row['data']));
			
			// Disco
			$avail['disco'][] = $row['disco'];
			
			// CPU
			$avail['cpu'][] = $row['cpu'];
			
			// Memoria
			$avail['memoria'][] = $row['memoria'];
			
		}

		// Aplica a sumarização dos dados
		$factor = 288; // 1 dia de coleta
		self::summarizeData($avail['disco'], $factor, "%.2f");
		self::summarizeData($avail['cpu'], $factor, "%.2f");
		self::summarizeData($avail['memoria'], $factor, "%.2f");
		self::summarizeLabel($avail['labels'], $factor);
		
		// Formata e ajusta os valores
		$avail['disco'] = join(",", $avail['disco']);
		$avail['cpu'] = join(",", $avail['cpu']);
		$avail['memoria'] = join(",", $avail['memoria']);
		$avail['ticks'] = floor( count($avail['labels'])/8 );
		
		return $avail;
		
	}
	
}