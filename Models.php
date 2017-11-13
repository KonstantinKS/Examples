<?php

class ModelMySQL
{
	
	protected $conn;
	protected $stats;
	protected $emode;
	protected $exname;	
	
	protected $defaults = array(
		'hostname'  => 'localhost',
		'username'  => 'root',
		'password'  => '',
		'database'  => 'test',
		'port'      => NULL,
		'socket'    => NULL,
		'pconnect'  => FALSE,
		'charset'   => 'utf8',
		'errmode'   => 'exception', //or 'error'
		'exception' => 'Exception', //Exception class name
	);
	
	const RESULT_ASSOC = MYSQLI_ASSOC;
	const RESULT_NUM   = MYSQLI_NUM;
	
	function __construct( $opt=[] )
		{
			$opt = array_merge($this->defaults,$opt);
			$this->emode  = $opt['errmode'];
			$this->exname = $opt['exception'];
			if (isset($opt['mysqli']))
			{
				if ($opt['mysqli'] instanceof mysqli)
				{
					$this->conn = $opt['mysqli'];
					return;
				} else {
					$this->error("mysqli option must be valid instance of mysqli class");
				}
			}
			if ($opt['pconnect'])
			{
				$opt['host'] = "p:".$opt['host'];
			}
			@$this->conn = mysqli_connect($opt['hostname'], $opt['username'], $opt['password'], $opt['database'], $opt['port'], $opt['socket']);
			if ( !$this->conn )
			{
				$this->error(mysqli_connect_errno()." ".mysqli_connect_error());
			}
			mysqli_set_charset($this->conn, $opt['charset']) or $this->error(mysqli_error($this->conn));
			unset($opt); // I am paranoid
		}
	
	public function query()
		{	
			return $this->rawQuery($this->prepareQuery(func_get_args()));
		}
		
	public function fetch($result, $mode=self::RESULT_ASSOC)
		{
			return mysqli_fetch_array($result, $mode);
		}
		
	public function insertId()
		{
			return mysqli_insert_id($this->conn);
		}
		
	public function free($result)
		{
			mysqli_free_result($result);
		}
		
	public function getOne()
		{
			$query = $this->prepareQuery(func_get_args());
			if ($res = $this->rawQuery($query))
			{
				$row = $this->fetch($res);
				if (is_array($row)) {
					return reset($row);
				}
				$this->free($res);
			}
			return FALSE;
		}
		
	public function getInd()
	{
		$args  = func_get_args();
		$index = array_shift($args);
		$query = $this->prepareQuery($args);
		$ret = array();
		if ( $res = $this->rawQuery($query) )
		{
			while($row = $this->fetch($res))
			{
				$ret[$row[$index]] = $row;
			}
			$this->free($res);
		}
		return $ret;
	}
		
	protected function rawQuery($query)
		{
			$start = microtime(TRUE);
			$res   = mysqli_query($this->conn, $query);
			$timer = microtime(TRUE) - $start;
			$this->stats[] = array(
				'query' => $query,
				'start' => $start,
				'timer' => $timer,
			);
			if (!$res)
			{
				$error = mysqli_error($this->conn);
				
				end($this->stats);
				$key = key($this->stats);
				$this->stats[$key]['error'] = $error;
				$this->cutStats();
				
				$this->error("$error. Full query: [$query]");
			}
			$this->cutStats();
			return $res;
		}	
	
	protected function prepareQuery($args)
		{
			$query = '';
			$raw   = array_shift($args);
			$array = preg_split('~(\?[nsiuap])~u', $raw, null, PREG_SPLIT_DELIM_CAPTURE);
			$anum  = count($args);
			$pnum  = floor(count($array) / 2);
			if ( $pnum != $anum )
			{
				$this->error("Number of args ($anum) doesn't match number of placeholders ($pnum) in [$raw]");
			}
			foreach ($array as $i => $part)
			{
				if ( ($i % 2) == 0 )
				{
					$query .= $part;
					continue;
				}
				$value = array_shift($args);
				switch ($part)
				{
					case '?n':
						$part = $this->escapeIdent($value);
						break;
					case '?s':
						$part = $this->escapeString($value);
						break;
					case '?i':
						$part = $this->escapeInt($value);
						break;
					case '?a':
						$part = $this->createIN($value);
						break;
					case '?u':
						$part = $this->createSET($value);
						break;
					case '?p':
						$part = $value;
						break;
				}
				$query .= $part;
			}
			return $query;
		}
		
