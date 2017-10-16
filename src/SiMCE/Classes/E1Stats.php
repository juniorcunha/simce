<?php

/**
 * Classe para armazenar o status do fluxo E1
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class E1Stats extends DataRecord {

	public static $beanName = "e1stats";
	
	/**
	 * Obtem o status atual
	 * 
	 * @return mixed
	 */
	public static function getCurrent() {
		
		// Obtem os ids dos links
		$ids = \R::$f->begin()
			->select("max(id)")
			->from(self::$beanName)
			->addSQL("group by link")
			->get('col');
		
		// Busca os registros de acordo com os ids
		return self::getByQuery(
			" id in ( " . implode(",", array_values($ids)) . " ) LIMIT :limit ",
			array(
				":limit" => count($ids)
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
			->select("distinct link")
			->from(self::$beanName)
			->get('col');
		
		// Obtem as informações por link
		foreach( $ids as $index => $id ) {
			
			$rows = self::getByQuery(
				" link = :link AND data BETWEEN :inicio AND :fim ",
				array( "link" => $id, "inicio" => $inicio, "fim" => $fim )
			);
			array_shift( $rows );
			
			// Cria o array de retorno para o template
			foreach( $rows as &$row ) {

				// Raw
				$row['data'] = date('d/m H:i', strtotime($row['data']));
				$avail[$index]['raw'][] = $row;

				// Status
				$avail[$index]['status'][$row['status']]++;

			}
			
			// Ajusta o percentual
			$avail[$index]['status']['ok']      = sprintf("%.2f", ( ($avail[$index]['status'][0] * 100) / count($avail[$index]['raw']) ));
			$avail[$index]['status']['not_ok']  = sprintf("%.2f", ( ($avail[$index]['status'][1] * 100) / count($avail[$index]['raw']) ));
			
			// Ajusta o tempo em minutos
			$avail[$index]['status']['ok_time']     = self::formatSeconds( $avail[$index]['status'][0]*300 );
			$avail[$index]['status']['not_ok_time'] = self::formatSeconds( $avail[$index]['status'][1]*300 );
		
			//$avail[1] = $avail[$index];
			
		}
		
		return $avail;
		
	}
	
}