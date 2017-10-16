<?php

/**
 * Classe pare acesso aos relatórios judiciais
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class RelatorioJudicial extends DataRecord {
	
	public static $beanName = "reljudicial";
	
	/**
	 * Valida as informações do formulário
	 * 
	 * @return boolean
	 */
	protected function validate() {

		parent::validate();
		$is_valid = true;
		
		// Nome
		if (empty($this->bean->nome)) {
			$this->invalidFields[] = "nome";
			$is_valid = false;
		}
		
		// Conteudo
		if (empty($this->bean->conteudo)) {
			$this->invalidFields[] = "conteudo";
			$is_valid = false;
		}
		
		return $is_valid;
		
	}
	
	/**
	 * Retorna um array com o id e o nome dos 
	 * relatórios da operação
	 * 
	 * @param type $id
	 * @return mixed
	 */
	public static function getListByOperation( $id ) {
		
		$rows = \R::getAll(
			" SELECT `id`,`nome`       " . 
			" FROM `reljudicial`       " . 
			" WHERE operacoes_id = :id " .
			" ORDER BY `nome`          ",
			array( "id" => $id )
		);
		
		return $rows;
		
	}
	
}

?>