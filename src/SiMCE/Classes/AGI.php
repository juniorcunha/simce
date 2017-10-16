<?php

/**
 * Classe para fazer a interface com o Asterisk AGI
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class AGI {
	
	private $agivars = array();
	
	/**
	 * Inicializa a classe e faz a leitura das informações
	 * enviadas pelo Asterisk
	 * 
	 * @return void
	 */
	function __construct() {
		
		// Faz a o loop nas informações em STDIN
		while (!feof(STDIN)) {
			
			$agivar = trim(fgets(STDIN));
			if ($agivar === '') {
				break;
			}
			
			$agivar = explode(':', $agivar);
			$this->agivars[$agivar[0]] = trim($agivar[1]);
			//$this->debug("{$agivar[0]}:{$agivar[1]}");
		 }
		 
	}
	
	/**
	 * Envia o comando para o Asterisk
	 * 
	 * @param string $command
	 * @return void
	 */
	function send( $command ) {
		fwrite(STDOUT,"$command\n");
		fflush(STDOUT);
	}
	
	/**
	 * Faz o debug das informações
	 * 
	 * @param string $msg
	 * @return void
	 */
	function debug( $msg ) {
		
		global $app;
		$app['debug']->log( "simce-agi", $msg );
		$this->send("VERBOSE \"$msg\" 1");
	}
	
	/**
	 * Retorna a variável solicitada
	 * 
	 * @param string $var
	 * @return string
	 */
	function get( $var ) {
		if (isset($this->agivars[$var]))
			return $this->agivars[$var];
		else
			return null;
	}
	
	/**
	 * Analisa um arquivo wav e detecta o tempo
	 * de duração
	 * 
	 * @param string $wav
	 * @return int
	 */
	function getDuration( $wav ) {
		$fp = fopen($wav, 'r');
		if (fread($fp,4) == "RIFF") {
			fseek($fp, 20);
			$rawheader = fread($fp, 16);
			$header = unpack('vtype/vchannels/Vsamplerate/Vbytespersec/valignment/vbits',$rawheader);
			$pos = ftell($fp);
			while (fread($fp,4) != "data" && !feof($fp)) {
				$pos++;
				fseek($fp,$pos);
			}
			$rawheader = fread($fp, 4);
			$data = unpack('Vdatasize',$rawheader);
			$sec = filesize($wav)/$header["bytespersec"];
			fclose($fp);
			return ceil($sec);
		}
	}
	
	/**
	 * Faz a conversão do stream WAV em um stream MP3 já com o 
	 * cabeçalho removido
	 * 
	 * @param string $stream
	 * @return string
	 */
	static function convertStreamToMp3( $stream ) {
		
		// Formato dos file descriptors
		$descriptorspec = array (
			0 => array ( "pipe", "r"),
			1 => array ( "pipe", "w"),
			2 => array ( "file", "/dev/null", "a")
		);
		
		// Cria o processo de encode
		$encoder = proc_open ( "lame --nohist --silent --nores -q 7 --resample 8000 --cbr -B 32 -a -V 9 - -", $descriptorspec, $pipes);
		
		// Informa o stream o cabeçalho e o stream wav
		fwrite( $pipes[0], self::createWavHeader(strlen($stream)), 44 );
		fwrite( $pipes[0], $stream );
		fclose( $pipes[0] );
		
		// Obtem o retorno do pipe descartando o cabeçalho
		$tmp = fread( $pipes[1], 4 );
		unset( $tmp );
		$mp3 = stream_get_contents( $pipes[1] );
		fclose( $pipes[1] );
		
		// Encerra o processo
		proc_close( $encoder );
		
		return $mp3;
	
	}

	/**
	 * Cria o cabeçalho wav do pedaço de áudio
	 * 
	 * @param int $size
	 * @return string
	 */
	static private function createWavHeader( $size ) {
		return "RIFF" . pack ( "V", ( 16000 * $size) + 36) . "WAVEfmt " . chr ( 0x10) . chr ( 0x00) . chr ( 0x00) . chr ( 0x00) . 
				chr ( 0x01) . chr ( 0x00) . chr ( 0x01) . chr ( 0x00) . chr ( 0x40) . chr ( 0x1F) . chr ( 0x00) . chr ( 0x00) . 
				chr ( 0x80) . chr ( 0x3E) . chr ( 0x00) . chr ( 0x00) . chr ( 0x02) . chr ( 0x00) . chr ( 0x10) . chr ( 0x00) . 
				"data" . pack ( "V", 16000 * $size);
}
	
	
}

?>