<?php

/**
 * Classe para fazer a interface com as informações do sistema
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class System {
	
	/**
	 * Obtem o modelo da CPU do equipamento atual
	 * 
	 * @return string
	 */
	static function getCpuModel() {
		
		$lines = file("/proc/cpuinfo");
		foreach( $lines as $line ) {
			if (preg_match("/^model name\s+:\s+(.*)$/i", $line, $reg)) {
				return $reg[1];
			}
		}
		return null;
		
	}
	
	/**
	 * Obtem a quantidade de memória do servidor
	 * 
	 * @return string
	 */
	static function getMemSize() {
		
		exec("/usr/bin/free -m", $lines);
		foreach( $lines as $line ) {
			if (preg_match("/^Mem:\s+(\d+)\s+/i", $line, $reg)) {
				return $reg[1] . "Mb";
			}
		}
		return null;
		
	}
	
	/**
	 * Obtem a quantidade de discos
	 * 
	 * @return string
	 */
	static function getDiskSize() {
		
		exec("/bin/df -h", $lines);
		$disks = array();
		
		foreach( $lines as $line ) {
			if (preg_match("/^\/[^\s]+\s+([^\s]+)\s+[^\s]+\s+[^\s]+\s+[^\s]+\s+([^\s]+)$/", $line, $reg)) {
				$disks[] = "{$reg[2]} => {$reg[1]}";
			}
		}
		
		return implode(" , ", $disks);
		
	}
	
}

?>