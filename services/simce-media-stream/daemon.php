<?php

/**
 * SiMCE Media Stream
 * 
 * Serviço que irá controlar o streaming online dos audios
 * 
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @version 1.0
 * @copyright (c) 2016
 */

include_once( __DIR__ . '/../../index.php' );
global $app;

/**
 * Desabilita timeout
 */
set_time_limit ( 0);

/**
 * Altera nome do processo, se possível
 */
if ( function_exists ( "setproctitle"))
{
  setproctitle ( "SiMCE MP3 streaming daemon");
}

/**
 * Declara constantes para uso no sistema de registros
 */
define ( "SiMCE_LOG_NOTICE", 0);
define ( "SiMCE_LOG_WARNING", 1);
define ( "SiMCE_LOG_ERROR", 2);
define ( "SiMCE_LOG_FATAL", 3);

/**
 * Função para enviar mensagens para registros, com níveis pré-definidos em constantes.
 *
 * @param $msg String Mensagem a ser registrada.
 * @param $severity int[Optional] Nível de erro. Padrão SiMCE_LOG_NOTICE, apenas registra. Se for SiMCE_LOG_ERROR, finaliza o processo atual. Se for SiMCE_LOG_FATAL, finaliza todos os processos do sistema.
 * @return void
 */
function writeLog ( $msg, $severity = SiMCE_LOG_NOTICE)
{
  global $_sys_log_file, $_lastlog, $_lasttime;

  // Verifica se última mensagem é igual:
  if ( $_lastlog == $msg)
  {
    $_lasttime++;
    return;
  } else {
    if ( $_lasttime > 0)
    {
      // Registra número de ocorrências:
      $message = date ( "M") . " " . sprintf ( "% 2s", date ( "j")) . " " . date ( "H:i:s") . " " . php_uname ( "n") . " simce-server[" . getmypid () . "]: " . gettext ( "WARNING") . ": " . sprintf ( gettext ( "Last message repeats %s times."), $_lasttime) . "\n";
      file_put_contents ( $_sys_log_file, $message, FILE_APPEND);
      echo $message;
      $_lasttime = 0;
    }
    $_lastlog = $msg;
  }

  // Grava mensagem:
  $message = date ( "M") . " " . sprintf ( "% 2s", date ( "j")) . " " . date ( "H:i:s") . " " . php_uname ( "n") . " simce-server[" . getmypid () . "]: " . ( $severity == SiMCE_LOG_ERROR ? gettext ( "ERROR") . ": " : "") . ( $severity == SiMCE_LOG_WARNING ? gettext ( "WARNING") . ": " : "") . ( $severity == SiMCE_LOG_FATAL ? gettext ( "FATAL") . ": " : "") . $msg . "\n";
  file_put_contents ( $_sys_log_file, $message, FILE_APPEND);
  echo $message;
  if ( $severity == SiMCE_LOG_FATAL || $severity == SiMCE_LOG_ERROR)
  {
    exit ( 1);
  }
}

/**
 * Função para criar cabeçalho WAV para ser utilizado na conversão do áudio para MP3. É necessário informar o número de segundos da duração para gerar o cabeçalho. O formato gerado é fixo e é "RIFF (little-endian) data, WAVE audio, Microsoft PCM, 16 bit, mono 8000 Hz", padrão utilizado pelo Asterisk. Cada segundo ocupa o tamanho de 16000 bytes.
 *
 * @param $size Integer Seconds of audio.
 * @return String Cabeçalho WAV com a duração indicada. Conteúdo byte-code.
 */
function createWavHeader ( $size)
{
  return "RIFF" . pack ( "V", ( 16000 * $size) + 36) . "WAVEfmt " . chr ( 0x10) . chr ( 0x00) . chr ( 0x00) . chr ( 0x00) . chr ( 0x01) . chr ( 0x00) . chr ( 0x01) . chr ( 0x00) . chr ( 0x40) . chr ( 0x1F) . chr ( 0x00) . chr ( 0x00) . chr ( 0x80) . chr ( 0x3E) . chr ( 0x00) . chr ( 0x00) . chr ( 0x02) . chr ( 0x00) . chr ( 0x10) . chr ( 0x00) . "data" . pack ( "V", 16000 * $size);
}

/**
 * Função para gravar em um socket de cliente, encerrando a conexão caso não consiga gravar
 */
