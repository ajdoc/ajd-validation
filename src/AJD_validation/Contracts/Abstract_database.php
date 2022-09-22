<?php namespace AJD_validation\Contracts;

use PDO;
use Exception;
use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Helpers\Database;
use AJD_validation\Helpers\Logics_map;

abstract class Abstract_database extends Abstract_rule
{
	const USERNAME_STR = 'username',
		  PASSWORD_STR = 'password',
		  OPTIONS_STR = 'options',
		  TABLE_STR = 'table',
		  PRIMARY_ID_STR = 'primary_id',
		  CONNECTION_STR = 'connection',
		  AES_DECRYPT_STR = 'aes_decrypt',
		  DEFAULT_PORT = '3306',
		  EXCLUDE_ID = 'exclude_id',
		  EXCLUDE_VALUE = 'exclude_value',
		  CALLABLE_QUERY_STR = 'callback',
		  LOGICS_MAP_STR = 'logics_map',
		  JUST_INSTANCE_STR = 'just_instance';

	protected $allowedStr = [];
	protected $db;
	protected $config;
	protected $queryConfig = [];
	protected $callableQueryConfig;
	protected $reverseCheck = false;

	protected $currentQuery;
	protected $setQuery;

	protected $retryConfig = [];

	public $table;
	public $primary_id;

	public function __construct($config, $queryConfig = array(), $callableQueryConfig = null)
	{
		$this->allowedStr = [self::USERNAME_STR, self::PASSWORD_STR, self::TABLE_STR, self::PRIMARY_ID_STR, self::AES_DECRYPT_STR, self::EXCLUDE_ID, self::EXCLUDE_VALUE, self::CALLABLE_QUERY_STR, self::LOGICS_MAP_STR, self::JUST_INSTANCE_STR];
		
		$mainConfig = $this->processConfig($config);
		$this->config = $mainConfig;
		$queryConfig = $this->processQueryConfig($queryConfig);

		$this->queryConfig = $queryConfig;

		if( !EMPTY( $callableQueryConfig ) && is_callable( $callableQueryConfig ) )
		{
			$this->callableQueryConfig = $callableQueryConfig;
		}

		$validator = $this->getValidator();
		$paramValidator = $validator->required();

		$this->processDbInstance($mainConfig);

		if( !$paramValidator->validate( $this->db ) )
		{
			throw new Exception('Database is required.');
		}
		
		if( 
			!empty($this->queryConfig)
			&& empty( $this->callableQueryConfig ) 
			&&
			(
				!isset($this->queryConfig[self::CALLABLE_QUERY_STR])
				|| empty($this->queryConfig[self::CALLABLE_QUERY_STR])
			)
			&& 
			(
				!isset($this->queryConfig[self::LOGICS_MAP_STR])
				|| empty($this->queryConfig[self::LOGICS_MAP_STR])
			)
			&& 
			(
				!isset($this->queryConfig[self::JUST_INSTANCE_STR])
				|| empty($this->queryConfig[self::JUST_INSTANCE_STR])	
			)
			
		)
		{
			if( !$paramValidator->validate( $this->queryConfig[ self::TABLE_STR ] ) )
			{
				throw new Exception('Table name is required.');
			}

			if( !$paramValidator->validate( $this->queryConfig[ self::PRIMARY_ID_STR ] ) )
			{
				throw new Exception('Primary id is required');
			}
		}

		$this->table = ( isset( $this->queryConfig[ self::TABLE_STR ] ) ) ? $this->queryConfig[ self::TABLE_STR ] : '';
		$this->primary_id = ( isset( $this->queryConfig[ self::PRIMARY_ID_STR ] ) ) ? $this->queryConfig[ self::PRIMARY_ID_STR ] : '';
	}

	protected function processDbInstance( $mainConfig )
	{
		if( !EMPTY( $mainConfig ) )
		{
			if( is_array( $mainConfig ) )
			{
				if( !EMPTY( $mainConfig['connection'] ) && !EMPTY( $mainConfig['username'] ) && ISSET( $mainConfig['password'] ) )
				{
					$this->db = new Database( $mainConfig['connection'], $mainConfig['username'], $mainConfig['password'], $mainConfig['options'] );
				}
				else if( !EMPTY( $mainConfig['connection'] ) )
				{
					if( ISSET( static::$dbConnections[ $mainConfig['connection'] ] ) )
					{
						if(is_array(static::$dbConnections[ $mainConfig['connection'] ]))
						{
							$this->db = new Database( null, null, null, [], ...static::$dbConnections[ $mainConfig['connection'] ] );
						}
						else
						{
							$this->db = new Database( static::$dbConnections[ $mainConfig['connection'] ] );	
						}
					}
					else if( is_object( $mainConfig['connection'] ) )
					{
						$this->db = new Database( $mainConfig['connection'] );
					}
				}
			}
			else if( is_object( $mainConfig ) )
			{
				$this->db = new Database( $mainConfig );
			}
		}
	}

