<?php

/**
 * Classe pare acesso as permissões do sistema
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class Permissoes extends DataRecord {
	
	public static $beanName = "permissoes";
	
	/**
	 * Valida as informações do formulário
	 * 
	 * @return boolean
	 */
	protected function validate() {

		parent::validate();
		$is_valid = true;
		
		return $is_valid;
		
	}
	
}

?>