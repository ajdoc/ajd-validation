<?php namespace AJD_validation\Helpers;

use \PDO;
use AJD_validation\Config\Config;

class Database
{

	const FETCH        = 'fetch';
    const FETCH_ALL     = 'fetchAll';
    const FETCH_OBJ     = 'fetchObject';
    const FETCH_COL     = 'fetchColumn';

    const SELECT        = 'select';
    const TABLE         = 'table';
    const WHERE         = 'where';
    const JOIN          = 'join';
    const UNION         = 'union';
    const ON            = 'on';
    const GROUPBY       = 'groupby';
    const ORDERBY       = 'orderby';
    const HAVING        = 'having';
    const LIMIT         = 'limit';

    const DB_AND        = 'AND';
    const DB_OR         = 'OR';

    const ISNOTNULL     = 'IS NOT NULL';
    const ISNULL        = 'IS NULL';
    const BETWEEN       = 'BETWEEN';
    const IN            = 'IN';
    const LIKE          = 'LIKE';

    protected static $db;

    protected $connection;
    protected $dbuser;
    protected $dbpass;
    protected $dboptions;

    protected $table;

    protected $bindings                 = array(

        self::SELECT                    => array(),
        self::WHERE                     => array(),
        self::UNION                     => array(),
        self::JOIN                      => array(),
        self::ON                        => array(),
        self::GROUPBY                   => array(),
        self::ORDERBY                   => array(),
        self::HAVING                    => array(),
        self::LIMIT                     => array()

    );

    protected static $table_join;    
    protected $distinct                         = FALSE;
    
    protected static $params                    = array();

    protected static $select_sub                = array();

    protected static $_allowed_joins            = array(

        'right_join'    => 'RIGHT JOIN',
        'left_join'     => 'LEFT JOIN',
        'join'          => 'JOIN'

    );

    protected static $_allowed_union            = array(

        'union'             => 'UNION',
        'union_all'         => 'UNION ALL',
        'union_distinct'    => 'UNION DISTINCT'

    );

    protected static $_fetch_type_array         = array(

        'fetch'         => self::FETCH,
        'fetchall'      => self::FETCH_ALL,
        'fetchobject'   => self::FETCH_OBJ,
        'fetchcolumn'   => self::FETCH_COL

    );

    protected static $_no_operator              = array(

        self::ISNOTNULL,
        self::ISNULL

    );

    protected static $_special_where            = array(

        self::BETWEEN,
        self::IN

    );

    protected static $_sql_function             = array(
        'lower',
        'upper',
        'concat',
        'ifnull'
    );

    protected $defaultOptions                   = array();

    public function __construct( $connection = NULL, $dbuser= NULL, $dbpass= NULL, $options = array() )
    {

        $this->connection           = $connection;
        $this->dbuser               = $dbuser;
        $this->dbpass               = $dbpass;
        $this->dboptions            = $options;

        $this->defaultOptions       = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        $this->process_connection( $connection, $dbuser, $dbpass, $options );

    }

    protected function process_connection( $connection, $dbuser, $dbpass, $options = array() )
    {


        $config                   = new Config( 'common_config.php' );

        if( !is_object( $connection ) ) 
        {

            try 
            {

                $user               = $config::get( 'db_config.dbuser' );
                $pass               = $config::get( 'db_config.dbpass', '' );

                if( EMPTY( $connection ) ) 
                {

                    $driver         = $config::get( 'db_config.driver' );
                    $host           = $config::get( 'db_config.host' );
                    $dbname         = $config::get( 'db_config.dbname' );

                    $connection     = $driver.':host='.$host.';dbname='.$dbname;

                } 
                else 
                {
                    $connection     = $connection;

                }

                if( !EMPTY( $dbuser ) ) 
                {

                    $user           = $dbuser;

                } 

                if( !EMPTY( $dbpass ) ) 
                {

                    $pass           = $dbpass;

                }

                $options            = array_merge( $options, $this->defaultOptions );

                static::$db = new PDO( $connection, $user, $pass, $options );

            } 
            catch( PDOException $e ) 
            {
                static::$db = NULL;

            }

        } 
        else 
        {

            static::$db     = $connection;

        }

    }

