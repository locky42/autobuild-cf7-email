<?php
/**
 * Autobuild CF7 Email
 *
 * Plugin Name: Auto build CF7 Email
 * Plugin URI:  https://github.com/locky42/autobuild-cf7-email
 * Description: Auto build email for Contact Form 7. The letter is automatically generated from the fields added to the form.
 * Version:     1.0.2
 * Author:      webT
 * Author URI:  https://webt.com.ua
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Requires at least: 4.9
 * Tested up to: 5.8
 * Requires PHP: 5.2.4
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */


add_filter( 'wpcf7_mail_components', 'filter_wpcf7_mail_components', 1, 3 );
function filter_wpcf7_mail_components($components, $wpcf7_get_current_contact_form, $instance) {
	$start = strpos($components['body'], '<body>');
	$clearContent = $components['body'];
	if($start) {
		$start = $start + 6;
		$clearContent = strip_tags(substr($components['body'], $start, strpos($components['body'], '</body>') - $start));
	}
	if(trim($clearContent) === '[default]') {
		$components['body'] = '';
		$rm_underscore = apply_filters('cfdb7_remove_underscore_data', true);
		$components['subject'] = str_replace('[your-subject]', $wpcf7_get_current_contact_form->title, $components['subject']);

		$fields = $wpcf7_get_current_contact_form->scan_form_tags();
		foreach ( $fields as $field ) {
			$key = $field->name;
			if ( $key == 'cfdb7_status' || $key == '' )  continue;
			if( $rm_underscore ) preg_match('/^_.*$/m', $key, $matches);
			if( ! empty($matches[0]) ) continue;
			if ( strpos($key, 'cfdb7_file') !== false ){
				$key_val = str_replace('cfdb7_file', '', $key);
				$key_val = str_replace('your-', '', $key_val);
				$key_val = str_replace( array('-','_'), ' ', $key_val);
				$key_val = ucwords( $key_val );
			}else{
				$key_val = str_replace('your-', '', $key);
				$key_val = str_replace( array('-','_'), ' ', $key_val);
				$key_val = ucwords( $key_val );
			}
			$key_val = __($key_val, get_option( 'template' ));
			$components['body'] .= "<div><span>$key_val: </span><span>[$field->name]</span></div>";
		}
		$components['body'] = $instance->replace_tags($components['body'], 1);
	}
	return $components;
}

add_filter( 'wpcf7_default_template',  'wpcf7_default_template_custom', 1000, 2);
function wpcf7_default_template_custom($template, $type) {
	if($type == 'mail' || $type == 'mail_2') {
		$template['body'] = '[default]';
	}
	return $template;
}