	protected function error($err)
		{
			$err  = __CLASS__.": ".$err;
			if ( $this->emode == 'error' )
			{
				$err .= ". Error initiated in ".$this->caller().", thrown";
				trigger_error($err,E_USER_ERROR);
			} else {
				throw new $this->exname($err);
			}
		}
		
	protected function caller()
		{
			$trace  = debug_backtrace();
			$caller = '';
			foreach ($trace as $t)
			{
				if ( isset($t['class']) && $t['class'] == __CLASS__ )
				{
					$caller = $t['file']." on line ".$t['line'];
				} else {
					break;
				}
			}
			return $caller;
		}
		
	protected function escapeIdent($value)
		{
			if ($value)
			{
				return "`".str_replace("`","``",$value)."`";
			} else {
				$this->error("Empty value for identifier (?n) placeholder");
			}
		}
	
	protected function escapeString($value)
		{
			if ($value === NULL)
			{
				return 'NULL';
			}
			return	"'".mysqli_real_escape_string($this->conn,$value)."'";
		}
		
	protected function escapeInt($value)
		{
			if ($value === NULL)
			{
				return 'NULL';
			}
			if(!is_numeric($value))
			{
				$this->error("Integer (?i) placeholder expects numeric value, ".gettype($value)." given");
				return FALSE;
			}
			if (is_float($value))
			{
				$value = number_format($value, 0, '.', ''); // may lose precision on big numbers
			} 
			return $value;
		}
		
	protected function cutStats()
		{
			if ( count($this->stats) > 100 )
			{
				reset($this->stats);
				$first = key($this->stats);
				unset($this->stats[$first]);
			}
		}
		
		
		
	protected function get_path()
		{
			if ( !isset($_GET['url']) ) { return false; }
			$m_url = str_replace( ['.html', '.php'], '', $_GET['url'] );			
			$m_path = explode('/', rtrim($m_url, '/\\'));
			return $m_path;
		}
		
	public function slug( $m_path=false )
		{			
			if ( !$m_path ) { $m_path = $this->get_path(); }
			
			if ( empty($m_path) ) { return false; }			
			
			$m_path = array_reverse($m_path);			
			
			$m_slug = [
				'page' => '',
				'equipment' => '',
				'model' => ''
			];
			
			$tabs = [ 'equipments', 'models' ];			
			
			foreach ( $m_path as $k=>$slug ) :
				if   ( $slug==='models' && $k===0 && $m_slug['page']==='' ) { $m_slug['page'] = $slug; }
				else 
					{
						foreach ( $tabs as $tab ) :
							$res = $this->query( "SELECT `id` FROM `{$tab}` WHERE `slug`=?s", $slug );
							if     ( $res->num_rows && $k===0 && $m_slug['page']==='' && $tab==='equipments' ) { $m_slug['page'] = 'equipment'; $m_slug['equipment'] = $slug; break; }
							elseif ( $res->num_rows && $k!==0 && $m_slug['page']!=='' && $tab==='equipments' ) { $m_slug['equipment'] = $slug; break; }
							elseif ( $res->num_rows && $k===0 && $m_slug['page']==='' && $tab==='models' )     { $m_slug['page'] = 'model'; $m_slug['model'] = $slug; break; }
							elseif ( $res->num_rows && $k!==0 && $m_slug['page']!=='' && $tab==='equipments' ) { $m_slug['model'] = $slug; break; }
							elseif ( !$res->num_rows && $k===0 && $m_slug['page']!=='' && $tab==='equipments' ) { $m_slug['page'] = 'models'; break; }							
						endforeach;
					}
			endforeach;
			
			return $m_slug;
		}		
	