    public function execute_query( $query, $params = array(), $fetch_type = self::FETCH )
    {

        $result     = NULL;

        try 
        {

            $fetch_type     = strtolower( $fetch_type );

            $db             = static::$db;
            
            $stmt           = $db->prepare( $query );
            $stmt->execute( $params );

            if( $fetch_type != FALSE ) 
            {

                $fetch          = static::$_fetch_type_array[ $fetch_type ];

                if( $fetch == self::FETCH OR $fetch == self::FETCH_ALL ) 
                {
                    
                    $result     = $stmt->{ $fetch }( PDO::FETCH_ASSOC );

                } 
                else 
                {

                    $result     = $stmt->{ $fetch }();

                }

            } 
            else 
            {

                $result         = $stmt;

            }

        } 
        catch( PDOException $e ) 
        {
            throw $e;

        }

        static::$params         = array();

        return $result;

    }

    protected function flatten_array( array $arr )
    {
        $flat_arr           = iterator_to_array(

            new \RecursiveIteratorIterator(

                new \RecursiveArrayIterator( $arr )

            ), false

        );

        return $flat_arr;
    }

    protected function new_query()
    {

        return new static( $this->connection, $this->dbuser, $this->dbpass, $this->dboptions );

    }   

    protected function add_bindings( $value, $type = self::SELECT, $key = NULL, $multiple = FALSE )
    {

        if ( !array_key_exists( $type, $this->bindings ) ) 
        {
            
            throw new \InvalidArgumentException( "Invalid binding type: {$type}." );

        }

        if ( is_array( $value ) ) 
        {
            
            $this->bindings[ $type ]                 = array_values( array_merge( $this->bindings[ $type ], $value ) );

        } 
        else 
        {

            if( !EMPTY( $key ) ) 
            {

                if( $multiple ) 
                {

                    $this->bindings[ $type ][ $key ][]  = $value;                    

                } 
                else 
                {

                    $this->bindings[ $type ][ $key ]    = $value;

                }

            } 
            else 
            {

                $this->bindings[ $type ][]          = $value;

            }

        }

        return $this;
    }

    protected function get_bindings( $type, $key = NULL )
    {

        $val        = NULL;

        if( !EMPTY( $key ) ) 
        {

            $bindings   = $this->bindings[ $type ];

            if( ISSET( $bindings[ $key ] ) ) 
            {

                $val    = $bindings[ $key ];

            }

        } 
        else 
        {

            $val    = $this->bindings[ $type ];

        }

        return $val;

    }

    protected function reset_bindings( $type, $key = NULL )
    {

        if( !EMPTY( $key ) ) 
        {

            $this->bindings[ $type ][ $key ]    = array();

        } 
        else 
        {

            $this->bindings[ $type ]            = array();

        }

    }

    protected function unset_bindings( $type, $key = NULL )
    {

        if( !EMPTY( $key ) ) 
        {

            unset( $this->bindings[ $type ][ $key ] );

        } 
        else 
        {

            unset( $this->bindings[ $type ] );

        }

    }

    protected function reset_all_bindings()
    {
        $keys       = array_keys( $this->bindings );
        
        foreach ( $keys as $k => $type ) 
        {
            
            $this->reset_bindings( $type );

        }

    }

    public function reset_query()
    {
        $this->reset_all_bindings();
        $this->clear_params();
    }

    public function getPdo()
    {
        return static::$db;
    }

    public function bindValue( $value )
    {
        static::$params[]       = $value;

        return $this;
    }

    public function bindValues( array $values )
    {
        foreach( $values as $value )
        {
            static::$params[]   = $value;
        }

        return $this;
    }

    public function from( $table )

