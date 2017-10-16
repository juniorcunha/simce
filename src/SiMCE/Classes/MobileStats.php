<?php

/**
 * Classe para armazenar o status dos canais GSM
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class MobileStats extends DataRecord {

	public static $beanName = "mobilestats";
	
	/**
	 * Obtem o status atual
	 * 
	 * @return mixed
	 */
	public static function getCurrent() {
		
		// Obtem os ids dos recursos
		$ids = \R::$f->begin()
			->select("max(id)")
			->from(self::$beanName)
			->addSQL("group by recursos_id")
			->get('col');
		
		// Busca os registros de acordo com os ids
		if (count($ids) == 0)
			$ids = array(0);
		return self::getByQuery(
			" id in ( " . implode(",", array_values($ids)) . " ) LIMIT :limit ",
			array(
				":limit" => (count($ids) == 0) ? 1 : count($ids)
			),
			true
		);
		
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
		
		// Obtem os ids dos links
		$ids = \R::$f->begin()
			->select("distinct recursos_id")
			->from(self::$beanName)
			->get('col');
		
		// Obtem as informações por link
		foreach( $ids as $index => $id ) {
			
			$rows = self::getByQuery(
				" recursos_id = :id AND data BETWEEN :inicio AND :fim ",
				array( "id" => $id, "inicio" => $inicio, "fim" => $fim )
			);
			array_shift( $rows );
			
			// Cria o array de retorno para o template
			$avail['labels'] = array();
			foreach( $rows as &$row ) {

				// Data
				$row['data'] = date('d/m H:i', strtotime($row['data']));
				$avail['labels'][] = $row['data'];

				// Sinal
				$avail['canais'][$id]['sinal'][] = $row['sinal'];

			}
			
			// Obtem a operadora do canal
			$avail['canais'][$id]['nome'] = Recursos::getByID( $id, true )->get('nome');

		}

		// Aplica a sumarização dos dados e formata os dados
		$factor = 288; // 1 dia de coleta
		self::summarizeLabel($avail['labels'], $factor);
		$avail['ticks'] = floor( count($avail['labels'])/8 );
		
		// Aplica os ajustes no sinal
		foreach( $ids as $id ) {
			self::summarizeData($avail['canais'][$id]['sinal'], $factor, "%d");
			$avail['canais'][$id]['sinal'] = join(",", $avail['canais'][$id]['sinal']);
		}

		return $avail;
		
	}
	
}
