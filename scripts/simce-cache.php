#!/usr/bin/php	
<?php

/**
 * SiMCE Stats
 * 
 * Script responsável por remover arquivos de cache antigos
 * 
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @version 1.0
 * @copyright (c) 2013
 */

include_once( __DIR__ . '/../index.php' );

// Verifica todos os arquivos do diretório de cache
foreach( new DirectoryIterator( SIMCE_CACHE_DIR ) as $fileInfo ) {
	
	if (substr($fileInfo->getFilename(),0,1) == '.')
		continue;
	
	// Verifica se é mais antigo que 10 horas e remove
	if ( (time()- $fileInfo->getMTime()) >= 36000 ) {
		@unlink( $fileInfo->getPathname() );
	}
	
}

?>
