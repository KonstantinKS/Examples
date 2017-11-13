<?php
/**
 * Custom
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package WordPress
 * @subpackage Servise_KKS
 * @since Servise_KKS 1.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Theme_Options' ) ) :

class Theme_Options
{
	 
	 var $default_settings = array(
	 	 'thop_phone'  => '',
		 'thop_top_text' => '',
		 'thop_header'	=> 'view-1',		 
	 );
	 
	 var $options;
	
	 function theme_options() {		 
		 add_action('admin_menu', array(&$this, 'add_menu'));
		 if (!is_array(get_option('theme_options'))) { add_option('theme_options', $this->default_settings); }
		 $this->options = get_option('theme_options');		 
	 }
	
	 function add_menu() {
	 	add_theme_page(	'WP Carrepair Theme Options',
						'Опции темы',
						8,
						"theme_options",
						array(&$this, 'menu_options'));
	 }	 	 
	 
	 function menu_options() {		 
		 if (isset($_POST['thop_phone_header_1'])) {
			if(trim($_POST['thop_phone_header']) !== '')  {			
				//if (!preg_match("/((8|\+7)-?)?\(?\d{3,5}\)?-?\d{1}-?\d{1}-?\d{1}-?\d{1}-?\d{1}((-?\d{1})?-?\d{1})?/", trim($_POST['thop_phone_site']))) {
				//	$_POST['thopError']['phoneError'] = 'Не верный формат телефона, пример: +7 123 123-45-67.';
				//	$thopError = true;
				//}
				//if (!preg_match("/^[\d]{1}\ \([\d]{2,3}\)\ [\d]{2,3}-[\d]{2,3}-[\d]{2,3}$/", trim($_POST['thop_phone_site']))) {
				//	$_POST['thopError']['phoneError'] = 'Не верный формат телефона, пример: 8 (999) 123-45-64.';
				//	$thopError = true;
				//	echo '<div class="error theme_options" id="message"><p>'.$_POST['thopError']['phoneError'].'</strong>.</p></div>';			
				//}
				if (!preg_match("/(8|\+7)\ \([\d]{2,3}\)\ [\d]{2,3}-[\d]{2,3}-[\d]{2,3}$/", trim($_POST['thop_phone_site']))) {
					$_POST['thopError']['phoneError'] = 'Не верный формат телефона, пример: +7 (999) 123-45-64.';
					$thopError = true;
					echo '<div class="error theme_options" id="message"><p>'.$_POST['thopError']['phoneError'].'</strong>.</p></div>';			
				}		
			}
		}
		if (isset($_POST['thop_phone_footer_1']) && empty($thopError)) {
			if(trim($_POST['thop_phone_footer']) !== '')  {			
				//if (!preg_match("/((8|\+7)-?)?\(?\d{3,5}\)?-?\d{1}-?\d{1}-?\d{1}-?\d{1}-?\d{1}((-?\d{1})?-?\d{1})?/", trim($_POST['thop_phone_contacts']))) {
				//	$_POST['thopError']['phoneError'] = 'Не верный формат телефона, пример: +7 123 123-45-67.';
				//	$thopError = true;
				//}
				//if (!preg_match("/^[\d]{1}\ \([\d]{2,3}\)\ [\d]{2,3}-[\d]{2,3}-[\d]{2,3}$/", trim($_POST['thop_phone_contacts']))) {
				//	$_POST['thopError']['phoneError'] = 'Не верный формат телефона, пример: 8 (999) 123-45-64.';
				//	$thopError = true;
				//	echo '<div class="error theme_options" id="message"><p>'.$_POST['thopError']['phoneError'].'</strong>.</p></div>';				
				//}
				if (!preg_match("/(8|\+7)\ \([\d]{2,3}\)\ [\d]{2,3}-[\d]{2,3}-[\d]{2,3}$/", trim($_POST['thop_phone_contacts']))) {
					$_POST['thopError']['phoneError'] = 'Не верный формат телефона, пример: +7 (999) 123-45-64.';
					$thopError = true;
					echo '<div class="error theme_options" id="message"><p>'.$_POST['thopError']['phoneError'].'</strong>.</p></div>';			
				}	
			}
		}
			 
		if ($_POST['thop_action'] == 'save' && empty($thopError)) {
			
			for ( $i=1; $i<=12; $i++ )
			{
				$this->options['thop_address']["$i"] = $_POST["thop_address_$i"];
				$this->options['thop_phones']["$i"] = $_POST["thop_phone_$i"];				
			}		
			
			$this->options["thop_footer_copyrighted"] = $_POST["thop_footer_copyrighted"];
			$this->options["thop_footer_info_link"] = $_POST["thop_footer_info_link"];
			$this->options["thop_footer_info_text"] = $_POST["thop_footer_info_text"];
						 
			update_option('theme_options', $this->options);
			//delete_option('theme_options');
			echo '<div class="updated theme_options" id="message"><p>Ваши изменения <strong>сохранены</strong>.</p></div>';
		}
	
		for ( $i=1; $i<9; $i++ )
		{			
			$args_thop_page_contact[$i] = array(
				'selected' => $this->options['thop_head'][$i]['link'],		
				'name' => 'thop_head['.$i.'][link]',		
				'show_option_none' => 'Выберите контакт',			
				'post_type' => 'contacts',
			);				
		}
				
echo '
	
<form action="" method="post" class="theme_options"><br/>
	<input type="submit" value="Сохранить" name="thop_save" class="thop_changes" /><br/>
	<input type="hidden" id="thop_action" name="thop_action" value="save">
	<div class="thop_tab">
		<br/>
		<b>Опции темы</b><br><br>	
		<div class="theme_option header">HEADER:			
			<p><input placeholder="Адрес 1" style="width:300px;" name="thop_address_1" id="thop_address_1" value="'.$this->options['thop_address']["1"].'"><label> - Адрес 1</label></p>
			<p><input placeholder="Телефон 1" style="width:300px;" name="thop_phone_1" id="thop_phone_1" value="'.$this->options['thop_phones']["1"].'"><label> - Телефон 1</label></p>
			<p><input placeholder="Адрес 2" style="width:300px;" name="thop_address_2" id="thop_address_2" value="'.$this->options['thop_address']["2"].'"><label> - Адрес 2</label></p>
			<p><input placeholder="Телефон 2" style="width:300px;" name="thop_phone_2" id="thop_phone_2" value="'.$this->options['thop_phones']["2"].'"><label> - Телефон 2</label></p>
			<p><input placeholder="Адрес 3" style="width:300px;" name="thop_address_3" id="thop_address_3" value="'.$this->options['thop_address']["3"].'"><label> - Адрес 3</label></p>
			<p><input placeholder="Телефон 3" style="width:300px;" name="thop_phone_3" id="thop_phone_3" value="'.$this->options['thop_phones']["3"].'"><label> - Телефон 3</label></p>
			<p><input placeholder="Адрес 4" style="width:300px;" name="thop_address_4" id="thop_address_4" value="'.$this->options['thop_address']["4"].'"><label> - Адрес 4</label></p>
			<p><input placeholder="Телефон 4" style="width:300px;" name="thop_phone_4" id="thop_phone_4" value="'.$this->options['thop_phones']["4"].'"><label> - Телефон 4</label></p>
			<p><input placeholder="Адрес 5" style="width:300px;" name="thop_address_5" id="thop_address_5" value="'.$this->options['thop_address']["5"].'"><label> - Адрес 5</label></p>
			<p><input placeholder="Телефон 5" style="width:300px;" name="thop_phone_5" id="thop_phone_5" value="'.$this->options['thop_phones']["5"].'"><label> - Телефон 5</label></p>
			<p><input placeholder="Адрес 6" style="width:300px;" name="thop_address_6" id="thop_address_6" value="'.$this->options['thop_address']["6"].'"><label> - Адрес 6</label></p>
			<p><input placeholder="Телефон 6" style="width:300px;" name="thop_phone_6" id="thop_phone_6" value="'.$this->options['thop_phones']["6"].'"><label> - Телефон 6</label></p>
			<p><input placeholder="Адрес 7" style="width:300px;" name="thop_address_7" id="thop_address_7" value="'.$this->options['thop_address']["7"].'"><label> - Адрес 7</label></p>
			<p><input placeholder="Телефон 7" style="width:300px;" name="thop_phone_7" id="thop_phone_7" value="'.$this->options['thop_phones']["7"].'"><label> - Телефон 7</label></p>
			<p><input placeholder="Адрес 8" style="width:300px;" name="thop_address_8" id="thop_address_8" value="'.$this->options['thop_address']["8"].'"><label> - Адрес 8</label></p>
			<p><input placeholder="Телефон 8" style="width:300px;" name="thop_phone_8" id="thop_phone_8" value="'.$this->options['thop_phones']["8"].'"><label> - Телефон 8</label></p>
			<p><input placeholder="Адрес 9" style="width:300px;" name="thop_address_9" id="thop_address_9" value="'.$this->options['thop_address']["9"].'"><label> - Адрес 9</label></p>
			<p><input placeholder="Телефон 9" style="width:300px;" name="thop_phone_9" id="thop_phone_9" value="'.$this->options['thop_phones']["9"].'"><label> - Телефон 9</label></p>
			<p><input placeholder="Адрес 10" style="width:300px;" name="thop_address_10" id="thop_address_10" value="'.$this->options['thop_address']["10"].'"><label> - Адрес 10</label></p>
			<p><input placeholder="Телефон 10" style="width:300px;" name="thop_phone_10" id="thop_phone_10" value="'.$this->options['thop_phones']["10"].'"><label> - Телефон 10</label></p>
			<p><input placeholder="Адрес 11" style="width:300px;" name="thop_address_11" id="thop_address_11" value="'.$this->options['thop_address']["11"].'"><label> - Адрес 11</label></p>
			<p><input placeholder="Телефон 11" style="width:300px;" name="thop_phone_11" id="thop_phone_11" value="'.$this->options['thop_phones']["11"].'"><label> - Телефон 11</label></p>
			<p><input placeholder="Адрес 12" style="width:300px;" name="thop_address_12" id="thop_address_12" value="'.$this->options['thop_address']["12"].'"><label> - Адрес 12</label></p>
			<p><input placeholder="Телефон 12" style="width:300px;" name="thop_phone_12" id="thop_phone_12" value="'.$this->options['thop_phones']["12"].'"><label> - Телефон 12</label></p>					
		</div>
		<div class="theme_option footer">FOOTER:
			<p><input placeholder="копирайт" name="thop_footer_copyrighted" id="thop_footer_copyrighted" value="'.$this->options['thop_footer_copyrighted'].'"><label> - Копирайт</label></p>			
			<p>'.wp_dropdown_pages(array('selected'=>$this->options["thop_footer_info_link"],'echo'=>0,'name'=>'thop_footer_info_link','show_option_none'=>'Выберите страницу с информацией')).'</p>			
		</div>
		<div class="theme_option footer">CONTENT:
			<p><textarea placeholder="Вставьте код карты" name="thop_content_map" id="thop_content_map">'.stripslashes($this->options["thop_content_map"]).'</textarea><label> - Вставьте код карты</label></p>
		</div>		
	</div>
	<br />
	<input type="submit" value="Сохранить" name="thop_save" class="thop_changes" />
	<br />
</form>
	
';
	}
	 
	//$this->theme_options_start();	 
}

endif;

return new Theme_Options();

//$theme_option = new Theme_Options();
//$theme_options = get_option('theme_options');