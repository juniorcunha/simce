<?php

/**
 * Controlador responsável por exibir as alocações ativas em todas as operações
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2016
 */

namespace SiMCE\Alocacoes;

class Controller {

	/**
	 * Define as rotas do controlador
	 * 
	 * @param \SiMCE\UserInterface\Silex\Application $app
	 * @param string $prefix
	 * @return void
	 */
	static function factory( \Silex\Application $app, $prefix ) {
		$app->match( $prefix ,                array(__CLASS__,'index') );
		$app->match( $prefix . "gen_report/", array(__CLASS__,'genReport'));
	}
	
	/**
	 * Exibe a tela inicial
	 * 
	 * @param \Silex\Application $app
	 * @return $string
	 */
	static function index( \Silex\Application $app ) {

		// Conta a quantidade de recursos disponíveis
		$avails = \SiMCE\Classes\Recursos::getByQuery(
			" unidades_id = :unidade AND id NOT IN (SELECT DISTINCT recursos_id FROM alocacoes a, operacoes b WHERE :date BETWEEN a.inicio AND a.fim AND a.operacoes_id = b.id and b.unidades_id = :unidade)",
			array(
				'unidade' => $app['user']->getUnidade(),
				'date'    => date('Y-m-d')
			)
		);
		$availsStr = array();
		foreach($avails as $avail)
			$availStr[] = $avail['nome'];
		$availStr = implode(",", $availStr);

		// Mostra as alocações ativas por operação
		$ops = \SiMCE\Classes\Operacoes::getByQuery(
			" unidades_id = :unidade ",
			array(
				'unidade' => $app['user']->getUnidade()
			)
		);
		foreach( $ops as $idx => $operacao ) {

			$arr = \SiMCE\Classes\Alocacoes::getByQuery(
				" :date BETWEEN inicio AND fim AND operacoes_id = :operacao ",
				array(
					'date'     => date('Y-m-d'),
					'operacao' => $operacao['id']
				),
				true
			);
			if (empty($arr)) { unset($ops[$idx]); continue; }

			$rows = array();
			foreach( $arr as $a ) {
				$id = $a->bean->alvos->nome;
				if (empty($rows[ $id ])) {
					$cfg = array(
						'id'   => $a->bean->alvos->id,
						'nome' => $a->bean->alvos->nome
					);
					$tPerm = array();
					foreach( $a->bean->alvos->ownPermissoes as $p ) {
						//print $p->usuarios->nome . PHP_EOL;
						$tPerm[] = $p->usuarios->nome . " (" . $p->cargos->nome . ")";
					}
					$cfg['permissoes'] = (!empty($tPerm)) ?implode(",", $tPerm) : "-";
					$rows[ $id ] = $cfg;
				}
				$rows[$id]['alocacoes'][] = array(
					'inicio'        => $a->get('inicio'),
					'fim'           => $a->get('fim'),
					'vara'          => $a->get('oficio'),
					'recurso'       => $a->bean->recursos->nome,
					'identificacao' => $a->get('identificacao'),
					'desvio'        => (!empty($a->bean->desvio_para) ? (\SiMCE\Classes\Alvos::getByID($a->get('desvio_para'),true)->get('nome') . " - " . \SiMCE\Classes\Alvos::getByID($a->get('desvio_para'),true)->get('telefone')) : "-"),
					'desvio_via'    => $a->get('desvio_via')
				);

			}
			ksort( $rows );
			$ops[$idx]['rows'] = $rows;

		}
		
		// Resultado
		return $app->json(
			array(
				'success'   => true,
				'error'     => false,
				'content'   => $app['twig']->render(
					basename(__DIR__) . '/Views/index.html', array(
						'ops'      => $ops,
						'avails'   => count($avails),
						'availstr' => $availStr,
						'content'  => rawurlencode(serialize($ops))
					)
				),
				'onLoad'    => false,
				'functions' => $app['twig']->render( basename(__DIR__) . '/Views/functions.js' ),
				'onUnload'  => false
			)
		);
			
	}

	/**
	 * Gera o PDF 
	 * 
	 * @param \Silex\Application $app
	 * @return string
	 */
	static function genReport( \Silex\Application $app ) {
		
		// Obtem as informações enviadas no formulário
		$data = $app['request']->get('data');
		$arr  = unserialize(rawurldecode($data));
	
		// Prepara o PDF
		$pdf = new \SiMCE\Classes\PDF( 'P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// Adiciona HTML
		$pdf->AddPage();
		$pdf->writeHTML(
			$app['twig']->render(
				basename(__DIR__) . '/Views/report.html',
				array( 'ops' => $arr )
			),
			true, 0, true, 0
		);
		
		// Gera o PDF
		$pdf_file = '/SiMCE-AC-' . microtime(true) . '.pdf';
		$pdf->Output( SIMCE_CACHE_DIR . $pdf_file, 'F' );
		
		// Auditoria
		$app['audit']->log( 'report_pdf', array( 'success' => true ));
		
		// Informa a mensagem para o usuário
		return $app->json( array(
			'success' => true,
			'file'    => '/simce/cache' . $pdf_file
		));
		
	}

	
}

?>