	protected function get_slug( $tab )
		{
			$m_path = $this->get_path();
			
			if ( empty($m_path) ) { return false; }			
						
			while ( empty($m_slug) && count($m_path)>0 ) :			
				$m_slug = array_pop($m_path);				
				$res = $this->query( "SELECT `id` FROM `{$tab}` WHERE `slug`=?s", $m_slug );
				if ( !$res->num_rows ) { $m_slug = false; }				
			endwhile;		
			if ( empty($m_slug) ) { return false; }
			else 				  { return $m_slug;	}		
		}	
		
	protected function get_slug_is( $tab )
		{			
			$m_path = $this->get_path();
			
			if ( empty($m_path) ) { return false; }	
						
			$m_slug = array_pop($m_path);
			if     ( $m_slug==='models' && $tab==='all_models' ) { return $m_slug; }
			elseif ( $tab==='all_models' )                       { return false; }
			else
				{
					$res = $this->query( "SELECT `id` FROM `{$tab}` WHERE `slug`=?s", $m_slug );
					if ( !$res->num_rows ) { $m_slug = false; }		
					if ( empty($m_slug) ) { return false; }
					else 				  { return $m_slug;	}
				}
		}		
		
	public function eq_title_pc()
		{			
			$slug = $this->get_slug( 'equipments' );
			if ( isset($slug) ) :
				return $this->getOne( "SELECT `name_parcase_plur` FROM `equipments` WHERE `slug`=?s", $slug );				
			else :
				return false;
			endif;			
		}
		
	public function popular_models()
		{
			$slug = $this->get_slug( 'equipments' );
			if ( isset($slug) ) :
				$equipments_id = $this->getOne( "SELECT `id` FROM `equipments` WHERE `slug`=?s", $slug );
			else :
				return false;
			endif;		
			if ( isset($equipments_id) ) :
				return $this->getInd( "slug", "SELECT `name`, `slug`, `image` FROM `models` WHERE `equipment`=?i AND `popular`=?i", $equipments_id, 1 );						
			else :
				return false;
			endif;			
		}
		
	public function all_models()
		{
			$slug = $this->get_slug( 'equipments' );
			if ( isset($slug) ) :
				$equipments_id = $this->getOne( "SELECT `id` FROM `equipments` WHERE `slug`=?s", $slug );
			else :
				return false;
			endif;		
			if ( isset($equipments_id) ) :
				return $this->getInd( "slug", "SELECT `name`, `slug`, `image` FROM `models` WHERE `equipment`=?i", $equipments_id );						
			else :
				return false;
			endif;			
		}
		
	public function is_equipment()
		{
			$slug = $this->get_slug_is( 'equipments' );							
			if ( $slug ) :					
				return $this->getOne( "SELECT `id` FROM `equipments` WHERE `slug`=?s", $slug );
			else :				
				return false;
			endif;
		}
		
	public function is_equipment_all_models()
		{			
			$slug = $this->get_slug_is( 'all_models' );			
			if ( $slug ) :
				return $slug;				
			else :
				return false;
			endif;			
		}
		
	public function is_model( $column='id' )
		{			
			$slug = $this->slug();
			
			if ( empty($slug) ) { return false; }
			
			if   ( $slug['page']==='model' && !empty($slug['model']) ) { $tab = 'models'; }
			else   														{ return false; }			
						
			return $this->getOne( "SELECT `{$column}` FROM `{$tab}` WHERE `slug`=?s", $slug['model'] );			
		}
		
	public function get_image_model()
		{
			return $this->is_model( 'image' );
		}
		
