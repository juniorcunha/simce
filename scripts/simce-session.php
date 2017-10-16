#!/usr/bin/php	
<?php

/**
 * SiMCE Stats
 * 
 * Script responsável por controlar as sessões dos usuários
 * 
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @version 1.0
 * @copyright (c) 2013
 */

include_once( __DIR__ . '/../index.php' );

// Obtem os usuários online com sessão mais antiga que 5 minutos
$rows = SiMCE\Classes\Usuarios::getByQuery(
	" online = :online AND last_update <= DATE_SUB( NOW(), INTERVAL 5 MINUTE ) ",
	array( "online" => 1 ),
	true
);

// Atualiza o campo online
foreach( $rows as $obj ) {
	
	$obj->set('online',0);
	$obj->set('password2', $obj->get('password'));
	$obj->save();
	
}

?>