    {

        if( $table instanceof \Closure ) 
        {

            $table( $this->new_query() );

            $this->table    = static::$select_sub;

        } 
        else 
        {

            $this->table    = $table;

        }

        return $this;

    }

    public function distinct()
    {

        $this->distinct     = TRUE;

        return $this;

    }

    protected function process_args( $args, $type = self::SELECT )    
    {

        if( $args instanceof \Closure ) 
        {

            $args( $this->new_query() );

            $this->add_bindings( static::$select_sub, $type );

        }

        if( is_array( $args ) ) 
        {

            foreach ( $args as $key => $value ) 
            {
                
                if( $value instanceof \Closure ) 
                {

                    $value( $this->new_query() );

                    $this->add_bindings( static::$select_sub, $type );

                } 
                else 
                {

                    $this->add_bindings( $value, $type );          

                }

            }

        }

    }

    public function select( $fields = array( '*' ) )
    {

        $fields         = ( is_array( $fields ) ) ? $fields : func_get_args();

        $this->process_args( $fields );

        return $this;

    }

    public function subQuery( $alias = NULL, $union = FALSE )
    {

        $sub        = $this->get( NULL, TRUE );
        
        if( !EMPTY( $this->table ) ) 
        {

            $sub    = ( $union ) ? $sub : '( '. $sub. ' )';

            $sub    = ( !EMPTY( $alias ) ) ? $sub.' as '.$alias : $sub;

            static::$table_join = ( !EMPTY( $alias ) ) ? $alias : NULL;

        }
        
        static::$select_sub = $sub;

    }

    public function runUnion()
    {

        $this->subQuery( NULL, TRUE );

    }

    public function where( $columns, $operator = NULL, $value = NULL, $logic = self::DB_AND, $multiple = FALSE, $having = FALSE, $raw = FALSE )
    {

        $table                              = ( $having ) ? $this->table : static::$table_join;
        $placeholder                        = '';
        
        if ( func_num_args() == 2 ) 
        {

            list( $value, $operator ) = array( $operator, '=' );

        }

        if( !in_array( $operator, static::$_no_operator ) ) 
        {

            if( is_array( $value ) ) 
            {

                foreach( $value as $k => $v ) 
                {

                    static::$params[]       = $v;
                    $placeholder           .= '?,';

                }

            } 
            else 
            {    

                $placeholder            = '?';

                if( !EMPTY( $value ) AND !$raw ) 
                {

                    static::$params[]    = $value;

                }

                /*if( EMPTY( $value ) AND !$raw )
                {
                    static::$params[]      = '';
                }*/

            }

        }

        $placeholder                        = rtrim( $placeholder, ',' );

        if( $raw )
        {
            $placeholder                    = $value;
        }

        if( $columns instanceof \Closure ) 
        {

            $table                          = ( $having ) ? $this->table : static::$table_join;

            $columns( $this->new_query() );

            if( !EMPTY( $operator ) ) 
            {

                if( !in_array( $operator, static::$_no_operator ) AND !in_array( $operator, static::$_special_where ) ) 
                {

                     $query_where           = static::$select_sub.' '.$operator.' '.$placeholder.' ';

                } 
                else if( strtoupper( $operator ) == self::IN ) 
                {

                    $query_where            = static::$select_sub.' '.$operator.' ('.$placeholder.') ';

                } 
                else if( strtoupper( $operator ) == self::BETWEEN ) 
                {

                    $query_where            = ( $raw ) ?  static::$select_sub.' '.$operator.' '.$placeholder.' ' : static::$select_sub.' '.$operator.' ? AND ? ';

                } 
                else 
                {

                    $query_where            = static::$select_sub.' '.$operator.' ';

                }
                
            } 
            else 
            {

                $query_where                = static::$select_sub.' ';

            }
            

        } 
        else 
        {

            if( !in_array( $operator, static::$_no_operator ) AND !in_array( $operator, static::$_special_where ) ) 
            {

              $query_where              = $columns.' '.$operator.' '.$placeholder.' ';

            } 
            else if( strtoupper( $operator ) == self::IN ) 
            {

                $query_where            = $columns.' '.$operator.' ('.$placeholder.') ';

            } 
            else if( strtoupper( $operator ) == self::BETWEEN ) 
            {

                $query_where            = ( $raw ) ?  $columns.' '.$operator.' '.$placeholder.' ' : $columns.' '.$operator.' ? AND ? ';

            } 
            else 
            {

                $query_where            = $columns.' '.$operator.' ';

            }

        }

        $query_where                    = $logic.' '.$query_where;            
        
        if( $multiple ) 
        {
            
            $this->add_bindings( $query_where, self::WHERE, $table, TRUE );

        } 
        else 
        {

            $this->add_bindings( $query_where, self::WHERE );

        }

        return $this;

    }

