<?php

/**
 * Classe pare acesso as configurações do sistema
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class Configuracoes extends DataRecord {
	
	public static $beanName = "configuracoes";
	
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
