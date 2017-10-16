<?php

/**
 * Classe pare acesso aos cargos e perfis do sistema
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class Cargos extends DataRecord {
	
	public static $beanName = "cargos";
	
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
		
		// Descrição
		if (empty($this->bean->descricao)) {
			$this->invalidFields[] = "descricao";
			$is_valid = false;
		}
		
		return $is_valid;
		
	}
	
}

?>