	protected function processQueryConfig($queryConfig)
	{
		$config = [];
		
		if( isset( $this->config[ self::TABLE_STR ] ) )
		{
			$config[ self::TABLE_STR ] = $this->config[ self::TABLE_STR ];
		}

		if( isset( $this->config[ self::PRIMARY_ID_STR ] ) )
		{
			$config[ self::PRIMARY_ID_STR ]	= $this->config[ self::PRIMARY_ID_STR ];
		}

		if( isset( $this->config[ self::AES_DECRYPT_STR ] ) )
		{
			$config[ self::AES_DECRYPT_STR ] = $this->config[ self::AES_DECRYPT_STR ];
		}

		if( isset( $this->config[ self::EXCLUDE_ID ] ) )
		{
			$config[ self::EXCLUDE_ID ] = $this->config[ self::EXCLUDE_ID ];
		}

		if( isset( $this->config[ self::EXCLUDE_VALUE ] ) )
		{
			$config[ self::EXCLUDE_VALUE ] = $this->config[ self::EXCLUDE_VALUE ];
		}

		if( isset( $this->config[ self::EXCLUDE_VALUE ] ) )
		{
			$config[ self::EXCLUDE_VALUE ] = $this->config[ self::EXCLUDE_VALUE ];
		}

		if(is_array($queryConfig))
		{

			if( isset( $this->queryConfig[ self::TABLE_STR ] ) )
			{
				$config[ self::TABLE_STR ] = $this->queryConfig[ self::TABLE_STR ];
			}

			if( isset( $this->queryConfig[ self::PRIMARY_ID_STR ] ) )
			{
				$config[ self::PRIMARY_ID_STR ]	= $this->queryConfig[ self::PRIMARY_ID_STR ];
			}

			if( isset( $this->queryConfig[ self::AES_DECRYPT_STR ] ) )
			{
				$config[ self::AES_DECRYPT_STR ] = $this->queryConfig[ self::AES_DECRYPT_STR ];
			}

			if( isset( $this->queryConfig[ self::EXCLUDE_ID ] ) )
			{
				$config[ self::EXCLUDE_ID ] = $this->queryConfig[ self::EXCLUDE_ID ];
			}

			if( isset( $this->queryConfig[ self::EXCLUDE_VALUE ] ) )
			{
				$config[ self::EXCLUDE_VALUE ] = $this->queryConfig[ self::EXCLUDE_VALUE ];
			}

			if( isset( $this->queryConfig[ self::CALLABLE_QUERY_STR ] ) )
			{
				$config[ self::CALLABLE_QUERY_STR ] = $this->queryConfig[ self::CALLABLE_QUERY_STR ];
			}

			if( isset( $this->queryConfig[ self::JUST_INSTANCE_STR ] ) )
			{
				$config[ self::JUST_INSTANCE_STR ] = $this->queryConfig[ self::JUST_INSTANCE_STR ];
			}
		}
		else
		{
			if($queryConfig instanceof Logics_map)
			{
				$config[self::LOGICS_MAP_STR] = $queryConfig;
			}
		}

		if( is_callable( $queryConfig ) )
		{
			$this->queryConfig = $config;
			$this->callableQueryConfig = $queryConfig;
		}
		else
		{
			if( !empty( $queryConfig ) && is_array( $queryConfig ) )
			{
				$config = array_merge( $config, $queryConfig );
			}
		}

		return $config;
	}

