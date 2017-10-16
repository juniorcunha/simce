#!/usr/bin/php	
<?php

include_once( __DIR__ . '/../index.php' );

global $app;

// Define o nome do serviço
$serviceName = basename(__FILE__, ".php");

// Inicializa a interface de acesso ao Asterisk AGI
$agi = new \SiMCE\Classes\AGI();

if ($handle = opendir( AST_SPOOL_DIR )) {
	while (false !== ($file = readdir($handle))) {
		if (preg_match("/^[\.]+$/", $file)) { continue; }

		// Obtem a data e hora do arquivo
		$file = AST_SPOOL_DIR . "/" . $file;
		$wav_file = basename( $file );
		$vars = explode("-", $wav_file);
		$exten = $vars[1];
		$caller = $vars[2];
		$datetime  = substr($vars[3],0,4) . "-" . substr($vars[3],4,2) . "-" . substr($vars[3],6,2) . " ";

		// Verifica o tamanho e a duração do arquivo wav
		$tamanho = filesize( $file );
		$duracao = $agi->getDuration( $file );

		// Verifica se existe alguma alocação para a extensão solicitada
		$alocFound = 0;
		$arrAloc = \SiMCE\Classes\Alocacoes::getByQuery(
			" ( inicio <= '$datetime' AND fim >= '$datetime' ) ",
			array(),
			true
		);

		// Percorre as alocações
		if (count($arrAloc)) {

			foreach( $arrAloc as $aloc ) {
				if ($aloc->bean->recursos->link == $exten) {

					$organization = $aloc->bean->operacoes->unidades->id;
					$operation    = $aloc->bean->operacoes->id;
					$target       = $aloc->bean->alvos->id;
					$resource     = $aloc->bean->recursos->id;
					$datetime    .= substr($vars[4],0,2) . ":" . substr($vars[4],2,2) . ":" . substr($vars[4],4,2);

					// Grava o registro
					// Armazena o registro no banco de dados
					$registro = new \SiMCE\Classes\Registros();
					$registro->set('unidades', \SiMCE\Classes\Unidades::getByID($organization,true)->bean);
					$registro->set('operacoes', \SiMCE\Classes\Operacoes::getByID($operation,true)->bean);
					$registro->set('alvos', \SiMCE\Classes\Alvos::getByID($target,true)->bean);
					$registro->set('recursos', \SiMCE\Classes\Recursos::getByID($resource,true)->bean);
					$registro->set('data', $datetime);
					$registro->set('tipo', \SiMCE\Classes\Recursos::TYPE_AUDIO);
					$registro->set('identificador', $caller);
					$registro->set('classificacao', \SiMCE\Classes\Registros::PRIORITY_NONE );
					$registro->set('estado', \SiMCE\Classes\Registros::STATUS_NEW );
					$registro->set('tamanho', $tamanho);
					$registro->set('attr', json_encode(array( 'tempo' => $duracao, 'voiceid' => 0 )) );
					$registro->set('observacoes', null);
					$registro->set('relato', null);
					$registro->save();
					
					print(">> Arquivo: $file\n");
					print(">> Registro ID " . $registro->bean->id . " armazenado com sucesso!\n");
					
					// Cria a estrutura de diretório necessária
					$target_dir = __DIR__ . "/../records/{$organization}/{$operation}/{$target}";
					$old_mask = umask(0);
					@mkdir( $target_dir, 0777, true );
					@umask( $old_mask );
					
					// Cria a imagem em PNG
					print(">> Gerando imagem PNG\n");
					@system( __DIR__ . "/wav2png -w 500 -h 100 --foreground-color=2e4562ff --background-color=00000000 -o $target_dir/" . $registro->bean->id . ".png $file");
					
					// Move o áudio para a estrutura final
					print(">> Movendo arquivos para $target_dir\n");
					@rename( $file, "$target_dir/" . $registro->bean->id . ".wav" );

					// Atualiza a permissão do novo arquivo
					print(">> Ajustando permissoes\n");
					@chmod( "$target_dir/" . $registro->bean->id . ".wav", 0777 );
					@chmod( "$target_dir/" . $registro->bean->id . ".png", 0777 );
					flush();

				}
			}
		}
	}
}

?>
