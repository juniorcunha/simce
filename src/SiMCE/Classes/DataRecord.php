<?php

/**
 * Classe base que será utilizada para todas
 * as classes de acesso ao banco
 *
 * @author Junior Cunha <jrcunha.rs@gmail.com>
 * @copyright (c) 2013
 */

namespace SiMCE\Classes;

class DataRecord {

	/**
	 * Representa o nome do bean
	 * @var string
	 */
	public static $beanName = false;
	
	/**
	 * Armazena o Bean
	 * @var \RedBean_Facade
	 */
	public $bean = false;
	
	/**
	 * Array de campos inválidos
	 * @var mixed
	 */
	public $invalidFields = false;
	
	/**
	 * Array com as funções utilizadas para formatar os dados
	 * @var mixed
	 */
	public static $formatCallbacks = array();
	
	/**
	 * Construtor da classe
	 * 
	 * @return \SiMCE\Classes\DataRecord;
	 * @throws \Exception
	 */
	function __construct( $id = false ) {
		
		// Verifica se foi definido o nome do bean
		if ( static::$beanName === false)
			throw new \Exception( __METHOD__ . " - beanName not defined!" );
		
		// Cria a nova instância do RedBean
		if ($id !== false)
			$this->bean = \R::load( static::$beanName, $id );
		else
			$this->bean = \R::dispense( static::$beanName );
		
		return $this;
		
	}
	
	/**
	 * Informa uma chave e um valor para o bean
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return \SiMCE\Classes\DataRecord
	 * @throws \Exception
	 */
	function set( $key, $value ) {
		
		// Verifica se a chave não é vazia
		if (empty($key))
			throw new \Exception( __METHOD__ . " - Key can't be empty!" );
		
		// Adiciona a informação no bean
		$this->bean->$key = $value;
		
		return $this;
		
	}
	
	/**
	 * Informa todos os valores em um 
	 * formato de array associativo
	 * 
	 * @param mixed $arr
	 * @return \SiMCE\Classes\DataRecord
	 * @throws \Exception
	 */
	function setAll( $arr ) {
		
		// Verifica se é um array
		if (is_array($arr) === false)
			throw new \Exception( __METHOD__ . " - Only array format is supported!" );
		
		// Verifica se o array é vazio
		if (empty($arr))
			throw new \Exception( __METHOD__ . " - Array can't be empty!" );
		
		// Adiciona os valores informados
		foreach( $arr as $key => $value )
			$this->set( $key, $value );
		
		return $this;
		
	}

	/**
	 * Retorna o conteúdo da string
	 * 
	 * @param string $key
	 * @return \SiMCE\Classes\DataRecord
	 * @throws \Exception
	 */
	function get( $key ) {
		
		// Verifica se a chave não é vazia
		if (empty($key))
			throw new \Exception( __METHOD__ . " - Key can't be empty!" );

		// Retorna o valor solicitado
		if (isset(static::$formatCallbacks[$key]))
			return call_user_func( static::$formatCallbacks[$key], $this->bean->$key );
		else
			return $this->bean->$key;
		
	}
	
	/**
	 * Retorna todos itens cadastrados
	 * 
	 * @param boolean $as_object
	 * @return mixed
	 * @throws \Exception
	 */
	static public function getAll( $as_object = false ) {
		
		// Verifica se foi definido o nome do bean
		if ( static::$beanName === false)
			throw new \Exception( __METHOD__ . " - beanName not defined!" );

		// Obtem a lista
		$arr = \R::findAll( static::$beanName );
		$ret = array();
		
		// Retorna no formato array
		if ( $as_object === false) {
			foreach( $arr as $obj ) 
				$ret[] = self::format($obj->export());
		} else {
			$class = "\\SiMCE\\Classes\\" . static::$beanName;
			foreach( $arr as $obj ) {
				$ret[] = new $class( $obj->id );
			}
		}
		
		return $ret;
		
	}
	