    public function whereRaw( $columns, $operator = NULL, $value = NULL )
    {
        $this->where( $columns, $operator, $value, self::DB_AND, FALSE, FALSE, TRUE );

        return $this;
    }

    public function whereOrRaw( $columns, $operator = NULL, $value = NULL )
    {
        $this->where( $columns, $operator, $value, self::DB_OR, FALSE, FALSE, TRUE );

        return $this;
    }

    public function whereOr( $columns, $operator = NULL, $value = NULL )
    {

        $this->where( $columns, $operator, $value, self::DB_OR );

        return $this;

    }

    public function whereNotNull( $column, $type = self::DB_AND )
    {

        $this->where( $column, self::ISNOTNULL, NULL, $type );

        return $this;
    }

    public function whereIsNull( $column,  $type = self::DB_AND )
    {

        $this->where( $column, self::ISNULL, NULL, $type );

        return $this;   
    }

    public function whereOrIsNull( $column )
    {

        $this->whereIsNull( $column, self::DB_OR );

        return $this;
    }

    public function whereOrNotNull( $column )
    {

        $this->whereNotNull( $column, self::DB_OR );

        return $this;
    }

    public function whereLike( $column, $value = NULL, $type = self::DB_AND )
    {
        $this->where( $column, self::LIKE, $value, $type );

        return $this;
    }

    public function whereOrLike( $column, $value = NULL )
    {
        $this->where( $column, self::LIKE, $value, self::DB_OR );

        return $this;
    }

    public function whereBetween( $column, $one = NULL, $two = NULL, $type = self::DB_AND )
    {

        $value          = NULL;

        if( !EMPTY( $one ) AND !EMPTY( $two ) ) 
        {

            $value      = array( $one, $two );

        }

        $this->where( $column, self::BETWEEN, $value, $type );

        return $this;
    }

    public function whereOrBetween( $column, $one = NULL, $two = NULL )
    {

        $value          = NULL;

        if( !EMPTY( $one ) AND !EMPTY( $two ) ) 
        {

            $value      = array( $one, $two );

        }

        $this->where( $column, self::BETWEEN, $value, self::DB_OR );

        return $this;
    }

    public function whereIn( $column, $value = NULL, $type = self::DB_AND )
    {

        $this->where( $column, self::IN, $value, $type );

        return $this;
    }

    public function whereOrIn( $column, $value = NULL )
    {

        $this->where( $column, self::IN, $value, self::DB_OR );

        return $this;
    }

    public function join( $table, $type = 'join' )
    {

        $join       = '';

        $type       = ( ISSET( static::$_allowed_joins[ $type ] ) ) ? static::$_allowed_joins[ $type ] : 'JOIN';
        
        if( $table instanceof \Closure ) 
        {
            
            $table( $this->new_query() );

            $join   = $type.' '.static::$select_sub;

        } 
        else 
        {

            static::$table_join     = $table;

            $join   = $type.' '.$table;

        }

        $this->add_bindings( $join, self::JOIN, static::$table_join );

    
        return $this;

    }

    public function rightJoin( $table )
    {

        $this->join( $table, 'right_join' );

        return $this;

    }