	private function placeholder( $value, $place )
		{		
			return str_replace(
				[
					"%brand%",
					"%eq_name_parcase_plur%",
					"%eq_name_parcase_singul%",
					"%eq_name_precase_plur%",
					"%eq_name_precase_singul%",
					"%model%",
					"%model_slug%",
					"%model_name%",
					"%eq_slug%",
					"%eq_title%",
					"%eq_title_singul%",
					"%brand_rus%"
				],
				[
					$place["brand"],
					$place["eq_name_parcase_plur"],
					$place["eq_name_parcase_singul"],
					$place["eq_name_precase_plur"],
					$place["eq_name_precase_singul"],
					$place["model"],
					$place["slug"],
					$place["name"],
					$place['eq_slug'],
					$place['eq_title'],
					$place['eq_title_singul'],
					$place['brand_rus']
				],
				$value
			);			
		}
		
	private function recarrayplace( $array, $place )
		{		
			foreach( $array as $k=>$v )
				{					
					if ( is_array($array[$k] )) { $array[$k] = $this->recarrayplace($v, $place); }
					else { $array[$k] = $this->placeholder($v, $place); } 
				}
			return $array;	
		}
		
	public function get_this_meta()
		{
			$slug = $this->slug();
			
			if ( empty($slug) ) { return false; }
			
			if     ( $slug['page']==='equipment' || $slug['page']==='models' ) { $tab = 'equipments'; $tab_meta = 'equipments_meta'; $column = 'equipment'; $index = 'equipment'; }
			elseif ( $slug['page']==='model' && !empty($slug['model']) )       { $tab = 'models'; $tab_meta = 'models_meta'; $column = 'model'; $index = 'model'; }
			
			if     ( $slug['page']==='models' )     { $meta_slug = $slug['equipment']; }
			elseif ( $slug['page']==='equipment' )  { $meta_slug = $slug['equipment']; }
			elseif ( $slug['page']==='model' )      { $meta_slug = $slug['model']; }
			else                                    { $meta_slug = $slug['model']; }
						
			$id_meta = $this->getOne( "SELECT `id` FROM `{$tab}` WHERE `slug`=?s", $meta_slug );
			
			if ( !$id_meta ) { return false; } 
			$meta = $this->getInd( "{$index}", "SELECT * FROM `{$tab_meta}` WHERE `{$column}`=?i", $id_meta );
			foreach ( $meta as $m ) : $meta = $m; endforeach;
			$meta['styles'] = unserialize( base64_decode( $meta['styles'] ) );
			$meta['scripts'] = unserialize( base64_decode( $meta['scripts'] ) );
			$meta['paths'] = unserialize( base64_decode( $meta['paths'] ) );
			
			$place = [
				'brand' => ( !empty($slug['equipment']) ) ? $this->getOne( "SELECT `brand` FROM `equipments` WHERE `slug`=?s", $slug['equipment'] ) : '' ,
				'brand_rus' => ( !empty($slug['equipment']) ) ? $this->getOne( "SELECT `brand_rus` FROM `equipments` WHERE `slug`=?s", $slug['equipment'] ) : '' ,
				'eq_name_parcase_plur' => ( !empty($slug['equipment']) ) ? $this->getOne( "SELECT `name_parcase_plur` FROM `equipments` WHERE `slug`=?s", $slug['equipment'] ) : '' ,
				'eq_name_parcase_singul' => ( !empty($slug['equipment']) ) ? $this->getOne( "SELECT `name_parcase_singul` FROM `equipments` WHERE `slug`=?s", $slug['equipment'] ) : '' ,
				'eq_name_precase_plur' => ( !empty($slug['equipment']) ) ? $this->getOne( "SELECT `name_precase_plur` FROM `equipments` WHERE `slug`=?s", $slug['equipment'] ) : '' ,
				'eq_name_precase_singul' => ( !empty($slug['equipment']) ) ? $this->getOne( "SELECT `name_precase_singul` FROM `equipments` WHERE `slug`=?s", $slug['equipment'] ) : '' ,
				'eq_slug' => ( !empty($slug['equipment']) ) ? $this->getOne( "SELECT `slug` FROM `equipments` WHERE `slug`=?s", $slug['equipment'] ) : '' ,
				'eq_title' => ( !empty($slug['equipment']) ) ? $this->getOne( "SELECT `title` FROM `equipments` WHERE `slug`=?s", $slug['equipment'] ) : '' ,
				'eq_title_singul' => ( !empty($slug['equipment']) ) ? $this->getOne( "SELECT `title_singul` FROM `equipments` WHERE `slug`=?s", $slug['equipment'] ) : '' ,
				'model' => ( !empty($slug['model']) ) ? $this->getOne( "SELECT `model` FROM `models` WHERE `slug`=?s", $slug['model'] ) : '' ,
				'slug' => ( !empty($slug['model']) ) ? $this->getOne( "SELECT `slug` FROM `models` WHERE `slug`=?s", $slug['model'] ) : '' ,
				'name' => ( !empty($slug['model']) ) ? $this->getOne( "SELECT `name` FROM `models` WHERE `slug`=?s", $slug['model'] ) : '' ,
			];
			
			return $this->recarrayplace($meta, $place);
		}
		
