<?php

include_once( '/opt/simce/index.php' );

global $app;

$operacao = \SiMCE\Classes\Operacoes::getByID(9);
$inicio = '2014-01-01';
$fim = '2016-02-28';

// Obtem os alvos
$alvos   = \SiMCE\Classes\Alvos::getByQuery( " operacoes_id = :operacao ", array( "operacao" => $operacao['id'] ) );

// Define o total de registros da operação
$opt = array(
	'total' => \SiMCE\Classes\Registros::getCount(
		" operacoes_id = :operacao AND data BETWEEN :inicio AND :fim ",
		array(
			"operacao" => $operacao['id'],
			"inicio" => $inicio,
			"fim" => $fim
		)
	)
);

// Cria um array com as propriedades desejadas ( chave = query )
$prop = array(
	'highPrio' => 'classificacao = ' . \SiMCE\Classes\Registros::PRIORITY_HIGH,
	'medPrio'  => 'classificacao = ' . \SiMCE\Classes\Registros::PRIORITY_MEDIUM,
	'lowPrio'  => 'classificacao = ' . \SiMCE\Classes\Registros::PRIORITY_LOW,
	'noPrio'   => 'classificacao = ' . \SiMCE\Classes\Registros::PRIORITY_NONE,
	'new'      => 'estado = ' . \SiMCE\Classes\Registros::STATUS_NEW,
	'view'     => 'estado = ' . \SiMCE\Classes\Registros::STATUS_VIEWED,
	'relato'   => 'LENGTH(relato) > 0',
	'obs'      => 'LENGTH(observacoes) > 0',
	'trans'    => 'id IN ( SELECT DISTINCT registros_id FROM segmentos WHERE LENGTH(transcricao) > 0 )'
);

// Percorre as propriedades obten os totais gerais da operação
foreach( $prop as $key => $query ) {

	// Obtem o valor
	$opt[$key] = \SiMCE\Classes\Registros::getCount(
		" operacoes_id = :operacao AND $query AND data BETWEEN :inicio AND :fim ",
		array(
			"operacao" => $operacao['id'],
			"inicio" => $inicio,
			"fim" => $fim
		)
	);
	$opt[$key . "Perc"] = ($opt['total']) ? sprintf("%.3f", ( $opt[$key] * 100 ) / $opt['total'] ) : 0;

}

// Percorre os alvos para obter as estatisticas por alvo
foreach( $alvos as $alvo ) {

	// Define o total de registros do alvo
	$alvo['total'] = \SiMCE\Classes\Registros::getCount(
		" operacoes_id = :operacao AND data BETWEEN :inicio AND :fim AND alvos_id = {$alvo['id']}",
		array(
			"operacao" => $operacao['id'],
			"inicio" => $inicio,
			"fim" => $fim
		)
	);

	// Percorre as propriedades obten os totais gerais da operação
	foreach( $prop as $key => $query ) {

		// Obtem o valor
		$alvo[$key] = \SiMCE\Classes\Registros::getCount(
			" operacoes_id = :operacao AND $query AND alvos_id = {$alvo['id']} AND data BETWEEN :inicio AND :fim ",
			array(
				"operacao" => $operacao['id'],
				"inicio" => $inicio,
				"fim" => $fim
			)
		);
		$alvo[$key . "Perc"] = ($alvo['total']) ? sprintf("%.3f", ( $alvo[$key] * 100 ) / $alvo['total'] ) : 0;

	}

	$opt['alvos'][] = $alvo;


}

?>
