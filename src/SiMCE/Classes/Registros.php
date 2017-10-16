<?php

/**
 * Classe pare acesso aos registros do sistema
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class Registros extends DataRecord {
	
	const STATUS_NEW = 0;
	const STATUS_VIEWED = 1;
	const STATUS_TRANSCRIBED = 2;
	
	const PRIORITY_NONE = 0;
	const PRIORITY_LOW = 1;
	const PRIORITY_MEDIUM = 2;
	const PRIORITY_HIGH = 3;
	
	public static $beanName = "registros";
	
	public static $formatCallbacks = array(
		'data'          => 'self::dbToDateTime',
		'identificador' => 'self::maskNumber',
		'attr'          => 'json_decode',
	);
	
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
	
	/**
	 * Retorna o stream em base64 da imagem
	 * 
	 * @return string
	 */
	public function getImagePath( $path = false ) {
		
		$file  = ($path === true) ? SIMCE_DIR . "/records/" : "/simce/records/";
		$file .= $this->get("unidades_id") . "/" . $this->get("operacoes_id") . "/" . $this->get("alvos_id") . "/" . $this->get("id") . ".png";
		return $file;
		
	}
	
	/**
	 * Retorna o web path do audio
	 * 
	 * @return string
	 */
	public function getFilePath() {
		
		$file  = SIMCE_DIR . "/records/";
		$file .= $this->get("unidades_id") . "/" . $this->get("operacoes_id") . "/" . $this->get("alvos_id") . "/" . $this->get("id") . ".wav";
		return $file;
		
	}
	
	public function getContatos() {

		// Adiciona os contatos
		$isTranscribed = false;
		$aContatos = array();
		$uContatos = array();
		$aSegmentos = \SiMCE\Classes\Segmentos::getByQuery(
			" registros_id = :id ",
			array( "id" => $this->get("id") ),
			true
		);
		foreach( $aSegmentos as $seg ) {
			if ($seg->bean->voiceid->contatos_id !== null)
				$aContatos[] = $seg->bean->voiceid->contatos->nome;
			else
				$uContatos[] = $seg->bean->voiceid->id;
			// Verifica transcrição
			if (!empty($seg->bean->transcricao))
				$isTranscribed = true;
		}
		sort($aContatos); $aContatos = array_unique($aContatos);
		sort($uContatos); $uContatos = array_unique($uContatos);
		if (isset($row['attr']->voiceid) && $row['attr']->voiceid == 0) {
			return "Aguardando processo de reconhecimento de voz...";
		} else if (isset($row['attr']->voiceid) && $row['attr']->voiceid == 1) {
			return "Processando reconhecimento de voz...";
		} else {
			if (count($uContatos))
				$aContatos[] = count($uContatos) . " Desconhecido(s)";
			if (!count($aContatos))
				$aContatos[] = "Nenhum segmento de voz identificado";
			return implode(", ", $aContatos);
		}

	}

	public function getTamanho() {

		// Tamanho em formato humano
		$base = log($this->get('tamanho')) / log(1024);
		$suffixes = array('B', 'Kb', 'Mb', 'Gb', 'Tb');   
		$size = round(pow(1024, $base - floor($base)), 2) . ' ' . $suffixes[floor($base)];
		
		// Tempo em formato humano
		$time = @gmdate("H:i:s", $this->get('attr')->tempo%86400);
		
		// Ajusta o valor
		return $time . " (" . $size . ")";

	}	

	public function getSegmentos() {

		// Obtem os segmentos
		$segmentos = \SiMCE\Classes\Segmentos::getByQuery(
			" registros_id = :id ORDER BY inicio ",
			array( "id" => $this->get('id') )
		);
		
		// Popula o voiceid e o contato
		foreach( $segmentos as &$seg ) {
			$seg['voiceid'] = \SiMCE\Classes\VoiceID::getByID($seg['voiceid_id']);
			if (!empty($seg['voiceid']['contatos_id'])) {
				$seg['voiceid']['contatos_id'] = \SiMCE\Classes\Contatos::getByID($seg['voiceid']['contatos_id']);
			}
		}
		return $segmentos;
	}
	
}

?>