	protected function processConfig($config)
	{
		$options 	= [
			self::CONNECTION_STR => '',
			self::USERNAME_STR => '',
			self::PASSWORD_STR => '',
			self::OPTIONS_STR => [],
			self::TABLE_STR => '',
			self::PRIMARY_ID_STR => '',
			self::AES_DECRYPT_STR => '',
			self::EXCLUDE_ID => '',
			self::EXCLUDE_VALUE => '',
		];

		if( is_string( $config ) )
		{
			$processOptions = $this->processStringConfig($config);

			$options = array_merge( $options, $processOptions );
		}
		else if( is_object( $config ) )
		{
			$options = $config;
		}
		else if( is_array( $config ) )
		{
			$processOptions = $this->processArrayConfig($config);

			$options = array_merge( $options, $processOptions );
		}

		return $options;
	}

	protected function processArrayConfig( array $config )
	{
		$host = null;
		$driver = null;
		$dbname = null;
		$port = self::DEFAULT_PORT;
		$connection = null;

		$optionArr = [];

		if( ISSET( $config['host'] ) )
		{
			$host = $config['host'];
		}

		if( ISSET( $config['driver'] ) )
		{
			$driver = $config['driver'];
		}

		if( ISSET( $config['dbname'] ) )
		{
			$dbname = $config['dbname'];
		}

		if( ISSET( $config['port'] ) )
		{
			$port = $config['port'];
		}

		if( !empty( $host ) && !empty( $driver ) && !empty( $dbname ) )
		{
			$connection = $driver.':host='.$host.';port='.$port.';dbname='.$dbname;
		}
		else if( isset( $config[ self::CONNECTION_STR ] ) )
		{
			$connection = $config[ self::CONNECTION_STR ];
		}

		if( !empty( $connection ) )
		{
			$optionArr = $config;
			$optionArr['connection'] = $connection;
		}

		return $optionArr;
	}

	protected function processStringConfig( $config )
	{
		$configPieces = explode('|', $config);

		$configArr = [];

		$configArr['connection'] = $configPieces[0];

		unset( $configPieces[0] );

		foreach( $configPieces as $piece )
		{
			$subPiece = explode('=', $piece);

			if( in_array($subPiece[0], $this->allowedStr, true) )
			{
				$configArr[ $subPiece[0] ] = $subPiece[1];
			}
			else if( $subPiece[0] == self::OPTIONS_STR )
			{
				$option = $this->processStringOptions( $subPiece[1] );

				$configArr[ $subPiece[0] ] = $option;
			}
		}

		return $configArr;
	}

	protected function processStringOptions( $optionString )
	{
		$optionPiece = explode(',', $optionString);
		$optionArr = [];

		foreach( $optionPiece as $piece )
		{
			$subPiece = explode('@', $piece);

			$optionArr[$subPiece[0]] = $subPiece[1];
		}

		return $optionArr;
	}