    public function leftJoin( $table )
    {

        $this->join( $table, 'left_join' );

        return $this;

    }

    public function on( $one, $operator = NULL, $two = NULL, $logic = self::DB_AND )
    {

        if( func_num_args() == 2 ) 
        {

            list( $two, $operator ) = array( $operator, '=' );

        }

        if( $one instanceof \Closure ) 
        {

            $one( $this->new_query() );

            $on                         = static::$select_sub;
        
        } 
        else 
        {

            $on                         = $logic.' '.$one.' '.$operator.' '.$two;    

        }

        $this->add_bindings( $on, self::ON, static::$table_join, TRUE );

        return $this;

    }

    public function orOn( $one, $operator = NULL, $two = NULL )
    {

        $this->on( $one, $operator, $two, self::DB_OR );

        return $this;

    }

    public function joinWhere( $columns, $operator = NULL, $value = NULL )
    {


        $this->where( $columns, $operator, $value, self::DB_AND, TRUE );

        return $this;

    }

    public function joinOrWhere( $columns, $operator = NULL, $value = NULL )
    {

         
        $this->where( $columns, $operator, $value, self::DB_OR, TRUE );

        return $this;

    }

    public function union( \Closure $func, $union_type = 'union' )
    {

        $func( $this->new_query() );

        $union_type    = ( ISSET( static::$_allowed_union[ $union_type ] ) ) ? static::$_allowed_union[ $union_type ] : 'UNION';

        $union         = $union_type.' ( '.static::$select_sub. ' ) ';

        $this->add_bindings( $union, self::UNION );

        return $this;
 
    }

    public function unionAll( \Closure $func )
    {

        $this->union( $func, 'union_all' );

        return $this;

    }

    public function unionDistinct( \Closure $func )
    {

        $this->union( $func, 'union_distinct' );

        return $this;

    }

    public function groupBy( $fields )
    {

        $fields         = ( is_array( $fields ) ) ? $fields : func_get_args();

        $this->process_args( $fields, self::GROUPBY );

        return $this;

    }

    public function orderBy( $fields )
    {

        $fields         = ( is_array( $fields ) ) ? $fields : func_get_args();

        $this->process_args( $fields, self::ORDERBY );

        return $this;

    }

    public function limit( $fields )
    {
        $fields         = ( is_array( $fields ) ) ? $fields : func_get_args();

        $this->process_args( $fields, self::LIMIT );

        return $this;
    }

    public function having( $columns, $operator = NULL, $value = NULL, $type = self::DB_AND )
    {

        $this->where( $columns, $operator, $value, $type, TRUE, TRUE );

        if( ISSET( $this->bindings[ self::WHERE ][ $this->table ] ) ) 
        {

            $where                      = $this->bindings[ self::WHERE ][ $this->table ];

        } 
        else 
        {

            $where                      = $this->bindings[ self::WHERE ];

        }

        $this->bindings[ self::HAVING ] = $where;

        return $this;
    }

    public function havingOr( $columns, $operator = NULL, $value = NULL )
    {

        $this->having( $columns, $operator, $value, self::DB_OR );

        return $this;
    }

    protected function process_join( $join )
    {

        $join_query             = "";

        $where                  = "";
        
        foreach( $join as $key => $query ) 
        {

            $on_val             = $this->get_bindings( self::ON, $key );
            $on_val             = implode( "\n", $on_val );            
            $on_val             = preg_replace( '/^(AND|OR)/', '', $on_val );
            $on_val             = trim( $on_val );

            $join_query        .= $query.' ON '.$on_val."\n";

            $join_where         = $this->get_bindings( self::WHERE, $key );

            if( !EMPTY( $join_where ) ) 
            {

                $join_where     = implode( ' ', $join_where );

                $where          = ' '.$join_where;

                $join_query    .= $where.' ';

                $this->unset_bindings( self::WHERE, $key );

            }

        }

        return $join_query;

    }

