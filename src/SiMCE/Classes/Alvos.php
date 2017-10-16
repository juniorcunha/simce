<?php

/**
 * Classe pare acesso aos alvos do sistema
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class Alvos extends DataRecord {
	
	public static $beanName = "alvos";
	
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
		
		// RG
		if (!empty($this->bean->rg)) {
			$this->bean->rg = preg_replace("/[^\d]+/i","", $this->bean->rg);
			if (strlen($this->bean->rg) < 10) {
				$this->invalidFields[] = "rg";
				$is_valid = false;
			}
		}
		
		// CPF
		if (!empty($this->bean->cpf)) {
			$this->bean->cpf = preg_replace("/[^\d]+/i","", $this->bean->cpf);
			if (strlen($this->bean->cpf) < 11) {
				$this->invalidFields[] = "cpf";
				$is_valid = false;
			}
		}
		
		return $is_valid;
		
	}
	
}

?>
