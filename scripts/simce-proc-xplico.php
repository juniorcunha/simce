#!/usr/bin/php	
<?php

/**
 * SiMCE Proc Xplico
 * 
 * Script responsável por processar os dados do Xplico e armazenar
 * na base de dados as informações dos registros
 * 
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @version 1.0
 * @copyright (c) 2013
 */

include_once( __DIR__ . '/../index.php' );
global $app;

// Define o nome do serviço
$serviceName = basename(__FILE__, ".php");

// Obtem as alocações
$arrAloc = \SiMCE\Classes\Alocacoes::getByQuery(
	//" ( inicio <= DATE(NOW()) AND fim >= DATE(NOW()) ) AND id = 167 ",
	//" id = 167 ",
	"",
	array()
);

// Percorre as alocações
foreach( $arrAloc as $alocacao ) {

	// Obtem recurso
	$recurso = \SiMCE\Classes\Recursos::getByID( $alocacao['recursos_id'] );
	
	// Filtra por alocação de Dados
	if ($recurso['tipo'] == \SiMCE\Classes\Recursos::TYPE_DATA) {
		
		// Obtem o ID do Xplico
		if (preg_match("/\/SD(\d+)/", $recurso['nome'], $reg)) {
			
			$xplico_id = intval( $reg[1] );
			$app['debug']->log( $serviceName, "Processando recurso " . $recurso['id'] . " - " . $recurso['nome']  . " - Xplico ID " . $xplico_id );
			$app['debug']->log( $serviceName, "Inicio: {$alocacao['orig_inicio']} - Fim: {$alocacao['orig_fim']}");

			//=================================
			//
			// Processa todas as tabeleas que 
			// conteham informações relevantes
			//
			//=================================
			$tables = array(
				'webs', 'emails', 'fbchats', 'ftp_files', 'ftps', 'httpfiles', 
				'mms', 'msn_chats', 'rtps', 'sips', 'tftp_files', 
				'tftps', 'unkfiles', 'unknows', 'webmails', 'webymsgs'
			);
			
			foreach( $tables as $table ) {
				
				$app['debug']->log( $serviceName, "Obtendo dados para a tabela $table" );
				
				$start = 0;
				$limit = 100;
				$total = 0;
				$count = 0;
						
				do {
				
					$sth = $app['xplico']->prepare("SELECT * FROM $table WHERE pol_id = ? AND capture_date BETWEEN ? AND ? ORDER BY capture_date LIMIT $start,$limit");
					$sth->execute(
						array( $xplico_id, $alocacao['orig_inicio'], $alocacao['orig_fim'] )
					);

					$start += $limit;
					$count = 0;
					while( $obj = $sth->fetchObject() ) {

						// Verifica informações extras para mms
						if ($table == 'mmscontents') {

							$add_sth = $app['xplico']->prepare("SELECT * FROM mmscontents WHERE mm_id = ?");
							$add_sth->execute(
								array( $obj->id )
							);
							$obj->mmscontents = $add_sth->fetchAll( PDO::FETCH_OBJ );

						}

						try {

							// Armazena o registro no banco de dados
							$registro = new \SiMCE\Classes\Registros();
							$registro->set('unidades', \SiMCE\Classes\Unidades::getByID( $recurso['unidades_id'] ,true)->bean);
							$registro->set('operacoes', \SiMCE\Classes\Operacoes::getByID( $alocacao['operacoes_id'] ,true)->bean);
							$registro->set('alvos', \SiMCE\Classes\Alvos::getByID( $alocacao['alvos_id'] ,true)->bean);
							$registro->set('recursos', \SiMCE\Classes\Recursos::getByID( $recurso['id'] ,true)->bean);
							$registro->set('data', $obj->capture_date );
							$registro->set('tipo', \SiMCE\Classes\Recursos::TYPE_DATA);
							$registro->set('identificador', "-" );
							$registro->set('classificacao', \SiMCE\Classes\Registros::PRIORITY_NONE );
							$registro->set('estado', \SiMCE\Classes\Registros::STATUS_NEW );
							$registro->set('tamanho', (!empty($obj->rs_bd_size) ? $obj->rs_bd_size : (!empty($obj->size) ? $obj->size : 0 ) ) );
							$registro->set('attr', json_encode(array( 'type' => $table, 'data' => $obj )));
							$registro->set('observacoes', null);
							$registro->set('relato', null);
							$registro->save();
							$count++;
							$total++;

							// Remove o registro inserido
							$del_sth = $app['xplico']->prepare("DELETE FROM $table WHERE id = ?");
							$del_sth->execute(
								array( $obj->id )
							);

						} catch (Exception $ex) {

							// CONTINUE
							$app['debug']->log( $serviceName, "Ignorando dado duplicado para $table - {$obj->id}" );

						}

					}

					if ($count) {
						$app['debug']->log( $serviceName, "$total registro(s) processado(s) para tabela $table" );
					}
					
				} while ( $count != 0 );
				
			}
			
		}
		
	}
	
}

?>