function writeStream ( $id, $msg)
{
  global $clients, $streams;

  while ( ! empty ( $msg))
  {
    if ( ! $return = socket_write ( $clients[$id]["FD"], $msg, strlen ( $msg)))
    {
      socket_shutdown ( $clients[$id]["FD"]);
      socket_close ( $clients[$id]["FD"]);
      if ( ! empty ( $clients[$id]["Canal"]))
      {
        $streams[$clients[$id]["Canal"]]["Listeners"]--;
      }
      unset ( $clients[$id]);
      writeLog ( sprintf ( gettext ( "Client %d: Error writing to socket, closing connection."), $id));
      return false;
    }
    if ( $return != strlen ( $msg))
    {
      $msg = substr ( $msg, $return);
      usleep ( 10000);
    } else {
      $msg = "";
    }
  }

  return true;
}

/**
 * Função para enviar resposta HTTP
 *
 * @param $id Integer ID do cliente no array $clients
 * @param $code Integer Código de retorno HTTP
 * @param $codetxt String Texto de retorno HTTP
 * @param $title String Título da página
 * @param $text String Texto do corpo
 * @param $headers Array[Optional] Array contendo cabeçalhos extras a serem inseridos.
 * @return boolean Resultado da escrita
 */
function sendReply ( $id, $code, $codetxt, $title, $text, $headers = array ())
{
  global $clients, $streams, $_sys_version, $_sys_language;

  $msg = "HTTP/1.0 " . $code . " " . $codetxt . "\r\n";
  $msg .= "Server: SiMCE/" . $_sys_version . "\r\n";
  $msg .= "Connection: close\r\n";
  $msg .= "Content-Type: text/html; charset=utf-8\r\n";
  foreach ( $headers as $tag => $value)
  {
    $msg .= $tag . ": " . $value . "\r\n";
  }
  $msg .= "\r\n";
  $msg .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\r\n";
  $msg .= "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"" . $_sys_language . "\" lang=\"" . $_sys_language . "\">\r\n";
  $msg .= "<head>\r\n";
  $msg .= "<title>SiMCE v" . $_sys_version . ": " . gettext ( $title) . "</title>\r\n";
  $msg .= "</head>\r\n";
  $msg .= "<body>\r\n";
  $msg .= "<h1 align=\"center\">SiMCE v" . $_sys_version . "</h1>\r\n";
  $msg .= "<p><strong>" . gettext ( "Error") . "</strong>: " . gettext ( $text) . "</p>\r\n";
  $msg .= "</body>\r\n";
  $msg .= "</html>\r\n";

  $return = socket_write ( $clients[$id]["FD"], $msg, strlen ( $msg));
  socket_shutdown ( $clients[$id]["FD"]);
  socket_close ( $clients[$id]["FD"]);
  unset ( $clients[$id]);
  writeLog ( sprintf ( gettext ( "Client %d: Sending reply code %d %s."), $id, $code, $codetxt));

  return true;
}

/**
 * Altera diretório atual para anterior
 */
chdir ( substr ( getcwd (), 0, strrpos ( getcwd (), "/")));

/**
 * Bom, aqui a porca torce o rabo. Temos que criar um serviço para monitorar o áudio dos canais das placas.
 * Para que isto seja independente do fabricante e independa de funcionabilidades do driver, vamos aproveitar
 * os arquivos wav gerados pelo sistema de monitoria do Asterisk, que sempre vai existir para quaisquer tipo
 * de canal de áudio existente nele. Para tanto, devemos criar um streaming WAV contendo silêncio, e quando
 * for detectado um novo arquivo de áudio, ler o conteúdo do mesmo e adicionar a este streaming, e retornar a
 * gerar silêncio quando o mesmo for encerrado. A princípio parece simples, mas por limitações dos encoders
 * de MP3, transformar um streaming de WAV em MP3 não é tão simples assim, ainda mais utilizando PHP, que não
 * foi nem de perto feito para isso. Como não tenho como gerar através de um streaming continuamente, vou
 * ficar monitorando os canais, e gerando fatias de áudio com um tempo pré-determinado (a princípo, de 30 a
 * 60 segundos, configurável) e colocando em um arquivo, fechando quando finaliza seu tempo, abrindo um outro
 * arquivo e gerando mais uma fatia de áudio, e então abre-se novamente (sobreescreve) a outra fatia gerada
 * anteriormente. Quando algum cliente coneccta no servidor de streaming, o servidor passa a converter essa
 * WAV para MP3 e envia por streaming, e quando o tempo da fatia terminar, pega a próxima fatia, assim por
 * diante. Uma vez gerado o streaming no formato MP3, pode-se conectar mais de um cliente que vai usufruir do
 * mesmo streaming de áudio, evitando uma nova codificação de WAV para MP3. Para podermos tratar isso,
 * devemos escrever um servidor de streaming em PHP, onde sempre que um cliente conectar, ele irá ativar o
 * conversor do canal desejado para MP3 caso não exista ainda, e removendo o conversor quando desconectado o
 * último cliente deste canal. Isto deve ser feito para todos canais. Posteriormente, devemos adicionar uma
 * segurança no acesso a este streaming de áudio, fazendo com que o cliente gere através da interface uma
 * cookie ou similar gravada em banco de dados, permitindo acesso a um canal específico.
 */

