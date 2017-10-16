#!/usr/bin/php	
<?php

/**
 * SiMCE Calls
 * 
 * Script que vai manipular as chamadas e realizar 
 * o processamento das interceptações
 * 
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @version 1.0
 * @copyright (c) 2013
 */

include_once( __DIR__ . '/../index.php' );

global $app;

// Inicializa a interface de acesso ao Asterisk AGI
$agi = new \SiMCE\Classes\AGI();

// Define o nome do serviço
$serviceName = basename(__FILE__, ".php");

// Define a ação a ser tomada a extensão solicitada
$action       = $agi->get("agi_arg_1");
$exten        = $agi->get("agi_arg_2");
$file         = $agi->get("agi_arg_3");
$type         = $agi->get("agi_arg_4");
$organization = $agi->get("agi_arg_5");
$operation    = $agi->get("agi_arg_6");
$target       = $agi->get("agi_arg_7");
$resource     = $agi->get("agi_arg_8");
$allocation   = $agi->get("agi_arg_9");
$dial_status  = $agi->get("agi_arg_10");
$dial_time    = $agi->get("agi_arg_11");
$answer_time  = $agi->get("agi_arg_12");
$dial_number  = $agi->get("agi_arg_13");
 
if ($type == \SiMCE\Classes\Recursos::TYPE_AUDIO)
	$file = AST_SPOOL_DIR . $file . ".wav";

/**
 * Pré processamento das mensagens
 * 
 * Nesta etapa é verificado se existe alocação
 * disponível para o desvio realizado. 
 */
if ($action == "PRE") {
	
	$agi->debug("SiMCE - Pre Script");
	$agi->debug(">> Canal:    " . $agi->get("agi_channel"));
	$agi->debug(">> Extensao: $exten");
	
	
	// Verifica se existe alguma alocação para a extensão solicitada
	$alocFound = 0;
	$arrAloc = \SiMCE\Classes\Alocacoes::getByQuery(
		" ( inicio <= DATE(NOW()) AND fim >= DATE(NOW()) ) ",
		array(),
		true
	);
	foreach( $arrAloc as $aloc ) {
		
		// Alocação de Áudio ( Fixo ou Celular )
		if ($type == \SiMCE\Classes\Recursos::TYPE_AUDIO && $aloc->bean->recursos->tipo == \SiMCE\Classes\Recursos::TYPE_AUDIO) {
			if ($aloc->bean->recursos->link == $exten || $aloc->bean->recursos->link == $agi->get("agi_channel")) {
				
				$agi->debug(">> Alocacao encontrada!");
				$agi->debug(">> Unidade - " . $aloc->bean->operacoes->unidades->nome);
				$agi->debug(">> Operacao - " . $aloc->bean->operacoes->nome);
				$agi->debug(">> Alvo - " . $aloc->bean->alvos->nome);

				// Declara as variáveis no Asteris para pós processamento
				$agi->send("SET VARIABLE SiMCE-Organization " . $aloc->bean->operacoes->unidades->id);
				$agi->send("SET VARIABLE SiMCE-Operation " . $aloc->bean->operacoes->id);
				$agi->send("SET VARIABLE SiMCE-Target " . $aloc->bean->alvos->id);
				$agi->send("SET VARIABLE SiMCE-Resource " . $aloc->bean->recursos->id);
				$agi->send("SET VARIABLE SiMCE-Allocation " . $aloc->bean->id);
				
				// Verifica se existe desvio ativo
				if (!empty($aloc->bean->desvio_para)) {
					
					// Obtem o usuario e o telefone
					$user = SiMCE\Classes\Usuarios::getByID($aloc->bean->desvio_para);
					//$phone = substr( preg_replace("/[^\d]+/","",$user['telefone']), 2);
					$phone = "014" . preg_replace("/[^\d]+/","",$user['telefone']);
					$agi->debug(">> Desviando para o usuário " . $user['nome'] . " - Telefone: " . $phone);
					
					// Detecta o channel a ser utilizado
					if (!empty($aloc->bean->desvio_via)) {
						
						// Se for fluxo e1
						if ($aloc->bean->desvio_via == 'E') {
							
							$e1Status = $app['asterisk']->command("khomp links show concise");
							if (preg_match("/(B\d+)L\d+:kesOk/i", $e1Status, $matches)) {
								
								// Define a variável para o desvio
								$agi->debug(">> Desviando via link E1 -> {$matches[1]}");
								$agi->send("SET VARIABLE SiMCE-Dial {$matches[1]}/$phone");
								
							}
							
						} else { // Canal de Celular
							
							$recurso = \SiMCE\Classes\Recursos::getByID($aloc->bean->desvio_via,true);
							list ( $vendor, $longName ) = explode("/", $recurso->get('link') );
							list ( $channel, $junk ) = explode("-", $longName );

							// Faz a captura da placa e do canal
							if (preg_match("/B(\d+)C(\d+)/", $channel, $regs)) {

								// Monta o novo nome
								$channelName = "B" . str_pad( $regs[1], 2, "0", STR_PAD_LEFT ) . "C" . str_pad( $regs[2], 3, "0", STR_PAD_LEFT );
								
								// Define a variável para o desvio
								$agi->debug(">> Desviando via recurso " . $recurso->get('nome') . " -> $channelName");
								$agi->send("SET VARIABLE SiMCE-Dial {$channelName}/$phone");
								
							}
							
						}					
					}
					
				}
				
				// Atualiza a alocação para status "busy" (ocupado)
				$aloc->set('status','busy');
				$aloc->save();
				
				$alocFound = 1;
				break;
				
			}
			
		// Alocação de SMS (GSM)
		} else if ($type == \SiMCE\Classes\Recursos::TYPE_GSM && $aloc->bean->recursos->tipo == SiMCE\Classes\Recursos::TYPE_GSM) {
			
			$ident = "0" . preg_replace("/[^\d]+/", "", $aloc->bean->identificacao);
			if ($exten == $ident) {
				
				$agi->debug(">> Alocacao encontrada!");
				$agi->debug(">> Unidade - " . $aloc->bean->operacoes->unidades->nome);
				$agi->debug(">> Operacao - " . $aloc->bean->operacoes->nome);
				$agi->debug(">> Alvo - " . $aloc->bean->alvos->nome);

				// Declara as variáveis no Asteris para pós processamento
				$agi->send("SET VARIABLE SiMCE-Organization " . $aloc->bean->operacoes->unidades->id);
				$agi->send("SET VARIABLE SiMCE-Operation " . $aloc->bean->operacoes->id);
				$agi->send("SET VARIABLE SiMCE-Target " . $aloc->bean->alvos->id);
				$agi->send("SET VARIABLE SiMCE-Resource " . $aloc->bean->recursos->id);
                                $agi->send("SET VARIABLE SiMCE-Allocation " . $aloc->bean->id);
				
				// Atualiza a alocação para status "busy" (ocupado)
				$aloc->set('status','busy');
				$aloc->save();

				$alocFound = 1;
				break;
				
			}
			
		}
		
	}
	
	// Desliga a ligação se não encontrar alocação válida
	if (!$alocFound) {
		$agi->debug(">> Nenhuma alocacao encontrada. Desligando!");
		$agi->send("HANGUP");
	}

}

