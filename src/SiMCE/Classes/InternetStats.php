<?php

/**
 * Classe para armazenar o status da internet
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class InternetStats extends DataRecord {

	public static $beanName = "internetstats";
	
	/**
	 * Obtem o status atual
	 * 
	 * @return mixed
	 */
	public static function getCurrent() {
		
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
		

		$rows = self::getByQuery(
			" data BETWEEN :inicio AND :fim ",
			array( "inicio" => $inicio, "fim" => $fim )
		);
		array_shift( $rows );
			
		// Cria o array de retorno para o template
		foreach( $rows as $row ) {

			// Raw
			$row['data'] = date('d/m H:i', strtotime($row['data']));
			
			// Labels
			$avail['labels'][] = $row['data'];
			
			// Latencia
			$avail['latencia'][] = $row['latencia'];
	
			// Perda de Pacotes
			$avail['perda_pacotes'][] = $row['perda_pacotes'];

			// Status
			$avail['status'][$row['status']]++;

		}
		
		// Aplica a sumarização dos dados
		$factor = 288; // 1 dia de coleta
		self::summarizeData($avail['latencia'], $factor, "%.2f");
		self::summarizeData($avail['perda_pacotes'], $factor, "%.2f");
		self::summarizeLabel($avail['labels'], $factor);
		
		// Formata e ajusta os valores
		$avail['latencia'] = join(",", $avail['latencia']);
		$avail['perda_pacotes'] = join(",", $avail['perda_pacotes']);
		$avail['ticks'] = floor( count($avail['labels'])/8 );
		
		// Ajusta o percentual
		$avail['status']['ok']      = sprintf("%.2f", ( ($avail['status'][0] * 100) / count($rows) ));
		$avail['status']['not_ok']  = sprintf("%.2f", ( ($avail['status'][1] * 100) / count($rows) ));

		// Ajusta o tempo em minutos
		$avail['status']['ok_time']     = self::formatSeconds( $avail['status'][0]*300 );
		$avail['status']['not_ok_time'] = self::formatSeconds( $avail['status'][1]*300 );
		
		return $avail;
		
	}
	
}