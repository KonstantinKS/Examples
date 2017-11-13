<?php
/**
 * Custom Metaboxes for this theme
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package Mastermb
 */

?>
<?php

add_action( 'init', 'kks_options_meta' );

function kks_options_meta()
{
	$options = [
		'typeworks' => [
			'name' => 'typeworks',
			'object_type' => 'taxonomy',
			'for_object' => 'typeworks',
			'args' => [
				'type'              => 'array',
				'description'       => '',
				'single'            => false,
				'sanitize_callback' => 'kks_sanitize_data_meta',
				'auth_callback'     => null,
				'show_in_rest'      => true,
			],
			'metas' => [
				'image' => [ 
					'thumbnail' => false,
				],
				'textarea' => [
					'subtitle' => false,
				],				
			],			
		],
		'contacts' => [
			'name' => 'contacts',
			'object_type' => 'post_type',
			'for_object' => 'contacts',
			'args' => [
				'type'              => 'array',
				'description'       => '',
				'single'            => false,
				'sanitize_callback' => 'kks_sanitize_data_meta',
				'auth_callback'     => null,
				'show_in_rest'      => true,
			],
			'metas' => [
				'input' => [
					'phone' => 'Телефон:/Укажите телефон',
					'coordinates' => 'Координаты:/Укажите координаты',
				],
			],			
		],		
	];
	kks_register_meta($options);
}

function kks_register_meta($options)
{	
	add_option( 'kks_options_meta', $options );
	update_option( 'kks_options_meta', $options );
	foreach ($options as $name=>$option) :
		register_meta( $option['object_type'], $option['name'], $option['args'] );
		
	endforeach;
}

function kks_sanitize_data_meta($data_meta) { return wp_kses_post($data_meta); }


/* ///////////////////////////////////////// START TYPEWORKS ///////////////////////////////////////// */

/* ---- DISPLAY META ----*/
add_action( 'typeworks_edit_form_fields', 'kks_typeworks_meta', 10, 2 );
function kks_typeworks_meta( $term, $taxonomy )
{	
	$metas = [];
	$metas = get_term_meta( $term->term_id, 'typeworks', false );	
	$this_options = get_option( 'kks_options_meta', 'Опция не найдена' );	
	?> 
    <?php wp_nonce_field( basename( __FILE__ ), 'kks_meta_typeworks_nonce' ); ?>
    <?php $i = 0; foreach ( $this_options['typeworks']['metas'] as $option_name=>$values ) { ?>           
        <?php if ( $option_name==='select' ) { ?>
        	<?php foreach ( $values as $k=>$v ) { ?>    
                <tr class="form-field">
                    <th scope="row" valign="top">
                        <label for="typeworks_select_<?php echo $k; ?>"><?php esc_html_e( 'Выберите шаблон категории:' ); ?></label>
                    </th>
                    <td>
                    <div>
                    <select name="typeworks[select][<?php echo $k; ?>]" id="typeworks_select_<?php echo $k; ?>">
                    <?php foreach ( $v as $kv=>$vv ) { ?>
                    	<?php if ( !isset($metas[$i]['select'][$k]) ) { $metas[$i]['select'][$k] = 'Значение не задано'; } ?>		
                        <option value="<?php echo $kv; ?>" <?php selected( $metas[$i]['select'][$k], $kv )?> ><?php echo $vv; ?></option>                        
                    <?php } ?>		
                    </select>
                    </div>
                    </td>
                </tr>
            <?php } ?>     
        <?php } ?>
        <?php if ( $option_name==='image' ) { ?>
        	<?php foreach ( $values as $k=>$v ) { ?>    
            	<tr class="form-field">
                	<div><th scope="row" valign="top"><label for="typeworks_image_<?php echo $k; ?>"><?php esc_html_e( 'Миниатюра:' ); ?></label></th></div>
                    <td>
                        <div class='typeworks'>                       	        
                        	<?php $img = ( $metas[$i]['image'][$k] ) ? ( wp_get_attachment_image_url( (int)$metas[$i]['image'][$k], array(200, '') ) ) : ( get_stylesheet_directory_uri() . '/image/no-image.png' ); ?>
                            <div>
                                <img
                                    data-src="<?php echo get_stylesheet_directory_uri() . '/image/no-image.png'; ?>"
                                    src="<?php echo $img; ?>"                            
                                    width="200px"
                                    height="auto"
                                />
                            </div>
                            <div>
                                <input
                                    type="hidden"
                                    name="typeworks[image][<?php esc_attr_e($k); ?>]"
                                    id="typeworks_image_<?php esc_attr_e($k); ?>"
                                    value="<?php echo $metas[$i]['image'][$k]; ?>"
                                />
                                <button type="submit" class="upload_image_button button">Загрузить</button>
                                <button type="submit" class="remove_image_button button">&times;</button>
                            </div>
                        </div>
                    </td>
            	</tr>
            <?php } ?>     
        <?php } ?>
        <?php if ( $option_name==='textarea' ) { ?>
        	<?php foreach ( $values as $k=>$v ) { ?>    
                <tr class="form-field">
                    <th scope="row" valign="top">
                        <label for="typeworks_textarea_<?php echo $k; ?>"><?php esc_html_e( 'Введите подзаголовок:' ); ?></label>
                    </th>
                    <td>
                    	<div><textarea name="typeworks[textarea][<?php echo $k; ?>]" id="typeworks_textarea_<?php echo $k; ?>"><?php echo $metas[$i]['textarea'][$k]; ?></textarea></div>
                    </td>
                </tr>
            <?php } ?>     
        <?php } ?>
   	<?php $i++; } ?>      
<?php }
/* ---- END DISPLAY META ----*/

