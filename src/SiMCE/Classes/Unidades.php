<?php

/**
 * Classe pare acesso as unidades do sistema
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class Unidades extends DataRecord {
	
	public static $beanName = "unidades";
	
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
		
		// Endereço
		if (empty($this->bean->endereco)) {
			$this->invalidFields[] = "endereco";
			$is_valid = false;
		}
		
		// Cidade
		if (empty($this->bean->cidade)) {
			$this->invalidFields[] = "cidade";
			$is_valid = false;
		}
		
		// Estado
		if (strlen($this->bean->estado)<2) {
			$this->invalidFields[] = "estado";
			$is_valid = false;
		}
		
		// Telefone
		if (empty($this->bean->telefone)) {
			$this->invalidFields[] = "telefone";
			$is_valid = false;
		} else {
			$this->bean->telefone = preg_replace("/[^\d]+/i","", $this->bean->telefone);
			if (strlen($this->bean->telefone) < 10) {
				$this->invalidFields[] = "telefone";
				$is_valid = false;
			}
		}
		
		return $is_valid;
		
	}
	
}

?>