<?php	
	$slug = $modelMS->slug();	
	
	$view = DIR_DATA_2 . 'views/model/';	
	
	$view_files = scandir( $view );	
	
	foreach ( $view_files as $f ) :		
		if ( $f!="." && $f!=".." ) :		
			$names[] = preg_split('/[-.]/', $f, 0, PREG_SPLIT_NO_EMPTY);			
		endif;	
	endforeach;	
		
	foreach ( $names as $name ) :
		$index = array_shift($name);
		if ( is_numeric($index) ) :
			$exp = array_pop($name);
			$type = implode("-", $name);				
			if ( ($type===$slug['equipment']) || ($type==='' && empty($path_files[$index])) ) :
				$path_files[$index] = ($type!=='') ? ($index.'-'.$type.'.'.$exp) : ($index.'.'.$exp);
			endif;
		endif;
	endforeach;	
	
	ksort($path_files);
	
	require DIR_DATA_2 . "meta/model.php";	
	
	foreach ($path_files as $file) :		
		$content = $modelMS->replace( file_get_contents( $view . $file ) );			
		eval(" ?>\n"."{$content}"."\n<?php ");
	endforeach;	
	
	unset( $names, $index, $exp, $type, $path_files, $content );	
?>





