	/**
	 * Carrega um bean baseado no ID informado
	 * 
	 * @param int $id
	 * @param boolean $as_object
	 * @return mixed
	 * @throws \Exception
	 */
	static public function getByID( $id, $as_object = false ) {
		
		// Verifica se foi definido o nome do bean
		if ( static::$beanName === false)
			throw new \Exception( __METHOD__ . " - beanName not defined!" );
		
		// Obtem o bean
		$bean = \R::load( static::$beanName, $id );
		
		// Retorna no formato array
		if ( $as_object === false ) {
			return self::format($bean->export());
		} else {
			$class = "\\SiMCE\\Classes\\" . static::$beanName;
			return new $class( $id );
		}
		
	}
	
	/**
	 * Faz a consulta dos dados utilizando o filtro informado
	 * 
	 * @param string $query
	 * @param mixed $bind
	 * @param boolean $as_object
	 * @return mixed
	 * @throws \Exception
	 */
	static public function getByQuery( $query, $bind, $as_object = false ) {
		
		// Verifica se foi definido o nome do bean
		if ( static::$beanName === false)
			throw new \Exception( __METHOD__ . " - beanName not defined!" );
		
		// Obtem a lista
		$arr = \R::find( static::$beanName, $query, $bind );
		$ret = array();
		
		// Retorna no formato array
		if ( $as_object === false) {
			foreach( $arr as $obj ) 
				$ret[] = self::format($obj->export());
		} else {
			$class = "\\SiMCE\\Classes\\" . static::$beanName;
			foreach( $arr as $obj ) {
				$ret[] = new $class( $obj->id );
			}
		}
		
		return $ret;
		
	}
	
	/**
	 * Valida as informações 
	 * 
	 * @return boolean
	 */
	protected function validate() {
		$this->invalidFields = array();
		return true;
	}
	
	/**
	 * 
	 * @return \SiMCE\Classes\DataRecord
	 * @throws Exception
	 */
	public function save() {
		
		// Verifica se as informações são válidas
		if ($this->validate() === false)
			throw new \Exception( __METHOD__ . " - Invalid field: " . json_encode($this->invalidFields) );
	
		// Persiste os dados no banco
		\R::store( $this->bean );
		
		return $this;
		
	}
	
	/**
	 * Remove o registro da base
	 * 
	 * @param int $id
	 * @return void
	 */
	static public function remove( $id ) {
		
		// Verifica se foi definido o nome do bean
		if ( static::$beanName === false)
			throw new \Exception( __METHOD__ . " - beanName not defined!" );
		
		// Carrega o bean e remove
		\R::trash( \R::load( static::$beanName, $id ) );
		
	}
	
	/**
	 * Retorna o conteúdo da classe
	 * para ser utilizado em debugs
	 * 
	 * @return string
	 */
	public function asDebug() {
		return print_r( $this, true );
	}

	/**
	 * Recebe uma data do formulário e converte para o banco
	 * 
	 * @param string $date
	 * @return string
	 */
	public function dateToDb( $date ) {
		if (preg_match("/-/", $date))
			return $date;
		list ($d, $m, $y) = explode("/", $date);
		return "$y-$m-$d";
	}

	/**
	 * Recebe uma data do banco e converte para visualização
	 * 
	 * @param string $date
	 * @return string
	 */
	public function dbToDate( $date ) {
		@list ($y, $m, $d) = @explode("-", $date);
		return "$d/$m/$y";
	}
	
	/**
	 * Recebe uma data e hora do banco e converte para visualização
	 * 
	 * @param string $date
	 * @return string
	 */
	public function dbToDateTime( $datetime ) {
		list ($date, $time) = explode(" ", $datetime);
		list ($y, $m, $d) = explode("-", $date);
		return "$d/$m/$y $time";
	}
	
	/**
	 * Formata o conteúdo do objeto se existir formatador
	 * 
	 * @param mixed $obj
	 * @return void
	 */
	static public function format( $obj ) {
		foreach( static::$formatCallbacks as $key => $callback ) {
			$obj["orig_$key"] = $obj["$key"];
			if ($callback == "json_decode")
				$obj["$key"] = call_user_func($callback, $obj["$key"]);
			else
				$obj["$key"] = call_user_func($callback, $obj["$key"], $obj);
		}
		return $obj;
	}
	
