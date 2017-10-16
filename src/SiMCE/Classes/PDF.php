<?php

/**
 * Classe para fazer a manipulação de arquivos PDF
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class PDF extends \TCPDF {

	/**
	 * Customiza os parâmetros de construção da classe
	 * 
	 * @param string $orientation
	 * @param string $unit
	 * @param string $format
	 * @param boolean $unicode
	 * @param string $encoding
	 * @param boolean $diskcache
	 * @param boolean $pdfa
	 * @return void
	 */
	public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false) {
		parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
		
		// Define os parâmetros iniciais
		global $app;
		$this->SetCreator( "DAD Tecnologia - SiMCE v" . $app['version'] );
		$this->SetAuthor( "DAD Tecnologia - SiMCE v" . $app['version'] );
		$this->SetLanguageArray(array('a_meta_charset' => 'UTF-8', 'a_meta_dir' => 'ltr', 'a_meta_language' => 'pt-br' ));
		
		// Define margens e outras definições
		$this->SetHeaderData( PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING );
		$this->SetHeaderFont( Array ( PDF_FONT_NAME_MAIN, "", PDF_FONT_SIZE_MAIN) );
		$this->setFooterFont( Array ( PDF_FONT_NAME_DATA, "", PDF_FONT_SIZE_DATA) );
		$this->SetDefaultMonospacedFont ( PDF_FONT_MONOSPACED );
		$this->SetMargins( PDF_MARGIN_LEFT, PDF_MARGIN_TOP + 20, PDF_MARGIN_RIGHT );
		$this->SetHeaderMargin( PDF_MARGIN_HEADER );
		$this->SetFooterMargin( PDF_MARGIN_FOOTER );
		$this->SetAutoPageBreak( TRUE, PDF_MARGIN_BOTTOM + 10);
		$this->SetImageScale( PDF_IMAGE_SCALE_RATIO );
		$this->SetFont( "freesans", "", 10);
		
	}
	
	/**
	 * Modifica o cabeçalho padrão dos relatórios
	 * 
	 * @return void
	 */
	public function Header() {
	
		global $app;
		
		// Obtem as informações da unidade do usuário
		$config = Configuracoes::getByID( $app['user']->getUnidade() );
		
		// Adiciona o cabeçalho
		$this->SetFont( "freesans", "", 8 );
		$this->writeHTML( $config['relatorio_cabecalho'], true, false, true, false);
		$this->writeHTML("<hr>");
		$this->SetMargins( PDF_MARGIN_LEFT, ceil($this->GetY()), PDF_MARGIN_RIGHT );
		$this->setFooterMargin( PDF_MARGIN_BOTTOM + ( 3 * (count(explode("\n", $config['relatorio_rodape'])))) );
		
	}
	
	/**
	 * Modifica o rodapé padrão dos relatórios
	 * 
	 * @return void
	 */
	public function Footer() {
	
		global $app;
		
		// Obtem as informações da unidade do usuário
		$config = Configuracoes::getByID( $app['user']->getUnidade() );
		
		// Adiciona o cabeçalho
		$this->writeHTML("<hr>");
		$this->writeHTML( $config['relatorio_rodape'], true, false, true, false);
		
		// Verifica se adiciona o nome do usuário também
		if ( $config['relatorio_usuario'] ) {
			$this->writeHTML(
				"<font size='8'>" . 
				" Página " . $this->getAliasNumPage() . " de " . $this->getAliasNbPages() .
				" - " . 
				" Gerado por " . $app['user']->get('nome') .
				"</font>",
				true,
				false,
				false,
				false,
				'C'
			);
		} else {
			$this->writeHTML(
				"<font size='8'>Página " . $this->getAliasNumPage() . " de " . $this->getAliasNbPages() . "</font>",
				true,
				false,
				false,
				false,
				'C'
			);
		}
		
		
	}
	
	public function refWriteHTML(&$html, $ln = true, $fill = false, $reseth = false, $cell = false, $align = '') {
		$this->writeHTML($html, $ln, $fill, $reseth, $cell, $align);
	}

	/**
	 * Modifica a função para capturar as tags de imagem embedadas
	 * 
	 * @param string $html
	 * @param boolean $ln
	 * @param boolean $fill
	 * @param boolean $reseth
	 * @param boolean $cell
	 * @param string $align
	 * @return void
	 */
	public function writeHTML($html, $ln = true, $fill = false, $reseth = false, $cell = false, $align = '') {
	
		//exec("/usr/bin/ffmpeg -i " . $file_path . " -acodec libmp3lame files/{$row->get('id')}.mp3 >/dev/null 2>/dev/null");

		// Captura as tags de audio
		if (preg_match_all("/tcpdf method=\"Annotation\" params=\"([^\"]+)\"/i", $html, $matches)) {
			foreach( $matches[1] as $regex ) {
				if (preg_match("/\.mp3/i", $regex))
					continue;
				$arr      = unserialize(urldecode($regex));
				$file_mp3 = SIMCE_CACHE_DIR . md5(microtime(true)) . ".mp3";
				exec("/usr/bin/ffmpeg -i " . $arr[5]['FS'] . " -acodec libmp3lame " . $file_mp3 . " >/dev/null 2>/dev/null");
				$arr[5]['FS'] = $file_mp3;
				$html = str_replace( $regex, urlencode(serialize($arr)), $html );
			}
		}
	
		// Captura as tags de imagem
		if (preg_match_all("/src=[\"\']+([^\"\']+)[\"\']+/i", $html, $matches)) {
		
			// Verifica o retorno
			foreach( $matches[1] as $regex ) {
		
				// Parse do Data URI
				preg_match("/data:([^;]+);base64\,(.*)$/", $regex, $parts);
				
				// Verifica se já processou a informação
				if (empty($parts[1]))
					continue;
				if (@preg_match("/simce/", $parts[2]))
					continue;
		
				// Verifica o mime type
				$ext = false;
				if ($parts[1] == "image/jpeg") // JPEG
					$ext = ".jpeg";
				else if ($parts[1] == "image/png") // PNG
					$ext = ".png";
				else if ($parts[1] == "image/gif") // PNG
					$ext = ".gif";
				
				// Gera o arquivo temporário
				if ($ext !== false) {
					
					//$tmpFile = microtime(true) . "-" . uniqid("", true) . $ext;
					$tmpFile = md5($parts[2]) . $ext;
					if (!file_exists( SIMCE_CACHE_DIR . "/" . $tmpFile ))
						file_put_contents( SIMCE_CACHE_DIR . "/" . $tmpFile, base64_decode($parts[2]) );
					
					// Substitiu a referencia do arquivo
					$html = str_replace( $regex, SIMCE_CACHE_DIR . $tmpFile, $html );
					
				}
				
			}
			
		}
		parent::writeHTML($html, $ln, $fill, $reseth, $cell, $align);
	}
	
}
