<?php

/**
 * Classe pare acesso aos usuários do sistema
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class Usuarios extends DataRecord {
	
	public static $beanName = "usuarios";
	
	/**
	 * Valida as informações do formulário
	 * 
	 * @return boolean
	 */
	protected function validate() {

		parent::validate();
		$is_valid = true;
		
		// Nome
		if (empty($this->bean->nome)) {
			$this->invalidFields[] = "nome";
			$is_valid = false;
		}
		
		// Login
		if (empty($this->bean->login)) {
			$this->invalidFields[] = "login";
			$is_valid = false;
		}
		
		// Email
		if (empty($this->bean->email)) {
			$this->invalidFields[] = "email";
			$is_valid = false;
		} else {
			if (filter_var($this->bean->email, FILTER_VALIDATE_EMAIL) === false) {
				$this->invalidFields[] = "email";
				$is_valid = false;
			}
		}
		
		// Senha
		if (empty($this->bean->password) || empty($this->bean->password2)) {
			$this->invalidFields[] = "password";
			$this->invalidFields[] = "password2";
			$is_valid = false;
		} else {
			if ($this->bean->password != $this->bean->password2) {
				$this->invalidFields[] = "password";
				$this->invalidFields[] = "password2";
				$is_valid = false;
			} else {
				unset($this->bean->password2);
				if (!preg_match('/^[a-f0-9]{32}$/', $this->bean->password))
					$this->set('password', md5( $this->bean->password ));
			}
		}
		
		return $is_valid;
		
	}
	
	/**
	 * Informa se o usuário é do tipo super administrador
	 * 
	 * @return boolean
	 */
	public function isSuperAdmin() {
		return ($this->get('tipo') == 'S') ? true : false;
	}
	
	/**
	 * Informa se o usuário é do tipo administrador
	 * 
	 * @return boolean
	 */
	public function isAdmin() {
		return ($this->get('tipo') == 'A') ? true : false;
	}
	
	/**
	 * Informa se o usuário é do tipo operador
	 * 
	 * @return boolean
	 */
	public function isOperator() {
		return ($this->get('tipo') == 'O') ? true : false;
	}
	
	/**
	 * Informa a unidade que o usuário pertence
	 * 
	 * @return int
	 */
	public function getUnidade() {
		return $this->get('unidades_id');
	}
	
	/**
	 * Verifica se determinada ação está disponível para a operação informada
	 * 
	 * @param string $action
	 * @param int $operation
	 * @return boolean
	 */
	public function hasPermissionByOperation( $operation, $action ) {
		
		// Obtem a lista de alvos da operação
		$alvos = Alvos::getByQuery(
			" operacoes_id = :id ",
			array(
				"id" => $operation
			),
			true
		);
		foreach( $alvos as $alvo ) {
		
			// Obtem as permissões para o usuário e para o alvo
			$perms = Permissoes::getByQuery(
				" alvos_id = :alvo AND usuarios_id = :usuario ",
				array(
					"alvo"    => $alvo->get("id"),
					"usuario" => $this->get("id")
				),
				true
			);
			foreach( $perms as $perm ) {
				
				$aTmp = explode(",", str_replace('"','',$perm->bean->cargos->acao) );
				if ( count(preg_grep("/$action/", $aTmp)) ) {
					return true;
				}
				
			}
			
		}
		
		return false;
		
	}
	
	/**
	 * Verifica se determinada ação está disponível para o alvo informado
	 * 
	 * @param string $action
	 * @param int $target
	 * @return boolean
	 */
	public function hasPermissionByTarget( $target, $action ) {
		
		
		// Obtem as permissões para o usuário e para o alvo
		$perms = Permissoes::getByQuery(
			" alvos_id = :alvo AND usuarios_id = :usuario ",
			array(
				"alvo"    => $target,
				"usuario" => $this->get("id")
			),
			true
		);
		foreach( $perms as $perm ) {

			$aTmp = explode(",", str_replace('"','',$perm->bean->cargos->acao) );
			if ( count(preg_grep("/$action/", $aTmp)) ) {
				return true;
			}

		}

		
		return false;
		
	}
	
	/**
	 * Retorna a lista de alvos que o usuário pode visualizar na operação por ação
	 * 
	 * @param int $operation
	 * @param string $action
	 * @return mixed
	 */
	public function getTargetsByAction( $operation, $action ) {
		
		$targets = array( -1 );
		
		// Obtem a lista de alvos da operação
		$alvos = Alvos::getByQuery(
			" operacoes_id = :id ",
			array(
				"id" => $operation
			),
			true
		);
		foreach( $alvos as $alvo ) {
		
			// Obtem as permissões para o usuário e para o alvo
			$perms = Permissoes::getByQuery(
				" alvos_id = :alvo AND usuarios_id = :usuario ",
				array(
					"alvo"    => $alvo->get("id"),
					"usuario" => $this->get("id")
				),
				true
			);
			foreach( $perms as $perm ) {
				
				$aTmp = explode(",", str_replace('"','',$perm->bean->cargos->acao) );
				//if ( in_array( $action, $aTmp ) === true ){
				if ( count(preg_grep("/$action/", $aTmp)) ) {
					$targets[] = $alvo->get("id");
				}
				
			}
			
		}
		
		return $targets;
		
	}
	
	/**
	 * Verifica se possui alguma permissão do tipo informado
	 * 
	 * @param int $operation
	 * @param string $action
	 * @return mixed
	 */
	public function hasPermissionByAction( $operation, $action ) {
		
		$hasPermission = false;
		
		// Obtem a lista de alvos da operação
		$alvos = Alvos::getByQuery(
			" operacoes_id = :id ",
			array(
				"id" => $operation
			),
			true
		);
		foreach( $alvos as $alvo ) {
		
			// Obtem as permissões para o usuário e para o alvo
			$perms = Permissoes::getByQuery(
				" alvos_id = :alvo AND usuarios_id = :usuario ",
				array(
					"alvo"    => $alvo->get("id"),
					"usuario" => $this->get("id")
				),
				true
			);
			foreach( $perms as $perm ) {
				
				$aTmp = explode(",", str_replace('"','',$perm->bean->cargos->acao) );
				//if ( in_array( $action, $aTmp ) === true ){
				if ( count(preg_grep("/$action/", $aTmp)) ) {
					$hasPermission = true;
				}
				
			}
			
		}
		
		return $hasPermission;
		
	}
	
}

?>
