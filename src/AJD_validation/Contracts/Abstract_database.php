<?php namespace AJD_validation\Contracts;

use PDO;
use Exception;
use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Helpers\Database;

abstract class Abstract_database extends Abstract_rule
{
	const USERNAME_STR 	= 'username',
		  PASSWORD_STR 	= 'password',
		  OPTIONS_STR 	= 'options',
		  TABLE_STR 	= 'table',
		  PRIMARY_ID_STR= 'primary_id',
		  CONNECTION_STR	= 'connection',
		  AES_DECRYPT_STR	= 'aes_decrypt',
		  DEFAULT_PORT 	= '3306',
		  EXCLUDE_ID 	= 'exclude_id',
		  EXCLUDE_VALUE = 'exclude_value';

	protected $allowedStr 	= array();
	protected $db;
	protected $config;
	protected $queryConfig 	= array();
	protected $callableQueryConfig;
	protected $reverseCheck = FALSE;

	public $table;
	public $primary_id;

	public function __construct($config, $queryConfig = array(), $callableQueryConfig= NULL)
	{
		$this->allowedStr 	= array( self::USERNAME_STR, self::PASSWORD_STR, self::TABLE_STR, self::PRIMARY_ID_STR, self::AES_DECRYPT_STR, self::EXCLUDE_ID, self::EXCLUDE_VALUE );
		
		$mainConfig 		= $this->processConfig($config);

		$this->config 		= $mainConfig;

		$queryConfig 		= $this->processQueryConfig($queryConfig);

		$this->queryConfig 	= $queryConfig;

		if( !EMPTY( $callableQueryConfig ) AND is_callable( $callableQueryConfig ) )
		{
			$this->callableQueryConfig	= $callableQueryConfig;
		}

		$validator 			= $this->getValidator();
		$paramValidator		= $validator->required();

		$this->processDbInstance($mainConfig);

		if( !$paramValidator->validate( $this->db ) )
		{
			throw new Exception('Database is required.');
		}

		if( EMPTY( $this->callableQueryConfig ) )
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

		$this->table 			= ( ISSET( $this->queryConfig[ self::TABLE_STR ] ) ) ? $this->queryConfig[ self::TABLE_STR ] : '';
		$this->primary_id 		= ( ISSET( $this->queryConfig[ self::PRIMARY_ID_STR ] ) ) ? $this->queryConfig[ self::PRIMARY_ID_STR ] : '';
	}

	protected function processDbInstance( $mainConfig )
	{
		if( !EMPTY( $mainConfig ) )
		{
			if( is_array( $mainConfig ) )
			{
				if( !EMPTY( $mainConfig['connection'] ) AND !EMPTY( $mainConfig['username'] ) AND ISSET( $mainConfig['password'] ) )
				{
					$this->db 	= new Database( $mainConfig['connection'], $mainConfig['username'], $mainConfig['password'], $mainConfig['options'] );
				}
				else if( !EMPTY( $mainConfig['connection'] ) )
				{
					if( ISSET( static::$dbConnections[ $mainConfig['connection'] ] ) )
					{
						$this->db = new Database( static::$dbConnections[ $mainConfig['connection'] ] );
					}
					else if( is_object( $mainConfig['connection'] ) )
					{
						$this->db = new Database( $mainConfig['connection'] );
					}
				}
			}
			else if( is_object( $mainConfig ) )
			{
				$this->db 		= new Database( $mainConfig );
			}
		}
	}

	protected function processQueryConfig($queryConfig)
	{
		$config 	= array();

		if( ISSET( $this->config[ self::TABLE_STR ] ) )
		{
			$config[ self::TABLE_STR ]		= $this->config[ self::TABLE_STR ];
		}

		if( ISSET( $this->config[ self::PRIMARY_ID_STR ] ) )
		{
			$config[ self::PRIMARY_ID_STR ]	= $this->config[ self::PRIMARY_ID_STR ];
		}

		if( ISSET( $this->config[ self::AES_DECRYPT_STR ] ) )
		{
			$config[ self::AES_DECRYPT_STR ]= $this->config[ self::AES_DECRYPT_STR ];
		}

		if( ISSET( $this->config[ self::EXCLUDE_ID ] ) )
		{
			$config[ self::EXCLUDE_ID ] 	= $this->config[ self::EXCLUDE_ID ];
		}

		if( ISSET( $this->config[ self::EXCLUDE_VALUE ] ) )
		{
			$config[ self::EXCLUDE_VALUE ] 	= $this->config[ self::EXCLUDE_VALUE ];
		}

		// 

		if( ISSET( $this->queryConfig[ self::TABLE_STR ] ) )
		{
			$config[ self::TABLE_STR ]		= $this->queryConfig[ self::TABLE_STR ];
		}

		if( ISSET( $this->queryConfig[ self::PRIMARY_ID_STR ] ) )
		{
			$config[ self::PRIMARY_ID_STR ]	= $this->queryConfig[ self::PRIMARY_ID_STR ];
		}

		if( ISSET( $this->queryConfig[ self::AES_DECRYPT_STR ] ) )
		{
			$config[ self::AES_DECRYPT_STR ]= $this->queryConfig[ self::AES_DECRYPT_STR ];
		}

		if( ISSET( $this->queryConfig[ self::EXCLUDE_ID ] ) )
		{
			$config[ self::EXCLUDE_ID ]= $this->queryConfig[ self::EXCLUDE_ID ];
		}

		if( ISSET( $this->queryConfig[ self::EXCLUDE_VALUE ] ) )
		{
			$config[ self::EXCLUDE_VALUE ]= $this->queryConfig[ self::EXCLUDE_VALUE ];
		}

		if( is_callable( $queryConfig ) )
		{
			$this->queryConfig 				= $config;
			$this->callableQueryConfig 		= $queryConfig;
		}
		else
		{
			if( !EMPTY( $queryConfig ) AND is_array( $queryConfig ) )
			{
				$config 					= array_merge( $config, $queryConfig );
			}
		}

		return $config;
	}