    public function get( $fetch_type = self::FETCH_ALL, $subquery = FALSE )
    {

        $query      = $this->build_query();

        if( $subquery ) 
        {

            return $query;

        } 
        else 
        {
           
            return $this->execute_query( $query, static::$params, $fetch_type );

        }

    }

    public function debug()
    {

        $arr            = array( 

            'query'     => $this->get( NULL, TRUE ),
            'params'    => static::$params

        );

        
        $return         = '<pre>'.var_export( $arr, TRUE ).'</pre>';

        $this->clear_params();

        return $return;
    }

    public function get_query()
    {
        return $this->get( NULL, TRUE );
    }

    public function fetchAll( $config = PDO::FETCH_ASSOC )
    {

        $query      = $this->build_query();
        
        return  $this->execute_query( $query, static::$params, FALSE )->fetchAll( $config );

    }

    public function fetch( $config = PDO::FETCH_ASSOC )
    {

        $query      = $this->build_query();

        return  $this->execute_query( $query, static::$params, FALSE )->fetch( $config );

    }

    public function fetchObject()
    {
        
        $query      = $this->build_query();

        return  $this->execute_query( $query, static::$params, FALSE )->fetchObject();        

    }

    public function fetchColumn()
    {
        $query      = $this->build_query();

        return  $this->execute_query( $query, static::$params, FALSE )->fetchColumn();        
    }

    public function fetchAllJson()
    {

        return json_encode( $this->fetchAll() );

    }

    public function fetchJson()
    {

        return json_encode( $this->fetch() );

    }

    public function first()
    {

        $query      = $this->build_query();

        $result     = $this->execute_query( $query, static::$params, self::FETCH_ALL );

        return ISSET( $result[0] ) ? $result[0] : $result;

    }

    public function find( $key )
    {

        $query      = $this->build_query();

        $result     = $this->execute_query( $query, static::$params, self::FETCH_ALL );

        return ISSET( $result[ $key ] ) ? $result[ $key ] : array();
    }

    public function rowCount()
    {

        $query      = $this->build_query();

        return  $this->execute_query( $query, static::$params, FALSE )->rowCount();        

    }

    protected function generate_sql_func_regex()
    {
        $regex      = implode( '|', static::$_sql_function );

        return $regex;
    }

