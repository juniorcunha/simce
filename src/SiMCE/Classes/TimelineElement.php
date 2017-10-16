<?php

/**
 * Classe responsável por registrar os elementos na timeline
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class TimelineElement extends DataRecord {
	
	public static $beanName = "timeline";
	
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
		
		// Data
		if (empty($this->bean->data)) {
			$this->invalidFields[] = "data";
			$is_valid = false;
		} else {
			$this->bean->data = $this->dateToDb($this->bean->data);
		}
		
		return $is_valid;
		
	}
	
}

?>