/** 
 * Cria socket para escutar requisições HTTP
 */
echo gettext ( "Executing") . ": " . gettext ( "Binding web server port") . "... ";
if ( ! $socket = socket_create ( AF_INET, SOCK_STREAM, 0))
{
  writeLog ( gettext ( "Could not create web server socket!"), SiMCE_LOG_FATAL);
}
if ( ! $result = socket_bind ( $socket, "0.0.0.0", 45400))
{
  writeLog ( gettext ( "Could not bind to web server socket!"), SiMCE_LOG_FATAL);
}
if ( ! $result = socket_listen ( $socket, 3))
{
  writeLog ( gettext ( "Could not set up web server socket listener!"), SiMCE_LOG_FATAL);
}
if ( ! socket_set_nonblock ( $socket))
{
  writeLog ( gettext ( "Could not set socket to non blocking mode!"), SiMCE_LOG_FATAL);
}
echo chr ( 27) . "[1;37m" . gettext ( "OK") . chr ( 27) . "[1;0m\n";

/**
 * Altera o UID/GID para um usuário não privilegiado (nobody/nogroup)
 */
echo gettext ( "Executing") . ": " . gettext ( "Changing effective UID/GID") . "... ";
if ( ! $uid = posix_getpwnam ( $_sys_uid))
{
  writeLog ( sprintf ( gettext ( "Unable to get ID of user \"%s\"!"), $_sys_uid), SiMCE_LOG_FATAL);
}
if ( ! $gid = posix_getgrnam ( $_sys_gid))
{
  writeLog ( sprintf ( gettext ( "Unable to get ID of group \"%s\"!"), $_sys_gid), SiMCE_LOG_FATAL);
}
if ( ! posix_setgid ( $gid["gid"]))
{
  writeLog ( sprintf ( gettext ( "Unable to change GID to %d \"%s\"!"), $gid["gid"], $_sys_gid), SiMCE_LOG_FATAL);
}
if ( ! posix_setuid ( $uid["uid"]))
{
  writeLog ( sprintf ( gettext ( "Unable to change UID to %d \"%s\"!"), $uid["uid"], $_sys_uid), SiMCE_LOG_FATAL);
}
echo chr ( 27) . "[1;37m" . gettext ( "OK") . chr ( 27) . "[1;0m\n";

/**
 * Mostra mensagem de início de operação
 */
echo gettext ( "All done. Start serving audio streams!") . "\n\n";

/**
 * Cria array com canais a serem monitorados
 */
$canais = array ();
foreach ( $channels as $channel)
{
  $canais[] = $channel["ChannelID"];
}
$streams = array ();
foreach ( $canais as $canal)
{
  $streams[$canal] = array ();
  $streams[$canal]["Buffer"] = array ();
  $streams[$canal]["Bucket"] = "";
  $streams[$canal]["Pointer"] = $_sys_aud_slice;
  $streams[$canal]["Delay"] = 0;
  $streams[$canal]["Listeners"] = 0;
  $streams[$canal]["Stream"] = array ();
  $streams[$canal]["StreamPointer"] = 0;
}
$clients = array ();

/**
 * Cria descritores para encoder
 */
$descriptorspec = array (
                    0 => array ( "pipe", "r"),
                    1 => array ( "pipe", "w"),
                    2 => array ( "file", "/dev/null", "a")
                  );

/**
 * Inicializa e configura uma instância do inotify para monitorar novos áudios do Asterisk
 */
$notifyfd = inotify_init ();
  
/**
 * Adiciona monitoria no diretório do Asterisk
 */
$notifywd = inotify_add_watch ( $notifyfd, $_sys_mon_path, IN_CREATE);
$monitorando = array ();

/**
 * Processa eventos (laço eterno)
 */