	/**
	 * Faz a consulta da página solcitada informando ainda
	 * o total sem o filtro
	 * 
	 * @param string $filter
	 * @param mixed $bind
	 * @param int $start
	 * @param int $limit
	 * @param boolean $as_object
	 * @param int $full
	 * @return mixed
	 * @throws \Exception
	 */
	static public function getByPage( $filter, $bind, $start, $limit, $as_object = false, $full = 0 ) {
		
		// Verifica se foi definido o nome do bean
		if ( static::$beanName === false)
			throw new \Exception( __METHOD__ . " - beanName not defined!" );
				
		// Obtem a lista
		$rows = array();
		if (!$full)	
			$rows = self::getByQuery( $filter . " LIMIT $start,$limit ", $bind, $as_object );
		else
			$rows = self::getByQuery( $filter, $bind, $as_object );
		
		// Obtem o total
		$total = \R::count( static::$beanName, $filter, $bind );
		
		// Retorna o conteúdo
		return (object)array(
			'rows'  => $rows,
			'total' => $total
		);
		
	}
	
	/**
	 * Faz a consulta dos dados utilizando o filtro informado e retorna o total
	 * 
	 * @param string $query
	 * @param mixed $bind
	 * @return int
	 * @throws \Exception
	 */
	static public function getCount( $query, $bind ) {
		
		// Verifica se foi definido o nome do bean
		if ( static::$beanName === false)
			throw new \Exception( __METHOD__ . " - beanName not defined!" );
		
		// Obtem a lista
		$total = \R::count( static::$beanName, $query, $bind );
		return $total;

	}
	
	/**
	 * Formata os segundos em algo legível
	 * 
	 * @param int $inputSeconds
	 * @return string
	 */
	static function formatSeconds($inputSeconds) {
		
	    $secondsInAMinute = 60;
        $secondsInAnHour  = 60 * $secondsInAMinute;
        $secondsInADay    = 24 * $secondsInAnHour;

        // extract days
        $days = floor($inputSeconds / $secondsInADay);

        // extract hours
        $hourSeconds = $inputSeconds % $secondsInADay;
        $hours = floor($hourSeconds / $secondsInAnHour);

        // extract minutes
        $minuteSeconds = $hourSeconds % $secondsInAnHour;
        $minutes = floor($minuteSeconds / $secondsInAMinute);

        // extract the remaining seconds
        $remainingSeconds = $minuteSeconds % $secondsInAMinute;
        $seconds = ceil($remainingSeconds);
		
		return sprintf("%d dia(s), %d hora(s), %d minuto(s) e %d segundo(s)", $days, $hours, $minutes, $seconds);
		
	}
	
	/**
	 * Sumariza os dados apartir de um valor informado
	 * 
	 * @param mixed $array
	 * @param int $bucket_size
	 * @param string $mask
	 * @return void
	 */
	static function summarizeData( &$array, $bucket_size, $mask ) {
		
		$bucket_size = floor( count($array) / $bucket_size );
		
		if (!is_array($array))
			return false;
		
		$buckets = array_chunk($array,$bucket_size);  // chop up array into bucket size units
		$array = array();
		foreach ($buckets as $bucket)
			$array[] = sprintf($mask, array_sum($bucket)/count($bucket));
		
	}
	
	/**
	 * Sumariza os labels apartir de um valor informado
	 * 
	 * @param mixed $array
	 * @param int $bucket_size
	 * @return mixed
	 */
	static function summarizeLabel( &$array, $bucket_size ) {
		
		$bucket_size = floor( count($array) / $bucket_size );
		
		if (!is_array($array))
			return false;
		
		$buckets = array_chunk($array,$bucket_size);  // chop up array into bucket size units
		$array = array();
		foreach ($buckets as $bucket)
			$array[] = $bucket[floor(count($bucket)/2)];
		
	}
	
	/**
	 * Recebe um numero e mascara o mesmo
	 * 
	 * @param string $number
	 * @return string
	 */
	public function maskNumber( $number ) {
		if (preg_match("/^([\d]{2})([\d]{4})([\d]+)$/", $number, $reg))
			return "({$reg[1]}) {$reg[2]}-{$reg[3]}";
		else
			return "XXXX";
	}

	
}

?>