	public function replace( $content, $slug=false )
		{		
			if ( !$slug ) { $slug = $this->slug(); }			
			
			if ( empty($slug) ) { return false; }
			
			$place = [
				'brand' => ( !empty($slug['equipment']) ) ? $this->getOne( "SELECT `brand` FROM `equipments` WHERE `slug`=?s", $slug['equipment'] ) : '' ,
				'brand_rus' => ( !empty($slug['equipment']) ) ? $this->getOne( "SELECT `brand_rus` FROM `equipments` WHERE `slug`=?s", $slug['equipment'] ) : '' ,
				'eq_name_parcase_plur' => ( !empty($slug['equipment']) ) ? $this->getOne( "SELECT `name_parcase_plur` FROM `equipments` WHERE `slug`=?s", $slug['equipment'] ) : '' ,
				'eq_name_parcase_singul' => ( !empty($slug['equipment']) ) ? $this->getOne( "SELECT `name_parcase_singul` FROM `equipments` WHERE `slug`=?s", $slug['equipment'] ) : '' ,
				'eq_name_precase_plur' => ( !empty($slug['equipment']) ) ? $this->getOne( "SELECT `name_precase_plur` FROM `equipments` WHERE `slug`=?s", $slug['equipment'] ) : '' ,
				'eq_name_precase_singul' => ( !empty($slug['equipment']) ) ? $this->getOne( "SELECT `name_precase_singul` FROM `equipments` WHERE `slug`=?s", $slug['equipment'] ) : '' ,
				'eq_slug' => ( !empty($slug['equipment']) ) ? $this->getOne( "SELECT `slug` FROM `equipments` WHERE `slug`=?s", $slug['equipment'] ) : '' ,
				'eq_title' => ( !empty($slug['equipment']) ) ? $this->getOne( "SELECT `title` FROM `equipments` WHERE `slug`=?s", $slug['equipment'] ) : '' ,
				'eq_title_singul' => ( !empty($slug['equipment']) ) ? $this->getOne( "SELECT `title_singul` FROM `equipments` WHERE `slug`=?s", $slug['equipment'] ) : '' ,
				'model' => ( !empty($slug['model']) ) ? $this->getOne( "SELECT `model` FROM `models` WHERE `slug`=?s", $slug['model'] ) : '' ,
				'slug' => ( !empty($slug['model']) ) ? $this->getOne( "SELECT `slug` FROM `models` WHERE `slug`=?s", $slug['model'] ) : '' ,
				'name' => ( !empty($slug['model']) ) ? $this->getOne( "SELECT `name` FROM `models` WHERE `slug`=?s", $slug['model'] ) : '' ,				
			];			
			
			return $this->placeholder($content, $place);
		}
	
}

