    protected function build_query()
    {

        $query          = '';
        $where          = '';
        $join           = '';
        $sel            = '*';
        $groupby        = '';
        $orderby        = '';
        $having         = '';
        $limit          = '';
        $distinct       = ( $this->distinct ) ? 'DISTINCT' : '';
    
        $table          = $this->table;
        $sql_func       = $this->generate_sql_func_regex();

        $union          = $this->get_bindings( self::UNION );
        $join_val       = $this->get_bindings( self::JOIN );
        $sel_val        = $this->get_bindings( self::SELECT );
        $on_val         = $this->get_bindings( self::ON, static::$table_join );
        $groupby_val    = $this->get_bindings( self::GROUPBY );
        $orderby_val    = $this->get_bindings( self::ORDERBY );
        $having_val     = $this->get_bindings( self::HAVING );
        $limit_val      = $this->get_bindings( self::LIMIT );

        if( !EMPTY( $sel_val ) ) 
        {

            $sel        = implode( ',', $sel_val );
        }



        if( !EMPTY( $join_val ) ) 
        {

            $join       = $this->process_join( $join_val );

        }

        if( ISSET( $this->bindings[ self::WHERE ][ $this->table ] ) ) 
        {

            $this->unset_bindings( self::WHERE, $table );

        }

        $where_val      = $this->get_bindings( self::WHERE );

        if( !EMPTY( $where_val  ) ) 
        {    

            $where      = implode( ' ', $this->flatten_array( $where_val ) );
            $where      = trim( preg_replace( '/^(AND|OR)/', '', $where ) );
            $where      = 'WHERE '.$where;
          
            $regex_no_operator  = '/[\)][\s]('.implode( '|', static::$_no_operator ).')/';
            
            if( preg_match( $regex_no_operator , $where ) ) 
            {
                
                $where          = trim( preg_replace( '/[^($sql_func)\(\w][\)][\s](IS NULL|IS NOT NULL)/', ' ) ', $where ) );

            }

            if( preg_match( '/[\)][\s](BETWEEN)/', $where ) ) 
            {

                $regex          = '/[^('.$sql_func.')\(\w][\)][\s](BETWEEN[\s][\?][\s]AND[\s][\?])/i';
                $where          = trim( preg_replace( $regex, ' ) ', $where ) );

            }

            if( preg_match( '/[\)][\s](IN)/', $where ) ) 
            {

                $where          = trim( preg_replace( '/[^($sql_func)\(\w][\)][\s](IN[\s][\(][\?][\)])/i', ' ) ', $where ) ); 

            }

            if( preg_match('/[\)][\s](LIKE)/', $where ) ) 
            {

                $where          = trim( preg_replace( '/[^($sql_func)\(\w][\)][\s](LIKE[\s][\?])/i', ' ) ', $where ) );

            }

        }

        if( !EMPTY( $having_val ) ) 
        {

            $having     = implode( ' ', $having_val );
            $having     = preg_replace( '/^(AND|OR)/', '', $having );
            $having     = trim( $having );
            $having     = 'HAVING '.$having;

        }

        if( !EMPTY( $groupby_val ) ) 
        {

            $groupby    = implode( ',', $groupby_val );
            $groupby    = ' GROUP BY '.$groupby;

        }

        if( !EMPTY( $orderby_val ) ) 
        {

            $orderby   = implode( ',', $orderby_val );
            $orderby   = ' ORDER BY '.$orderby;

        }

        if( !EMPTY( $limit_val ) ) 
        {

            $limit     = implode( ',', $limit_val );
            $limit     = ' LIMIT '.$limit;

        }

        if( !EMPTY( $table ) ) 
        {

            $query    = "

                SELECT  $distinct $sel
                FROM    $table
                $join
                $where
                $groupby
                $having
                $orderby
                $limit
        
            ";

            $query    = str_replace(':blank', '""', $query);

        }
        
        if( !EMPTY( $union ) ) 
        {

            $union  = implode( "\n", $this->get_bindings( self::UNION ) );
            
            $query  = !EMPTY( $query ) ? $query.' '.$union : $union;

            if( preg_match( '/^(union)/i', $query ) ) 
            {

                $query = trim( preg_replace( '/^(union[\s](distinct|all)|union)/i' , '', $query ) );

            }

        }

        if( EMPTY( $table ) AND !EMPTY( $on_val )  ) 
        {
            
            $on_val       = implode( "\n", $on_val );            
            $on_val       = preg_replace( '/^(AND|OR)/', '', $on_val );
            $on_val       = trim( $on_val );

            $query         = "( $on_val )";

        }  

        if( EMPTY( $table ) AND !EMPTY( $where ) )
        {

            if( preg_match( '/^(WHERE)/', $where ) ) 
            {

                $where  = preg_replace( '/^(WHERE)/', '', $where );
                $where  = trim( $where );

            }

            $query      = "( $where )";

        }

        if( EMPTY( $table ) AND !EMPTY( $having ) ) 
        {

            if( preg_match( '/^(HAVING)/', $having ) ) 
            {

                $having  = preg_replace( '/^(HAVING)/', '', $having );
                $having  = trim( $having );

            }

            $query      = "( $having )";

        }


        if( EMPTY( $table ) AND !EMPTY( $groupby ) ) 
        {

            $query      = $query.$groupby;

        }

        if( EMPTY( $table ) AND !EMPTY( $orderby ) ) 
        {

            $query      = $query.$orderby;

        }
        
        $this->reset_all_bindings();

        return $query;

    }

    protected function clear_params()
    {
        static::$params     = array();
    }

}





