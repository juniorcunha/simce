#!/usr/bin/php	
<?php

/**
 * SiMCE Motd
 * 
 * Script responsável por coletar variáveis de sistema e gerar o arquivo /etc/motd
 * 
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @version 1.0
 * @copyright (c) 2016
 */

include_once( __DIR__ . '/../index.php' );

global $app;

$motdMask = "
 _____ ____  ________  _____          _____
/  ___(_)  \/  /  __ \|  ___|        / __  \
\ `--. _| .  . | /  \/| |__   __   __`' / /'
 `--. \ | |\/| | |    |  __|  \ \ / /  / /
/\__/ / | |  | | \__/\| |___   \ V / ./ /___
\____/|_\_|  |_/\____/\____/    \_/  \_____/

Version:      %s
Host:         %s
Processador:  %s
Memória:      %s
Kernel:       %s

";

// Obtem a cpu
$cpu = trim(shell_exec("cat /proc/cpuinfo | grep 'model name' | cut -f2 -d ':' | sort -g | uniq"));

// Obtem o host
$host = trim(shell_exec("hostname"));

// Obtem a memória
$mem = trim(shell_exec("free -m | grep Mem | tr -s ' ' | cut -f2 -d ' '")) . "MB";

// Obtem o kernel
$kernel = trim(shell_exec("uname -r"));

// Cria o arquivo /etc/motd
file_put_contents("/etc/motd", sprintf($motdMask, $app['version'], $host, $cpu, $mem, $kernel ));

?>
