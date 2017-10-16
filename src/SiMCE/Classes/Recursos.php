<?php

/**
 * Classe pare acesso aos recursos do sistema
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class Recursos extends DataRecord {
	
	const TYPE_AUDIO = 'A';
	const TYPE_GSM = 'G';
	const TYPE_DATA = 'D';
	
	public static $beanName = "recursos";
	
}

?>