	public function run( $value, $satisfier = null, $field = null )
	{
		$check = false;
		$result = null;
		$appendMsg = '';

		if( 
			isset($this->queryConfig[self::LOGICS_MAP_STR]) 
			&& !empty($this->queryConfig[self::LOGICS_MAP_STR])
			&& (
				$this->queryConfig[self::LOGICS_MAP_STR] instanceof Logics_map
			)
		)
		{
			$args = [
				'db' => $this->db,
				'queryConfig' => $this->queryConfig
			];

			$whenObj = $this->queryConfig[self::LOGICS_MAP_STR];

			$resultArr = $whenObj->deferToWhen()->runLogics($value, $args);

			if(is_bool($resultArr))
			{
				$result = $resultArr;	
			}
		}
		else if( 
			isset($this->queryConfig[self::CALLABLE_QUERY_STR]) 
			&& !empty($this->queryConfig[self::CALLABLE_QUERY_STR])
			&& (
				is_callable($this->queryConfig[self::CALLABLE_QUERY_STR])
			)
		)
		{
			$args = [$this->db, $value, $this->queryConfig];
			$resultArr = call_user_func_array($this->queryConfig[self::CALLABLE_QUERY_STR], $args);

			if(is_array($resultArr))
			{
				if(isset($resultArr['check']))
				{
					$result = $resultArr['check'];
				}

				if(isset($resultArr['main_table']))
				{
					$table = $resultArr['main_table'];

					if(!empty($table))
					{
						$this->table = $table;	
					}
				}
			}
			else
			{
				if(is_bool($resultArr))
				{
					$result = $resultArr;	
				}
			}
		}
		else
		{
			$table = ( isset( $this->queryConfig[ self::TABLE_STR ] ) ) ? $this->queryConfig[ self::TABLE_STR ] : '';
			$id = ( isset( $this->queryConfig[ self::PRIMARY_ID_STR ] ) ) ? $this->queryConfig[ self::PRIMARY_ID_STR ] : '';

			$aes_decrypt = ( isset( $this->queryConfig[ self::AES_DECRYPT_STR ] ) ) ? $this->queryConfig[ self::AES_DECRYPT_STR ] : '';
			
			if( !empty( $this->db )  )
			{
				if(empty($this->setQuery))
				{
					$qb = $this->db
						->select( "COUNT(".$id.") as check_id" )
						->from( $table );

					if( !empty( $aes_decrypt ) )
					{
						$id = $this->Fadd_aes_decrypt($aes_decrypt)
					        	->cacheFilter( 'value' )
					            ->filterSingleValue( $id, true );
					}
		       	}

		       	if(!empty($value))
		       	{
		       		if(!empty($this->setQuery))
		       		{
		       			$this->setQuery->where( $id, $value );
		       		}
		       		else
		       		{
		       			$qb->where( $id, $value );	
		       		}
		       	}

				if( 
					( 
						isset( $this->queryConfig[ self::EXCLUDE_ID ] ) 
						&& !empty( $this->queryConfig[ self::EXCLUDE_ID ] )
					)
					&& 
					(
						isset( $this->queryConfig[ self::EXCLUDE_VALUE ] ) 
						&& !empty( $this->queryConfig[ self::EXCLUDE_VALUE ] )
					)

				)
				{
					if(empty($this->setQuery))
					{
						$qb->where( $this->queryConfig[ self::EXCLUDE_ID ], '!=', $this->queryConfig[ self::EXCLUDE_VALUE ] );
					}
				}

				$realQb = !empty($this->setQuery) ? $this->setQuery : $qb;

				if( !empty( $this->callableQueryConfig ) )
				{
					$args = [$realQb, $this->db, $value];
					$realQb = call_user_func_array($this->callableQueryConfig, $args);
				}

				$this->currentQuery = $realQb;
			}
			/*$result = $realQb->debug();
			var_dump($result);*/
			if(!empty($this->retryConfig) && !empty($value))
			{
				$times = $this->retryConfig['times'] ?? 3;
				$sleep 	= $this->retryConfig['sleep'] ?? 0;
				$whenCallback = $this->retryConfig['when'] ?? null;
				$attempt = 0;

				$callback = $this->retryConfig['callback'] ?? function($attempts, $realQb, $db, $paramsHolder) use(&$attempt)
				{
					$newQuery = $db->new_query($db);
					$newQuery = $newQuery->applyBindings($realQb);
					$newQuery->applyParams($paramsHolder);
					$result = null;
					
					try
					{
						$result = (isset($newQuery)) ? $newQuery->fetchColumn() : null;		
					}
					catch(\PDOException $e)
					{
						throw $e;
					}

					$attempt = $attempts;
					
					if(empty($result))
					{
						throw new \Exception('Empty result retrying ('.$attempts.').');
					}
					
					return $result;
				};

				$db = $this->db;
				$paramsHolder = $realQb->getParams();

				try
				{
					$result = static::retry($times, function($attempts) use ($callback, $realQb, $db, $paramsHolder)
					{
						return $callback($attempts, $realQb, $db, $paramsHolder);
					}, $sleep, $whenCallback);
					
				}
				catch(\PDOException $e)
				{
					$result = null;
				}
				catch(\Exception $e)
				{
					$result = null;
				}

				if(!empty($attempt))
				{
					$appendMsg = 'With '.$attempt.' retry/retries';
				}

				$realQb->reset_query();
			}
			else
			{
				if(!empty($value))
				{
					$result = (isset($realQb)) ? $realQb->fetchColumn() : null;
				}
			}
		}

		if( empty( $result ) )
		{
			$check = ( $this->reverseCheck ) ? true : false;
		}
		else 
		{
			$check = ( $this->reverseCheck ) ? false : true;
		}
		
		return [
			'check' => $check,
			'append_error' => $appendMsg
		];
	}

	public function query(callable $callback)
	{
		$qb = $callback($this->currentQuery, $this->db);

		if(!empty($qb))
		{
			$this->setQuery = $qb;
		}

		return $this->getReturn();
	}

	public function retryDb(array $retryConfig)
	{
		$this->retryConfig = $retryConfig;

		return $this->getReturn();
	}

	public function validate( $value )
    {
        $check = $this->run( $value );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
    }
}