/**
 * Pós processamento das mensagens
 * 
 * Nesta etapa o áudio gerado é processado e registrado
 * no seu devido lugar.
 * 
 */
if ($action == "POST") {
		
	$agi->debug("SiMCE - Post Script");
	$agi->debug(">> Canal:       " . $agi->get("agi_channel"));
	$agi->debug(">> Extensao:    $exten");
	if ($type == SiMCE\Classes\Recursos::TYPE_AUDIO)
		$agi->debug(">> Arquivo:     $file");
	$agi->debug(">> ID Unidade:  $organization");
	$agi->debug(">> ID Operacao: $operation");
	$agi->debug(">> ID Alvo:     $target");
	$agi->debug(">> ID Recurso:  $resource");

	// Verifica se o arquivo existe
	if ($type == SiMCE\Classes\Recursos::TYPE_AUDIO) {
		if (!file_exists($file)) {
			$agi->debug(">> Arquivo nao encontrado!");
			exit;
		} else { // Se existir, força o fim da gravação
			$agi->send("EXEC StopMixMonitor");
			$agi->send("EXEC Wait 5");
		}
	}
	
	// ----------------------------
	//
	// Interceptação de áudio
	//
	// ----------------------------
	if ($type == \SiMCE\Classes\Recursos::TYPE_AUDIO) {
		
		// Verifica o tamanho e a duração do arquivo wav
		$tamanho = filesize( $file );
		$duracao = $agi->getDuration( $file );
		$agi->debug(">> Duracao:  $duracao segundo(s)");
		$agi->debug(">> Tamanho:  $tamanho bytes");
		
		// Obtem a data e hora do arquivo
		$wav_file = basename( $file );
		$vars = explode("-", $wav_file);
		$datetime  = substr($vars[3],0,4) . "-" . substr($vars[3],4,2) . "-" . substr($vars[3],6,2) . " ";
		$datetime .= substr($vars[4],0,2) . ":" . substr($vars[4],2,2) . ":" . substr($vars[4],4,2);
		
		// Armazena o registro no banco de dados
		$registro = new \SiMCE\Classes\Registros();
		$registro->set('unidades', \SiMCE\Classes\Unidades::getByID($organization,true)->bean);
		$registro->set('operacoes', \SiMCE\Classes\Operacoes::getByID($operation,true)->bean);
		$registro->set('alvos', \SiMCE\Classes\Alvos::getByID($target,true)->bean);
		$registro->set('recursos', \SiMCE\Classes\Recursos::getByID($resource,true)->bean);
		$registro->set('data', $datetime);
		$registro->set('tipo', $type);
		$registro->set('identificador', $agi->get('agi_callerid'));
		$registro->set('classificacao', \SiMCE\Classes\Registros::PRIORITY_NONE );
		$registro->set('estado', \SiMCE\Classes\Registros::STATUS_NEW );
		$registro->set('tamanho', $tamanho);
		$registro->set('attr', json_encode(array( 'tempo' => $duracao, 'voiceid' => 0 )) );
		$registro->set('observacoes', null);
		$registro->set('relato', null);
		if (!empty($dial_status)) {
			$registro->set('dial_status', $dial_status);
			$registro->set('dial_time',   $dial_time);
			$registro->set('answer_time', $answer_time);
			$registro->set('dial_number', $dial_number);
		}
		$registro->save();
		
		$agi->debug(">> Registro ID " . $registro->bean->id . " armazenado com sucesso!");
		
		// Atualiza a aloacação para status "free" (livre)
		$aloc = \SiMCE\Classes\Alocacoes::getByID( $allocation, true );
		$aloc->set('status', 'free');
		$aloc->save();
		
		// Cria a estrutura de diretório necessária
		$target_dir = __DIR__ . "/../records/{$organization}/{$operation}/{$target}";
		$old_mask = umask(0);
		@mkdir( $target_dir, 0777, true );
		@umask( $old_mask );
		
		// Cria a imagem em PNG
		$agi->debug(">> Gerando imagem PNG");
		@system( __DIR__ . "/wav2png -w 500 -h 100 --foreground-color=2e4562ff --background-color=00000000 -o $target_dir/" . $registro->bean->id . ".png $file");
		
		// Move o áudio para a estrutura final
		$agi->debug(">> Movendo arquivos para $target_dir");
		@rename( $file, "$target_dir/" . $registro->bean->id . ".wav" );

		// Atualiza a permissão do novo arquivo
		$agi->debug(">> Ajustando permissoes");
		@chmod( "$target_dir/" . $registro->bean->id . ".wav", 0777 );
		@chmod( "$target_dir/" . $registro->bean->id . ".png", 0777 );

		// Faz a segmentação do áudio e reconhecimento de voz
		//$agi->debug(">> Executando biometria de voz em background ...");
		//exec( __DIR__ . "/simce-voiceid.php " . $registro->bean->id . " >/dev/null 2>/dev/null &" );
			
	}
	
	// ----------------------------
	//
	// Interceptação de SMS (GSM)
	//
	// ----------------------------
	if ($type == \SiMCE\Classes\Recursos::TYPE_GSM) {
		
		
		// Verifica o tamanho
		$tamanho = strlen( $file );
		$agi->debug(">> Tamanho:  $tamanho bytes");
		
		// Verifica o encode
		if ($tamanho == 0) {
			$tmp = "";
			for ( $i = 0; $i < strlen($file); $i += 2 ) {
				$tmp .= chr( hexdec( substr( $file, $i, 2 )));
			}
			$agi->debug(">> UCS2:  " . mb_convert_encoding($tmp, "UTF-8", "UCS-2"));
			$agi->debug(">> UCS2 Size:  " . mb_strlen($tmp, "UTF-8"));
		}
                
		// Armazena o registro no banco de dados
		$registro = new \SiMCE\Classes\Registros();
		$registro->set('unidades', \SiMCE\Classes\Unidades::getByID($organization,true)->bean);
		$registro->set('operacoes', \SiMCE\Classes\Operacoes::getByID($operation,true)->bean);
		$registro->set('alvos', \SiMCE\Classes\Alvos::getByID($target,true)->bean);
		$registro->set('recursos', \SiMCE\Classes\Recursos::getByID($resource,true)->bean);
		$registro->set('data', \R::isoDateTime(time()));
		$registro->set('tipo', $type);
		$registro->set('identificador', $exten);
		$registro->set('classificacao', \SiMCE\Classes\Registros::PRIORITY_NONE );
		$registro->set('estado', \SiMCE\Classes\Registros::STATUS_NEW );
		$registro->set('tamanho', $tamanho);
		$registro->set('attr', json_encode(array( 'conteudo' => mb_convert_encoding( $file, "UTF-8", "ISO-8859-1") )));
		$registro->set('observacoes', null);
		$registro->set('relato', null);
		$registro->save();
		
		$agi->debug(">> Registro ID " . $registro->bean->id . " armazenado com sucesso!");
		
		// Atualiza a aloacação para status "free" (livre)
		$aloc = \SiMCE\Classes\Alocacoes::getByID( $allocation, true );
		$aloc->set('status', 'free');
		$aloc->save();
		
	}
	
}

?>
