<?php

/**
 * Classe pare acesso as operaçõces do sistema
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class Operacoes extends DataRecord {
	
	public static $beanName = "operacoes";
	
	public static $formatCallbacks = array(
		'inicio' => 'self::dbToDate',
		'fim'    => 'self::dbToDate'
	);
	
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
		
		// Início
		if (empty($this->bean->inicio)) {
			$this->invalidFields[] = "inicio";
			$is_valid = false;
		} else {
			$this->bean->inicio = $this->dateToDb($this->bean->inicio);
		}

		// Encerramento
		if (empty($this->bean->fim)) {
			$this->invalidFields[] = "fim";
			$is_valid = false;
		} else {
			$this->bean->fim = $this->dateToDb($this->bean->fim);
		}
		
		return $is_valid;
		
	}
	
}

?>