	protected function processConfig($config)
	{
		$options 	= array(
			self::CONNECTION_STR 	=> '',
			self::USERNAME_STR 		=> '',
			self::PASSWORD_STR 		=> '',
			self::OPTIONS_STR 		=> array(),
			self::TABLE_STR 		=> '',
			self::PRIMARY_ID_STR 	=> '',
			self::AES_DECRYPT_STR 	=> '',
			self::EXCLUDE_ID 		=> '',
			self::EXCLUDE_VALUE 	=> ''
		);

		if( is_string( $config ) )
		{
			$processOptions 		= $this->processStringConfig($config);

			$options 				= array_merge( $options, $processOptions );
		}
		else if( is_object( $config ) )
		{
			$options 				= $config;
		}
		else if( is_array( $config ) )
		{
			$processOptions 		= $this->processArrayConfig($config);

			$options 				= array_merge( $options, $processOptions );
		}

		return $options;
	}

	protected function processArrayConfig( array $config )
	{
		$host 		= NULL;
		$driver 	= NULL;
		$dbname 	= NULL;
		$port 		= self::DEFAULT_PORT;
		$connection = NULL;

		$optionArr 	= array();

		if( ISSET( $config['host'] ) )
		{
			$host 	= $config['host'];
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
			$port 	= $config['port'];
		}

		if( !EMPTY( $host ) AND !EMPTY( $driver ) AND !EMPTY( $dbname ) )
		{
			$connection 	= $driver.':host='.$host.';port='.$port.';dbname='.$dbname;
		}
		else if( ISSET( $config[ self::CONNECTION_STR ] ) )
		{
			$connection 	= $config[ self::CONNECTION_STR ];
		}

		if( !EMPTY( $connection ) )
		{
			$optionArr 					= $config;
			$optionArr['connection']	= $connection;
		}

		return $optionArr;
	}

	protected function processStringConfig( $config )
	{
		$configPieces 	= explode('|', $config);

		$configArr 		= array();

		$configArr['connection']	= $configPieces[0];

		unset( $configPieces[0] );

		foreach( $configPieces as $piece )
		{
			$subPiece 	= explode('=', $piece);

			if( in_array($subPiece[0], $this->allowedStr, TRUE) )
			{
				$configArr[ $subPiece[0] ] 	= $subPiece[1];
			}
			else if( $subPiece[0] == self::OPTIONS_STR )
			{
				$option 	= $this->processStringOptions( $subPiece[1] );

				$configArr[ $subPiece[0] ] 	= $option;
			}
		}

		return $configArr;
	}

	protected function processStringOptions( $optionString )
	{
		$optionPiece 	= explode(',', $optionString);
		$optionArr 		= array();

		foreach( $optionPiece as $piece )
		{
			$subPiece 	= explode('@', $piece);

			$optionArr[$subPiece[0]] 	= $subPiece[1];
		}

		return $optionArr;
	}

	public function run( $value, $satisfier = NULL, $field = NULL )
	{
		$check 	= FALSE;

		$table 	= ( ISSET( $this->queryConfig[ self::TABLE_STR ] ) ) ? $this->queryConfig[ self::TABLE_STR ] : '';
		$id 	= ( ISSET( $this->queryConfig[ self::PRIMARY_ID_STR ] ) ) ? $this->queryConfig[ self::PRIMARY_ID_STR ] : '';

		$aes_decrypt 	= ( ISSET( $this->queryConfig[ self::AES_DECRYPT_STR ] ) ) ? $this->queryConfig[ self::AES_DECRYPT_STR ] : '';
		
		if( !EMPTY( $this->db ) AND !EMPTY( $value ) )
		{

			$qb 		= $this->db
							->select( "COUNT(".$id.") as check_id" )
							->from( $table );

			if( !EMPTY( $aes_decrypt ) )
			{
				$id 	= $this->Fadd_aes_decrypt($aes_decrypt)
	                        ->cacheFilter( 'value' )
	                        ->filterSingleValue( $id, TRUE );
            }

			$qb->where( $id, $value );

			if( 
				( 
					ISSET( $this->queryConfig[ self::EXCLUDE_ID ] ) 
					AND !EMPTY( $this->queryConfig[ self::EXCLUDE_ID ] )
				)
				AND 
				(
					ISSET( $this->queryConfig[ self::EXCLUDE_VALUE ] ) 
					AND !EMPTY( $this->queryConfig[ self::EXCLUDE_VALUE ] )
				)

			)
			{
				$qb->where( $this->queryConfig[ self::EXCLUDE_ID ], '!=', $this->queryConfig[ self::EXCLUDE_VALUE ] );
			}

			if( !EMPTY( $this->callableQueryConfig ) )
			{
				$args 	= array( $qb, $this->db, $value );

				$qb 	= call_user_func_array($this->callableQueryConfig, $args);
			}

			$result 	= $qb->fetchColumn();
			
			/*$result 	= $qb->debug();
			var_dump($result);*/
			
			if( EMPTY( $result ) )
			{
				$check 	= ( $this->reverseCheck ) ? TRUE : FALSE;
			}
			else 
			{
				$check 	= ( $this->reverseCheck ) ? FALSE : TRUE;
			}
		}

		return $check;
	}

	public function validate( $value )
    {
        $check              = $this->run( $value );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
    }
}