/* ---- SAVE META ----*/
add_action( 'create_typeworks', 'kks_meta_typeworks_save' );
add_action( 'edit_typeworks', 'kks_meta_typeworks_save' );
function kks_meta_typeworks_save( $term_id )
{	
	if (
		!isset($_POST['kks_meta_typeworks_nonce']) ||
		!wp_verify_nonce($_POST['kks_meta_typeworks_nonce'], basename( __FILE__ ))
		)
		{ return; }
	$old_metas = get_term_meta( $term_id, 'typeworks', false );	
	if ( isset($_POST['typeworks']) ) {
		foreach( $_POST['typeworks'] as $key=>$value ) { $new_metas[$key] = isset( $value ) ? $value : ''; }
	}	
	delete_term_meta( $term_id, 'typeworks' );
	foreach ( $new_metas as $key=>$value )
	{		
		if ( isset( $value ) ) { kks_sanitize_data_meta( $value ); }
		else { unset($new_metas[$key]); }
		add_term_meta( $term_id, 'typeworks', array( $key=>$value ), false );	
	}		
}
/* ---- END SAVE META ----*/

/* ///////////////////////////////////////// END TYPEWORKS ///////////////////////////////////////// */


/* ///////////////////////////////////////// START CONTACTS ///////////////////////////////////////// */

/* ---- DISPLAY META ----*/
add_action( 'edit_form_after_title', 'kks_meta_contacts', 100 );
function kks_meta_contacts( $post )
{
	if ( $post->post_type == 'contacts' ) :
		$metas = [];
		$metas = get_term_meta( $post->ID, 'contacts', false );		
		$this_options = get_option( 'kks_options_meta', 'Опция не найдена' );	
		?> 
    	<?php wp_nonce_field( basename( __FILE__ ), 'kks_meta_contacts_nonce' ); ?>
    	<?php $i = 0; foreach ( $this_options['contacts']['metas'] as $option_name=>$values ) { ?>
        	
            <div class="kss_meta_box">
                <table class="kks_meta_table">                
            
					<?php if ( $option_name==='input' ) { ?>
                        <?php foreach ( $values as $k=>$v ) { ?>
                        	<?php $v = explode( '/', $v ); ?>
                        	<tr>
                                <td>
                                    <label for="contacts_input_<?php echo $k ?>"><?php esc_html_e( $v[0] ); ?></label>                    
                                    <input id="contacts_input_<?php echo $k ?>" type="tel" name="contacts[input][<?php echo $k ?>]" value="<?php echo $metas[$i]['input'][$k]; ?>" placeholder="<?php esc_html_e( $v[1] ); ?>" />
                                </td>
                            </tr>                           
                        <?php } ?>     
                    <?php } ?>
                    
               	</table>
            </div>                
        
        <?php $i++; } ?>    
		<?php
	endif;
}
/* ---- END SERVICES ----*/

/* ---- SAVE META ----*/
add_action( 'save_post', 'kks_meta_contacts_save', 10, 3 );
function kks_meta_contacts_save( $post_ID, $post, $update )
{	
	if ( $post->post_type == 'contacts' ) :
		if (
			!isset($_POST['kks_meta_contacts_nonce']) ||
			!wp_verify_nonce($_POST['kks_meta_contacts_nonce'], basename( __FILE__ ))
			)
			{ return; }
		$old_contacts = get_term_meta( $post_ID, 'contacts', false );			
		if ( isset($_POST['contacts']) ) {
			foreach( $_POST['contacts'] as $key=>$value ) { $new_metas[$key] = isset( $value ) ? $value : ''; }
		}	
		delete_term_meta( $post_ID, 'contacts' );		
		foreach ( $new_metas as $key=>$value )
		{		
			if ( isset( $value ) ) { kks_sanitize_data_meta( $value ); }
			else { unset($new_metas[$key]); }
			add_term_meta( $post_ID, 'contacts', array( $key=>$value ), false );	
		}		
	endif;	
}
/* ---- END SAVE META ----*/

/* ///////////////////////////////////////// END CONTACTS ///////////////////////////////////////// */













