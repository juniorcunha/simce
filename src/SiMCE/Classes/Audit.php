<?php

/**
 * Classe para fazer a auditoria das ações
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class Audit extends DataRecord {
	
	public static $beanName = "audit";

	public static $formatCallbacks = array(
		'data'        => 'self::dbToDateTime',
		'usuarios_id' => '\SiMCE\Classes\Audit::formatID',
		'acao'        => '\SiMCE\Classes\Audit::formatAction',
		'param'       => '\SiMCE\Classes\Audit::formatParams'
	);
	
	/**
	 * Função para fazer o log da ação realizada
	 * 
	 * @param string $action
	 * @param mixed $param
	 * @return void
	 */
	function log( $action, $param ) {
		
		global $app;

		/**
		 * Tabela de ações
		 * 
		 * :: Interface ::
		 * user_login - Login do usuário
		 * user_logout - Logout do usuário
		 * user_profile - Abre o perfil de usuário
		 * user_profile_save - Salva o perfil do usuário
		 * 
		 * :: Alvos ::
		 * target_form - Formulário de adição/edição de alvos
		 * target_list - Lista de alvos da operação
		 * target_save - Salva o alvo
		 * target_aloc_save - Salva alocação no alvo
		 * target_aloc_remove - Remove alocação do alvo
		 * target_aloc_form - Carrega o formulário da alocação
		 * target_permission_save - Adiciona permissão para o alvo
		 * target_permission_remove - Remove permissão para o alvo
		 * target_remove - Remove o alvo
		 * 
		 * :: Cargos e Perfis ::
		 * profile_form - Formulário para adição/edição de alvos
		 * profile_list - Lista os cargos/perfis da unidade
		 * profile_save - Salva o perfil
		 * profile_remove - Remove o perfil
		 * 
		 * :: Configurações ::
		 * config_load - Carrega as configurações atuais da unidade
		 * config_load - Salva as configurações
		 * 
		 * :: Contatos ::
		 * contact_form - Formulário para adição/edição dos alvos
		 * contact_list - Lista os contatos da operação
		 * contact_save - Salva o contato
		 * contact_remove - Remove o contato
		 * 
		 * :: Dashboard ::
		 * dashboard_load - Carrega o dashboard
		 * dashboard_active_interceptions - Carrega as interceptações ativas da operação
		 * dashboard_interception_history - Carrega o histórico de interceptações por tipo
		 * 
		 * :: Interceptações ::
		 * interception_list - Lista as interceptações
		 * interception_online - Exibe o painel online
		 * interception_details - Exibe os detalhes do registro
		 * interception_play - Reproduz determinado registro
		 * interception_play_grid - Reproduz determinado registro diretamente do grid
		 * interception_save - Salva as informações do registro
		 * interception_seg_save - Salva as informaçoes do segmento
		 * interception_contact_new - Adiciona um novo contato pela interface de registro
		 * interception_voiceid - Identifica a voz de um locutor
		 * interception_download - Download do registro
		 * interception_play_online - Escuta online
		 * 
		 * :: Rede de Relacionamentos ::
		 * network_load - Exibe a rede de relacionamentos da operação
		 * network_details - Exibe os detalhes dos envolvidos
		 * 
		 * :: Operações ::
		 * operation_form - Formulário de adição/edição de operações
		 * operation_list - Exibe a lista de operações
		 * operation_save - Salva/Atualiza uma operação
		 * operation_remove - Remove operação
		 * 
		 * :: Recursos ::
		 * resources_form - Carrega o formulário de edição de recursos
		 * resources_save - Salva os recursos de determinada unidade
		 * 
		 * :: Relatórios ::
		 * report_menu - Exibe o menu de relatórios
		 * report_process - Processa o relatório
		 * report_judicial_save - Salva o relatorio judicial
		 * report_judicial_pdf - Gera o pdf do relatório judicial
		 * 
		 * :: Timeline ::
		 * timeline_load - Carrega a timeline
		 * timeline_save - Salva os dados na timeline
		 * 
		 * :: Unidades ::
		 * unit_form - Formulário para adição/edição de unidades
		 * unit_list - Lista as unidades organizacionais
		 * unit_save - Salva a unidade organizacional
		 * unit_remove - Remove a unidade organizacional
		 * 
		 * :: Usuários ::
		 * user_form - Formulário para adição/edição de usuários
		 * user_list - Lista os usuários do sistema
		 * user_save - Salva o usuário
		 * user_remove - Remove o usuário
		 * 
		 */
		
		$log = new Audit();
		$log->set('data', date('Y-m-d H:i:s'));
		if ($app['user'] !== null) {
			$log->set('usuarios', $app['user']->bean);
			$log->set('unidades', $app['user']->bean->unidades);
		}
		$log->set('ip', $_SERVER['REMOTE_ADDR']);
		$log->set('acao', $action);
		$log->set('param', json_encode($param));
		$log->save();
		
	}
	
	/**
	 * Recebe um id e retorna o nome do usuario
	 * 
	 * @param int $id
	 * @return string
	 */
	public static function formatID( $id ) {
		if (!empty($id))
			return Usuarios::getByID($id,true)->get('nome');
		else
			return '-';
	}
	
	/**
	 * Formata a ação que foi executada
	 * 
	 * @param string $action
	 * @return string
	 */
	public static function formatAction( $action ) {

		switch ($action) {
			
			// Interface
			case 'user_login':
				$action = '<span class="cus-information"></span> Login do usuário';
				break;
			case 'user_logout':
				$action = '<span class="cus-information"></span> Logout do usuário';
				break;
			case 'user_profile':
				$action = '<span class="cus-information"></span> Abre o perfil de usuário';
				break;
			case 'user_profile_save':
				$action = '<span class="cus-information"></span> Salva o perfil do usuário';
				break;

			// Alvos
			case 'target_form':
				$action = '<span class="cus-group-error"></span> Formulário de adição/edição de alvos';
				break;
			case 'target_list':
				$action = '<span class="cus-group-error"></span> Lista de alvos da operação';
				break;
			case 'target_save':
				$action = '<span class="cus-group-error"></span> Salva o alvo';
				break;
			case 'target_aloc_save':
				$action = '<span class="cus-group-error"></span> Salva alocação no alvo';
				break;
			case 'target_aloc_remove':
				$action = '<span class="cus-group-error"></span> Remove alocação do alvo';
				break;
			case 'target_aloc_form':
				$action = '<span class="cus-group-error"></span> Carrega o formulário da alocação';
				break;
			case 'target_permission_save':
				$action = '<span class="cus-group-error"></span> Adiciona permissão para o alvo';
				break;
			case 'target_permission_remove':
				$action = '<span class="cus-group-error"></span> Remove permissão para o alvo';
				break;
			case 'target_remove':
				$action = '<span class="cus-group-error"></span> Remove o alvo';
				break;
			
			// Cargos e Perfis
			case 'profile_form':
				$action = '<span class="cus-table-edit"></span> Formulário para adição/edição de cargos/perfil';
				break;
			case 'profile_list':
				$action = '<span class="cus-table-edit"></span> Lista os cargos/perfis da unidade';
				break;
			case 'profile_save':
				$action = '<span class="cus-table-edit"></span> Salva o cargo/perfil';
				break;
			case 'profile_remove':
				$action = '<span class="cus-table-edit"></span> Remove o cargo/perfil';
				break;
			
			// Configurações
			case 'config_load':
				$action = '<span class="cus-application-form"></span> Carrega as configurações atuais da unidade';
				break;
			case 'config_save':
				$action = '<span class="cus-application-form"></span> Salva as configurações';
				break;
			
			// Contatos
			case 'contact_form':
				$action = '<span class="cus-group-link"></span> Formulário para adição/edição dos interlocutores';
				break;
			case 'contact_list':
				$action = '<span class="cus-group-link"></span> Lista os interlocutores da operação';
				break;
			case 'contact_save':
				$action = '<span class="cus-group-link"></span> Salva o interlocutor';
				break;
			case 'contact_remove':
				$action = '<span class="cus-group-link"></span> Remove o interlocutor';
				break;
			
			// Dashboard
			case 'dashboard_load':
				$action = '<span class="cus-chart-curve"></span> Carrega o dashboard';
				break;
			case 'dashboard_active_interceptions':
				$action = '<span class="cus-chart-curve"></span> Carrega as interceptações ativas da operação';
				break;
			case 'dashboard_interception_history':
				$action = '<span class="cus-chart-curve"></span> Carrega o histórico de interceptações por tipo';
				break;
			
			// Interceptações
			case 'interception_list':
				$action = '<span class="cus-table-multiple"></span> Lista as interceptações';
				break;
			case 'interception_online':
				$action = '<span class="cus-table-multiple"></span> Exibe o painel online';
				break;
			case 'interception_details':
				$action = '<span class="cus-table-multiple"></span> Exibe os detalhes do registro';
				break;
			case 'interception_play':
				$action = '<span class="cus-table-multiple"></span> Reproduz determinado registro';
				break;
			case 'interception_play_grid':
				$action = '<span class="cus-table-multiple"></span> Reproduz determinado registro diretamente do grid';
				break;
			case 'interception_save':
				$action = '<span class="cus-table-multiple"></span> Salva as informações do registro';
				break;
			case 'interception_seg_save':
				$action = '<span class="cus-table-multiple"></span> Salva as informaçoes do segmento';
				break;
			case 'interception_contact_new':
				$action = '<span class="cus-table-multiple"></span> Adiciona uma novo interlocutor pela interface de registro';
				break;
			case 'interception_voiceid':
				$action = '<span class="cus-table-multiple"></span> Identifica a voz de um locutor';
				break;
			case 'interception_download':
				$action = '<span class="cus-table-multiple"></span> Download do registro';
				break;
			case 'interception_play_online':
				$action = '<span class="cus-table-multiple"></span> Escuta online';
				break;
			
			// Rede de relacionamentos
			case 'network_load':
				$action = '<span class="cus-chart-organisation"></span> Exibe a rede de relacionamentos da operação';
				break;
			case 'network_details':
				$action = '<span class="cus-chart-organisation"></span> Exibe os detalhes do interlocutor';
				break;
			
			// Operações
			case 'operation_form':
				$action = '<span class="cus-cog"></span> Formulário de adição/edição de operações';
				break;
			case 'operation_list':
				$action = '<span class="cus-cog"></span> Exibe a lista de operações';
				break;
			case 'operation_save':
				$action = '<span class="cus-cog"></span> Salva/Atualiza a operação';
				break;
			case 'operation_remove':
				$action = '<span class="cus-cog"></span> Remove a operação';
				break;
			
			// Recursos
			case 'resources_form':
				$action = '<span class="cus-plugin-go"></span> Carrega o formulário de edição de recursos';
				break;
			case 'resources_save':
				$action = '<span class="cus-plugin-go"></span> Salva os recursos da unidade';
				break;
			
			// Relatórios
			case 'report_menu':
				$action = '<span class="cus-report"></span> Exibe o menu de relatórios';
				break;
			case 'report_process':
				$action = '<span class="cus-report"></span> Processa o relatório';
				break;
			case 'report_judicial_save':
				$action = '<span class="cus-report"></span> Salva o relatório judicial';
				break;
			case 'report_judicial_pdf':
				$action = '<span class="cus-report"></span> Gera o PDF do relatório judicial';
				break;
			
			// Timeline
			case 'timeline_load':
				$action = '<span class="cus-timeline-marker"></span> Carrega a cronologia de eventos';
				break;
			case 'timeline_save':
				$action = '<span class="cus-timeline-marker"></span> Adiciona um elemento na cronologia de eventos';
				break;
			
			// Unidades
			case 'unit_form':
				$action = '<span class="cus-world"></span> Formulário para adição/edição de unidades';
				break;
			case 'unit_list':
				$action = '<span class="cus-world"></span> Lista as unidades organizacionais';
				break;
			case 'unit_save':
				$action = '<span class="cus-world"></span> Salva a unidade organizacional';
				break;
			case 'unit_remove':
				$action = '<span class="cus-world"></span> Remove a unidade organizacional';
				break;

			// Usuários
			case 'user_form':
				$action = '<span class="cus-user"></span> Formulário para adição/edição de usuários';
				break;
			case 'user_list':
				$action = '<span class="cus-user"></span> Lista os usuários da unidade';
				break;
			case 'user_save':
				$action = '<span class="cus-user"></span> Salva o usuário';
				break;
			case 'user_remove':
				$action = '<span class="cus-user"></span> Remove o usuário';
				break;

			default: 
				$action = '<span class="cus-information"></span> ' . $action;
				break;
			
		}

		return $action;
		
	}
	
	/**
	 * Formata os parametros
	 * 
	 * @param string $param
	 * @return string
	 */
	public static function formatParams( $param, $arr ) {

		$obj    = json_decode($param);
		$action = array();
		$classificacao = array( "Nenhuma", "Baixa Prioridade", "Média Prioridade", "Alta Prioridade" );
		$estado = array( "Novo", "Visualizada", "Com Breve Relato ou Observação" );

		switch ($arr['orig_acao']) {
			
			// Interface
			case 'user_login':
				break;
			case 'user_logout':
				break;
			case 'user_profile':
				break;
			case 'user_profile_save':
				break;

			// Alvos
			case 'target_form':
				if (!empty($obj->data)) {
					$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data->operacoes_id,true )->get('nome');
					$action[] = "<b>Alvo: </b>" . $obj->data->nome;
					$action = implode("<br>", $action);
				}
				break;
			case 'target_list':
				$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data, true )->get('nome');
				$action = implode("<br>", $action);
				break;
			case 'target_save':
				$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data->operacoes_id,true )->get('nome');
				$action[] = "<b>Alvo: </b>" . $obj->data->nome;
				$action = implode("<br>", $action);
				break;
			case 'target_aloc_save':
				$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data->operacoes_id,true )->get('nome');
				$action[] = "<b>Alvo: </b>" . \SiMCE\Classes\Alvos::getByID( $obj->data->alvos_id,true )->get('nome');
				$action[] = "<b>Início: </b>" . $obj->data->inicio;
				$action[] = "<b>Fim: </b>" . $obj->data->fim;
				$action[] = "<b>Identificação: </b>" . $obj->data->identificacao;
				$action[] = "<b>Ofício: </b>" . $obj->data->oficio;
				$action[] = "<b>Recurso: </b>" . \SiMCE\Classes\Recursos::getByID( $obj->data->recursos_id,true )->get('nome');
				$action = implode("<br>", $action);
				break;
			case 'target_aloc_remove':
				$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data->operacoes_id,true )->get('nome');
				$action[] = "<b>Alvo: </b>" . \SiMCE\Classes\Alvos::getByID( $obj->data->alvos_id,true )->get('nome');
				$action[] = "<b>Início: </b>" . $obj->data->inicio;
				$action[] = "<b>Fim: </b>" . $obj->data->fim;
				$action[] = "<b>Identificação: </b>" . $obj->data->identificacao;
				$action[] = "<b>Ofício: </b>" . $obj->data->oficio;
				$action[] = "<b>Recurso: </b>" . \SiMCE\Classes\Recursos::getByID( $obj->data->recursos_id,true )->get('nome');
				$action = implode("<br>", $action);
				break;
			case 'target_aloc_form':
				break;
			case 'target_permission_save':
				$action[] = "<b>Alvo: </b>" . \SiMCE\Classes\Alvos::getByID( $obj->data->alvos_id,true )->get('nome');
				$action[] = "<b>Usuário: </b>" . \SiMCE\Classes\Usuarios::getByID( $obj->data->usuarios_id,true )->get('nome');
				$action[] = "<b>Cargo: </b>" . \SiMCE\Classes\Cargos::getByID( $obj->data->cargos_id,true )->get('nome');
				$action = implode("<br>", $action);
				break;
			case 'target_permission_remove':
				$action[] = "<b>Alvo: </b>" . \SiMCE\Classes\Alvos::getByID( $obj->data->alvos_id,true )->get('nome');
				$action[] = "<b>Usuário: </b>" . \SiMCE\Classes\Usuarios::getByID( $obj->data->usuarios_id,true )->get('nome');
				$action[] = "<b>Cargo: </b>" . \SiMCE\Classes\Cargos::getByID( $obj->data->cargos_id,true )->get('nome');
				$action = implode("<br>", $action);
				break;
			case 'target_remove':
				$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data->operacoes_id,true )->get('nome');
				$action[] = "<b>Alvo: </b>" . $obj->data->nome;
				$action = implode("<br>", $action);
				break;
			
			// Cargos e Perfis
			case 'profile_form':
				if (!empty($obj->data)) {
					$action[] = "<b>Cargo: </b>" . $obj->data->nome;
					$action[] = "<b>Descrição: </b>" . $obj->data->descricao;
					$action = implode("<br>", $action);
				}
				break;
			case 'profile_list':
				break;
			case 'profile_save':
				$action[] = "<b>Cargo: </b>" . $obj->data->nome;
				$action[] = "<b>Descrição: </b>" . $obj->data->descricao;
				$action = implode("<br>", $action);
				break;
			case 'profile_remove':
				$action[] = "<b>Cargo: </b>" . $obj->data->nome;
				$action[] = "<b>Descrição: </b>" . $obj->data->descricao;
				$action = implode("<br>", $action);
				break;
			
			// Configurações
			case 'config_load':
				break;
			case 'config_save':
				break;
			
			// Contatos
			case 'contact_form':
				if (!empty($obj->data)) {
					$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data->operacoes_id,true )->get('nome');
					$action[] = "<b>Interlocutor: </b>" . $obj->data->nome;
					$action = implode("<br>", $action);
				}
				break;
			case 'contact_list':
				$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data, true )->get('nome');
				$action = implode("<br>", $action);
				break;
			case 'contact_save':
				$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data->operacoes_id,true )->get('nome');
				$action[] = "<b>Interlocutor: </b>" . $obj->data->nome;
				$action = implode("<br>", $action);
				break;
			case 'contact_remove':
				$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data->operacoes_id,true )->get('nome');
				$action[] = "<b>Interlocutor: </b>" . $obj->data->nome;
				$action = implode("<br>", $action);
				break;
			
			// Dashboard
			case 'dashboard_load':
				break;
			case 'dashboard_active_interceptions':
				$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data->operacao,true )->get('nome');
				$action = implode("<br>", $action);
				break;
			case 'dashboard_interception_history':
				$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data->operacao,true )->get('nome');
				$action = implode("<br>", $action);
				break;
			
			// Interceptações
			case 'interception_list':
				$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data->operacao,true )->get('nome');
				$action = implode("<br>", $action);
				break;
			case 'interception_online':
				$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data->operacao,true )->get('nome');
				$action = implode("<br>", $action);
				break;
			case 'interception_details':
				$action[] = "<b>Registro: </b>" . $obj->data;
				$action = implode("<br>", $action);
				break;
			case 'interception_play':
				$action[] = "<b>Registro: </b>" . $obj->data->id;
				$action[] = "<b>Início: </b>" . gmdate("H:i:s", $obj->data->start/1000);
				$action[] = "<b>Fim: </b>" . gmdate("H:i:s", $obj->data->end/1000);
				$action = implode("<br>", $action);
				break;
			case 'interception_play_grid':
				$action[] = "<b>Registro: </b>" . $obj->data->id;
				$action = implode("<br>", $action);
				break;
			case 'interception_save':
				$action[] = "<b>Registro: </b>" . $obj->data->id;
				$action[] = "<b>Classificação: </b>" . $classificacao[ $obj->data->classificacao ];
				$action[] = "<b>Estado: </b>" . $estado[ $obj->data->estado ];
				$action[] = "<b>Breve Relato: </b>" . $obj->data->relato;
				$action[] = "<b>Observações: </b>" . $obj->data->observacoes;
				$action = implode("<br>", $action);
				break;
			case 'interception_seg_save':
				$action[] = "<b>Registro: </b>" . $obj->data->registros_id;
				$action[] = "<b>Início: </b>" . $obj->data->inicio;
				$action[] = "<b>Fim: </b>" . $obj->data->final;
				$action[] = "<b>Transcrição: </b>" . $obj->data->transcricao;
				$action = implode("<br>", $action);
				break;
			case 'interception_contact_new':
				$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data->operacoes_id,true )->get('nome');
				$action[] = "<b>Interlocutor: </b>" . $obj->data->nome;
				$action = implode("<br>", $action);
				break;
			case 'interception_voiceid':
				$seg = array_pop( \SiMCE\Classes\Segmentos::getByQuery(" voiceid_id = :id ", array( "id" => $obj->data->voiceid->id ), true ) );
				$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data->contato->operacoes_id,true )->get('nome');
				$action[] = "<b>Registro: </b>" . $seg->get('registros_id');
				$action[] = "<b>Interlocutor: </b>" . $obj->data->contato->nome;
				$action[] = "<b>Início: </b>" . $seg->get('inicio');
				$action[] = "<b>Fim: </b>" . $seg->get('final');
				$action = implode("<br>", $action);
				break;
			case 'interception_download':
				$action[] = "<b>Registro: </b>" . $obj->data;
				$action = implode("<br>", $action);
				break;
			case 'interception_play_online':
				$aloc =  \SiMCE\Classes\Alocacoes::getByID( $obj->data, true );
				$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $aloc->get('operacoes_id'), true )->get('nome');
				$action[] = "<b>Alvo: </b>" . \SiMCE\Classes\Alvos::getByID( $aloc->get('alvos_id'), true )->get('nome');
				$action = implode("<br>", $action);
				break;
			
			// Rede de relacionamentos
			case 'network_load':
				$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data->operacao,true )->get('nome');
				$action = implode("<br>", $action);
				break;
			case 'network_details':
				$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data->obj->operacoes_id, true )->get('nome');
				$action[] = "<b>Alvo: </b>" . $obj->data->obj->nome;
				$action = implode("<br>", $action);
				break;
			
			// Operações
			case 'operation_form':
				if (!empty($obj->data)) {
					$action[] = "<b>Operação: </b>" . $obj->data->nome;
					$action = implode("<br>", $action);
				}
				break;
			case 'operation_list':
				break;
			case 'operation_save':
				$action[] = "<b>Operação: </b>" . $obj->data->nome;
				$action[] = "<b>Início: </b>" . $obj->data->inicio;
				$action[] = "<b>Fim: </b>" . $obj->data->fim;
				$action[] = "<b>Vara: </b>" . $obj->data->vara;
				$action[] = "<b>Autos: </b>" . $obj->data->autos;
				$action = implode("<br>", $action);
				break;
			case 'operation_remove':
				$action[] = "<b>Operação: </b>" . $obj->data->nome;
				$action[] = "<b>Início: </b>" . $obj->data->inicio;
				$action[] = "<b>Fim: </b>" . $obj->data->fim;
				$action[] = "<b>Vara: </b>" . $obj->data->vara;
				$action[] = "<b>Autos: </b>" . $obj->data->autos;
				$action = implode("<br>", $action);
				break;
			
			// Recursos
			case 'resources_form':
				break;
			case 'resources_save':
				break;
			
			// Relatórios
			case 'report_menu':
				break;
			case 'report_process':
				$action[] = "<b>Tipo: </b>" . $obj->data->tipo;
				$action[] = "<b>Início: </b>" . $obj->data->inicio;
				$action[] = "<b>Fim: </b>" . $obj->data->fim;
				$action = implode("<br>", $action);
				break;
			case 'report_judicial_save':
				$action[] = "<b>Relatório: </b>" . $obj->data->report->nome;
				$action = implode("<br>", $action);
				break;
			case 'report_judicial_pdf':
				$action[] = "<b>Relatório: </b>" . $obj->data->nome;
				$action = implode("<br>", $action);
				break;
			
			// Timeline
			case 'timeline_load':
				$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data,true )->get('nome');
				$action = implode("<br>", $action);
				break;
			case 'timeline_save':
				$action[] = "<b>Operação: </b>" . \SiMCE\Classes\Operacoes::getByID( $obj->data->operacao,true )->get('nome');
				$action[] = "<b>Nome: </b>" . $obj->data->obj->nome;
				$action[] = "<b>Data: </b>" . $obj->data->obj->data;
				$action = implode("<br>", $action);
				break;
			
			// Unidades
			case 'unit_form':
				if (!empty($obj->data)) {
					$action[] = "<b>Unidade: </b>" . $obj->data->nome;
					$action = implode("<br>", $action);
				}
				break;
			case 'unit_list':
				break;
			case 'unit_save':
				$action[] = "<b>Unidade: </b>" . $obj->data->nome;
				$action[] = "<b>Endereço: </b>" . $obj->data->endereco;
				$action[] = "<b>Cidade: </b>" . $obj->data->cidade . " - " . $obj->data->estado;
				$action[] = "<b>Telefone: </b>" . $obj->data->telefone;
				$action = implode("<br>", $action);
				break;
			case 'unit_remove':
				$action[] = "<b>Unidade: </b>" . $obj->data->nome;
				$action = implode("<br>", $action);
				break;

			// Usuários
			case 'user_form':
				if (!empty($obj->data)) {
					$action[] = "<b>Usuário: </b>" . $obj->data->nome;
					$action = implode("<br>", $action);
				}
				break;
			case 'user_list':
				$action[] = "<b>Unidade: </b>" . \SiMCE\Classes\Unidades::getByID( $obj->data, true )->get('nome');
				$action = implode("<br>", $action);
				break;
			case 'user_save':
				$action[] = "<b>Usuário: </b>" . $obj->data->nome;
				$action[] = "<b>Login: </b>" . $obj->data->login;
				$action[] = "<b>E-mail: </b>" . $obj->data->email;
				$action[] = "<b>Telefone: </b>" . $obj->data->telefone;
				$action = implode("<br>", $action);
				break;
			case 'user_remove':
				$action[] = "<b>Usuário: </b>" . $obj->data->nome;
				$action = implode("<br>", $action);
				break;

		}
		return (!empty($action)) ? $action : "-";
		
	}
	
}