$sleep = time ();
while ( true)
{
  /**
   * Processa requisições HTTP
   */
  foreach ( $clients as $id => $cliente)
  {
    if ( $clients[$id]["InHeaders"] == true)
    {
      /**
       * Verifica se ocorreu timeout, e envia mensagem
       */
      if ( time () >= $clients[$id]["TimeOut"])
      {
        sendReply ( $id, 400, "Timeout", gettext ( "Request timeout"), gettext ( "Timeout waiting for request."));
        continue;
      }
      $clients[$id]["Buffer"] .= str_replace ( "\r\n", "\n", socket_read ( $clients[$id]["FD"], 8192));
      if ( strpos ( $clients[$id]["Buffer"], "\n\n") !== false)
      {
        $clients[$id]["InHeaders"] = false;
        /**
         * Processa requisição HTTP
         */
        $tmp = explode ( "\n", $clients[$id]["Buffer"]);
        unset ( $clients[$id]["Buffer"]);
        $request = array ();
        $request["Method"] = preg_replace ( "/^(.*) (.*) (HTTP\/\d\.\d)$/", "\${1}", $tmp[0]);
        $request["Path"] = preg_replace ( "/^(.*) (.*) (HTTP\/\d\.\d)$/", "\${2}", $tmp[0]);
        $request["Protocol"] = preg_replace ( "/^(.*) (.*) (HTTP\/\d\.\d)$/", "\${3}", $tmp[0]);
        if ( ! in_array ( $request["Method"], $_sys_httpd_valid_methods))
        {
          sendReply ( $id, 400, "Bad Request", gettext ( "Invalid request"), gettext ( "Invalid HTTP method requested."));
          continue;
        }

        /**
         * Processa cabeçalhos
         */
        array_shift ( $tmp);
        foreach ( $tmp as $line)
        {
          if ( ! empty ( $line))
          {
            $key = substr ( $line, 0, strpos ( $line, ":"));
            $value = ltrim ( substr ( $line, strpos ( $line, ":") + 1));
            if ( $key == "Cookie")
            {
              if ( ! isset ( $request["Headers"]["Cookies"]))
              {
                $request["Headers"]["Cookies"] = array ();
              }
              foreach ( explode ( ";", $value) as $cookie)
              {
                $request["Headers"]["Cookies"][trim ( urldecode ( substr ( $cookie, 0, strpos ( $cookie, "="))))] = urldecode ( substr ( $cookie, strpos ( $cookie, "=") + 1));
              }
            } else {
              $request["Headers"][$key] = $value;
            }
          }
        }
        unset ( $tmp);

        /**
         * Verifica se caminho existe
         */
        switch ( substr ( $request["Path"], 0, strrpos ( $request["Path"], "/") + 1))
        {
          case "/listen/":
            /**
             * Primeiro, verifica autenticação decodificando cookie utilizando base64
             */
            $usercookie = explode ( "|", base64_decode ( $request["Headers"]["Cookies"]["simce"]));
            if ( ! empty ( $usercookie[0]) && ! empty ( $usercookie[1]))
            {
              /**
               * Antes de realizar quaisquer acao no banco, verifica se o mesmo nao encerrou por timeout a conexao
               */
              if ( ! $result = @mysql_query ( "SELECT 1", $_sql_id))
              {
                if ( mysql_errno ( $_sql_id) == 2006)
                {
                  db_connect ();
                }
              }

              /**
               * Verifica se a sessão do usuário existe
               */
              @socket_getpeername ( $clients[$id]["FD"], $ip, $port);
              if ( ! $result = @mysql_query ( "SELECT `sessoes`.*, `usuarios`.*, `sessoes`.`ID` AS `IDSessao` FROM `sessoes`, `usuarios` WHERE `sessoes`.`Usuario` = '" . mysql_real_escape_string ( $usercookie[0], $_sql_id) . "' AND `sessoes`.`Cookie` = '" . mysql_real_escape_string ( $usercookie[1], $_sql_id) . "' AND `sessoes`.`IP` = INET_ATON('" . mysql_real_escape_string ( $ip, $_sql_id) . "') AND `usuarios`.`ID` = `sessoes`.`Usuario`", $_sql_id))
              {
                sendReply ( $id, 401, "Not Authorized", gettext ( "Access Denied"), gettext ( "An error ocurred while trying to recover the session informations."));
                break;
              } else {
                $userinfo = @mysql_fetch_assoc ( $result);
                /**
                 * Verifica se sessão não expirou
                 */
                if ( time () > $userinfo["Validade"])
                {
                  if ( $userinfo["Expirada"] == "N")
                  {
                    add_log ( "logout", $userinfo["IDSessao"], 0, array ( "Timeout" => true));
                  }
                  if ( ! @mysql_query ( "DELETE FROM `sessoes` WHERE `ID` = '" . mysql_real_escape_string ( $userinfo["IDSessao"], $_sql_id) . "'", $_sql_id))
                  {
                    sendReply ( $id, 401, "Not Authorized", gettext ( "Access Denied"), gettext ( "An error ocurred while trying to remove your session."), array ( "Set-Cookie" => "SCG_admin="));
                    break;
                  }
                  sendReply ( $id, 401, "Not Authorized", gettext ( "Access Denied"), gettext ( "Your session was timed out. Login-in again."), array ( "Set-Cookie" => "SCG_admin="));
                  break;
                } else {
                  /**
                   * Atualiza validade da sessão
                   */
                  if ( ! @mysql_query ( "UPDATE `sessoes` SET `Validade` = '" . ( time () + $_misc_timeout ) . "' WHERE `ID` = '" . mysql_real_escape_string ( $userinfo["IDSessao"], $_sql_id) . "'", $_sql_id))
                  {
                    sendReply ( $id, 401, "Not Authorized", gettext ( "Access Denied"), gettext ( "An error ocurred while trying to recover the session informations."), array ( "Set-Cookie" => "SCG_admin="));
                    break;
                  }
                }
              }
              writeLog ( sprintf ( gettext ( "Client %d: User authenticated as \"%s\""), $id, $userinfo["Usuario"]));
            } else {
              sendReply ( $id, 401, "Not Authorized", gettext ( "Access Denied"), gettext ( "Please authenticate at SiMCE system first."));
              break;
            }

            /**
             * Verifica se canal desejado existe e se usuário tem permissão de escutá-lo
             */
            $canal = substr ( $request["Path"], strrpos ( $request["Path"], "/") + 1);
	    print "Canal $canal\n";
            if ( ! in_array ( $canal, $canais))
            {
              /**
               * Stream não encontrado
               */
              sendReply ( $id, 404, "Not Found", gettext ( "Not Found"), gettext ( "The requested stream was not found at this server."));
              break;
            }

            /**
             * Verifica informações do canal (e permissões, caso necessário)
             */
            if ( ! $result = @mysql_query ( "SELECT `canais`.*" . ( $userinfo["Admin"] != "Y" ? ", `cargos`.`Permissoes` AS `Permissoes`" : "") . " FROM `hardware`, `canais`" . ( $userinfo["Admin"] != "Y" ? ", `operacoescanais`, `operacoesacesso`, `cargos`" : "") . " WHERE `canais`.`Hardware` = `hardware`.`ID` AND `canais`.`Canal` = '" . mysql_real_escape_string ( $canal, $_sql_id) . "' AND `hardware`.`Valido` = 'Y'" . ( $userinfo["Admin"] != "Y" ? " AND `operacoescanais`.`SMS` IS NULL AND `operacoescanais`.`Canal` = `canais`.`ID` AND `operacoescanais`.`Operacao` = `operacoesacesso`.`Operacao` AND `operacoesacesso`.`Usuario` = " . (int) $userinfo["ID"] . " AND `operacoescanais`.`Inicio` <= NOW() AND CONCAT(`operacoescanais`.`Fim`,' 23:59:59') >= NOW() AND `operacoesacesso`.`Nivel` = `cargos`.`ID`" : ""), $_sql_id))
            {
	      print "AQUI 1\n";
              sendReply ( $id, 401, "Not Authorized", gettext ( "Access Denied"), gettext ( "An error ocurred while trying to recover the channel informations."));
              break;
            }
            if ( mysql_num_rows ( $result) != 1)
            {
	      print "AQUI 2\n";
              sendReply ( $id, 401, "Not Authorized", gettext ( "Access Denied"), gettext ( "You are not authorized to listen this channel now."));
              break;
            }
            $canalinf = mysql_fetch_assoc ( $result);
            if ( $userinfo["Admin"] == "N" && ! preg_match ( "/(^|\|)1(\$|\|)/", $canalinf["Permissoes"]))
            {
	      print "AQUI 3\n";
              sendReply ( $id, 401, "Not Authorized", gettext ( "Access Denied"), gettext ( "You are not authorized to listen this channel now."));
              break;
            }
            if ( ! $result = @mysql_query ( "SELECT `Operacao` FROM `operacoescanais` WHERE `Canal` = " . (int) $canalinf["ID"] . " AND `Inicio` <= NOW() AND CONCAT(`Fim`,' 23:59:59') >= NOW()", $_sql_id))
            {
	      print "AQUI 4\n";
              sendReply ( $id, 401, "Not Authorized", gettext ( "Access Denied"), gettext ( "An error ocurred while trying to recover the channel informations."));
              break;
            }
            if ( mysql_num_rows ( $result) == 1)
            {
              $canalop = mysql_result ( $result, 0);
            } else {
              $canalop = 0;
            }

            /**
             * Registra escuta do canal
             */
            add_log ( "lst_chn", $canalinf["ID"], $canalop);

            /**
             * Aloca escuta de canal
             */
            writeLog ( sprintf ( gettext ( "Client %d: Listening to channel %s"), $id, $canal));
            $streams[$canal]["Listeners"]++;
            $clients[$id]["Canal"] = $canal;
            $clients[$id]["StreamPointer"] = 0;

            /**
             * Cria e envia cabeçalhos
             */
            $msg = "HTTP/1.0 200 OK\r\n";
            $msg .= "Server: SiMCE/" . $_sys_version . "\r\n";
            $msg .= "Cache-Control: max-age=86400\r\n";
            $msg .= "Pragma: no-cache\r\n";
            $msg .= "Content-Length: 220000000\r\n";
            $msg .= "Content-Type: audio/mpeg\r\n";
            $msg .= "Connection: keep-alive\r\n";
            $msg .= "\r\n";
            $msg .= chr ( 0xff) . chr ( 0xe3) . chr ( 0x48) . chr ( 0xc4);
            writeStream ( $id, $msg);
            break;
          case "/":
            /**
             * Redireciona para página do SiMCE
             */
            sendReply ( $id, 302, "Moved Temporarily", gettext ( "Moved temporarily"), sprintf ( gettext ( "The requested page was moved <a href=\"%s\">here</a>."), $_sys_baseurl), array ( "Location" => $_sys_baseurl));
            break;
          default:
            /**
             * Caso não tenha encontrado, retorna 404
             */
            sendReply ( $id, 404, "Not Found", gettext ( "Not Found"), gettext ( "The requested stream was not found at this server."));
            break;
        }
        continue;
      }
    } else {
      /**
       * Envia todas fatias de áudio MP3 disponíveis para o cliente
       */
      foreach ( array_keys ( $streams[$clients[$id]["Canal"]]["Stream"]) as $key)
      {
        if ( $key > $clients[$id]["StreamPointer"])
        {
          if ( writeStream ( $id, $streams[$clients[$id]["Canal"]]["Stream"][$key]))
          {
            $clients[$id]["StreamPointer"] = $key;
          } else {
            break;
          }
        }
      }
    }
  }

  /**
   * Varre canais procurando por canal com monitoria em andamento
   */
  foreach ( $streams as $canal => $canalinf)
  {
    if ( $streams["$canal"]["Delay"] > 0)
    {
      $streams[$canal]["Delay"]--;
    }
    if ( isset ( $streams[$canal]["FD"]) && ( ! $streams[$canal]["Delay"] || $streams[$canal]["HangUp"]))
    {
      /**
       * Se o canal ainda está escutando, realimenta o balde
       */
      if ( isset ( $streams[$canal]["FD"]))
      {
        $streams[$canal]["Bucket"] .= stream_get_contents ( $streams[$canal]["FD"]);
        if ( $streams[$canal]["HangUp"])
        {
          fclose ( $canalinf["FD"]);
          unset ( $streams[$canal]["FD"]);

          /**
           * Adiciona nulos (silêncio) para completar o segundo
           */
          $streams[$canal]["Bucket"] .= str_repeat ( chr ( 0), 16000 - ( strlen ( $streams[$canal]["Bucket"]) % 16000));
        }
      }
    }

    /**
     * Se o balde ainda possui conteúdo, alimenta buffer, senão deixa em silêncio
     */
    if ( strlen ( $streams[$canal]["Bucket"]) > 0)
    {
      if ( strlen ( $streams[$canal]["Bucket"]) < 16000)
      {
        if ( $streams[$canal]["HangUp"])
        {
          writeLog ( gettext ( "Bucket with less than 1 second of audio and no call. Flushing bucket!", SiMCE_LOG_WARNING));
          $streams[$canal]["Bucket"] = "";
        } else {
          writeLog ( gettext ( "Bucket desync detected. Bucket with less than 1 second of audio and active call! Making silence!"), SiMCE_LOG_WARNING);
        }
      } else {
        $streams[$canal]["Buffer"][$streams[$canal]["Pointer"]] = substr ( $streams[$canal]["Bucket"], 0, 16000);
        if ( strlen ( $streams[$canal]["Bucket"]) > 16000)
        {
          $streams[$canal]["Bucket"] = substr ( $streams[$canal]["Bucket"], 16000);
        } else {
          if ( isset ( $streams[$canal]["HangUp"]))
          {
            unset ( $streams[$canal]["HangUp"]);
          }
          $streams[$canal]["Bucket"] = "";
        }
      }
    }
  }

  /**
   * Verifica se existem eventos na fila de notificação do kernel e trata o evento
   */
  if ( inotify_queue_len ( $notifyfd) > 0)
  {
    $events = inotify_read ( $notifyfd);
    foreach ( $events as $event)
    {
      /**
       * Verifica se evento é término de gravação
       */
      if ( $event["wd"] != $notifywd && array_key_exists ( $event["wd"], $monitorando))
      {
        writeLog ( sprintf ( gettext ( "Hangup detected at channel %s."), $monitorando[$event["wd"]]));
        inotify_rm_watch ( $notifyfd, $event["wd"]);
        $streams[$monitorando[$event["wd"]]]["HangUp"] = true;
        unset ( $monitorando[$event["wd"]]);
        continue;
      }
      /**
       * Evento de novo arquivo. Verifica se é evento a ser monitorado
       */
      $canal = preg_replace ( "/^REC-(\w+)-(\w+)-.*/", "$1-$2", $event["name"]);

      if ( in_array ( $canal, $canais) && substr ( $event["name"], strlen ( $event["name"]) - 7) == "-in.wav" && $event["wd"] == $notifywd)
      {
        if ( isset ( $streams[$canal]["FD"]))
        {
          writeLog ( sprintf ( gettext ( "New call detected at channel %s, but we already have an active call!"), $canal), SiMCE_LOG_WARNING);
          continue;
        }
        writeLog ( sprintf ( gettext ( "New call detected at channel %s."), $canal));
        if ( ! $streams[$canal]["FD"] = @fopen ( $_sys_mon_path . $event["name"], "r"))
        {
          writeLog ( gettext ( "Cannot open new channel audio file!"), SiMCE_LOG_WARNING);
          unset ( $streams[$canal]["FD"]);
        } else {
          /**
           * Descarta os cabeçalhos da WAV
           */
          $tmp = fread ( $streams[$canal]["FD"], 44);
          unset ( $tmp);
          /**
           * Seta leitura do stream não obstrusiva
           */
          stream_set_blocking ( $streams[$canal]["FD"], 0);
          $monitorando[inotify_add_watch ( $notifyfd, $_sys_mon_path . $event["name"], IN_CLOSE_WRITE)] = $canal;
          $streams[$canal]["Delay"] = $_sys_aud_delay;
          $streams[$canal]["HangUp"] = false;
        }
        continue;
      }
    }
  }

  /**
   * Varre canais procurando por ouvintes
   */
  foreach ( $streams as $canal => $canalinf)
  {
    if ( $streams[$canal]["Listeners"] == 0)
    {
      /**
       * Verifica se ainda possui buffer, e zera, pois ouvinte saiu
       */
      if ( sizeof ( $streams[$canal]["Stream"]) > 0)
      {
        $streams[$canal]["Stream"] = array ();
        $streams[$canal]["StreamPointer"] = 0;
      }
    } else {
      /**
       * Processa buffer até não haver mais dados disponíveis para conversão
       */
      if ( $streams[$canal]["StreamPointer"] == 0)
      {
        $streams[$canal]["StreamPointer"] = $streams[$canal]["Pointer"] - $_sys_aud_slice;
      }
      while ( $streams[$canal]["StreamPointer"] + $_sys_aud_encodeslice <= $streams[$canal]["Pointer"])
      {
        /**
         * Cria processo para encodear a MP3
         */
        $encoder = proc_open ( "lame --nohist --silent --nores -q 7 --resample 8000 --cbr -B 32 -a -V 9 - -", $descriptorspec, $pipes);

        /**
         * Envia o áudio em WAV para o processo
         */
        if ( ! fwrite ( $pipes[0], createWavHeader ( $_sys_aud_encodeslice), 44))
        {
          writeLog ( gettext ( "Cannot write WAV header to lame encoder!"), E_ERROR);
        }
        for ( $x = $streams[$canal]["StreamPointer"]; $x <= $streams[$canal]["StreamPointer"] + $_sys_aud_encodeslice; $x++)
        {
          if ( ! fwrite ( $pipes[0], ( isset ( $streams[$canal]["Buffer"][$x]) ? $streams[$canal]["Buffer"][$x] : str_repeat ( chr ( 0), 16000)), 16000))
          {
            writeLog ( gettext ( "Cannot write WAV content to lame encoder!"), E_ERROR);
          }
        }
        fclose ( $pipes[0]);

        /**
         * Lê e descarta o cabeçalho do MP3
         */
        $tmp = fread ( $pipes[1], 4);
        unset ( $tmp);

        /**
         * Alimenta o buffer com o áudio MP3 gerado
         */
        while ( ! feof ( $pipes[1]))
        {
          $streams[$canal]["Stream"][$streams[$canal]["StreamPointer"]] .= fread ( $pipes[1], 8192);
        }
        fclose ( $pipes[1]);
        @proc_close ( $encoder);
        $streams[$canal]["StreamPointer"] += $_sys_aud_encodeslice;
      }
    }
  }

  /**
   * Aceita conexões entrantes e abre um socket para elas
   */
  if ( $spawn = socket_accept ( $socket))
  {
    if ( sizeof ( $clients) > $_sys_httpd_maxthreads)
    {
      writeLog ( gettext ( "Maximum webserver clients reached!"));
      socket_shutdown ( $spawn);
      socket_close ( $spawn);
    } else {
      $clients[] = array ( "FD" => $spawn, "TimeOut" => time () + $_sys_httpd_timeout, "InHeaders" => true);
      /**
       * Altera opções do soquete (Buffer de escrita de 1 MB, acesso não obstrusivo)
       */
      socket_set_option ( $spawn, SOL_SOCKET, SO_SNDBUF, 1048576);
      socket_set_nonblock ( $spawn);
    }
    end ( $clients);
    if ( socket_getpeername ( $spawn, $ip, $port))
    {
      writeLog ( sprintf ( gettext ( "Client %d: New connection from %s:%d"), key ( $clients), $ip, $port));
    } else {
      writeLog ( sprintf ( gettext ( "Client %d: New connection from unknown source!"), key ( $clients)), SiMCE_LOG_WARNING);
    }
  }

  /**
   * Dorme para completar 1 segundo (se não passado)
   */
  $time = microtime ();
  if ( $sleep <= substr ( $time, 11))
  {
    usleep ( 1000000 - substr ( $tmp, 2, 6));
  }
  $sleep++;

  /**
   * Limpa último segundo do buffer de cada canal e buffer de streaming antigos
   */
  reset ( $streams);
  foreach ( $streams as $canal => $canalinf)
  {
    unset ( $streams[$canal]["Buffer"][$streams[$canal]["Pointer"] - $_sys_aud_slice]);
    $streams[$canal]["Pointer"]++;
    if ( sizeof ( $streams[$canal]["Stream"]) > 0)
    {
      foreach ( array_keys ( $streams[$canal]["Stream"]) as $key)
      {
        if ( $key < $streams[$canal]["StreamPointer"] - $_sys_aud_slice)
        {
          unset ( $streams[$canal]["Stream"][$key]);
        }
      }
    }
  }

  /**
   * Para depuração
   */
  // echo chr ( 27) . "[1;37mB01C07" . chr ( 27) . "[1;0m: S: " . sizeof ( $streams["B01C07"]["Buffer"]) . " P: " . $streams["B01C07"]["Pointer"] . " B: " . strlen ( $streams["B01C07"]["Bucket"]) . " M: " . sizeof ( $streams["B01C07"]["Stream"]) . " L: " . $streams["B01C07"]["Listeners"] . " H: " . ( $streams["B01C07"]["HangUp"] == true ? "S" : "N") . " D: " . ( isset ( $streams["B01C07"]["Delay"]) ? $streams["B01C07"]["Delay"] : "0") . "\n";
}

/**
 * Encerra monitoria do diretório
 */
inotify_rm_watch ( $notifyfd, $notifywd);

/**
 * Encerra a instância do inotify
 */
fclose ( $notifyfd);
?>
