<?php

/**
Plugin Name: Largest Jackpots Feed
Plugin URI:  http://suurimmatjackpotit.com/largest-jackpots-feed/
Description: Create simple shortcodes that show largest slot games' jackpots.
Version:     0.1
Author:      kuopassa
Author URI:  http://kuopassa.net/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: larjacfee
Domain Path: 
 
{Plugin Name} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
{Plugin Name} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {License URI}.
*/

if (!defined('ABSPATH')) {
	die;
}

if (!defined('LARJACFEE_BASE_URL')) {
	define('LARJACFEE_BASE_URL','http://suurimmatjackpotit.com/');
}

function larjacfee_shortcodes_init() {
	
	if (!function_exists('larjacfee_validate_wraptag')) {
		
		function larjacfee_validate_wraptag($candidate = 'ol') {
			
			$candidate = strtolower($candidate);
			
			if (!in_array($candidate,array('ul','ol'))) {
				return 'ol';
			}
			else {
				return $candidate;
			}
		}
	}
	
	if (!function_exists('larjacfee_largest_jackpots')) {
		
		function larjacfee_largest_jackpots($attributes) {

			$attributes = shortcode_atts(
				array(
					'limit'=>5,
					'offset'=>0,
					'wraptag'=>'ol',
				),
				$attributes
			);
			
			$attributes['wraptag'] = 
				larjacfee_validate_wraptag($attributes['wraptag']);
			
			if (
				(!is_numeric($attributes['limit']))
				||
				($attributes['limit'] > 10)
				||
				($attributes['limit'] < 1)
			) {
				$attributes['limit'] = 5;
			}
			
			if (
				(!is_numeric($attributes['offset']))
				||
				($attributes['offset'] > 10)
				||
				($attributes['offset'] < 1)
			) {
				$attributes['offset'] = 0;
			}
			
			$source = LARJACFEE_BASE_URL;
			$source .= 'larjacfee/?';
			$source .= 'offset='.$attributes['offset'];
			$source .= '&limit='.$attributes['limit'];
			
			$source = esc_url_raw($source);
			
			$cache_file = get_temp_dir();
			$cache_file = rtrim($cache_file,'/');
			$cache_file .= '/';
			$cache_file .= 'larjacfee';
			$cache_file .= $attributes['offset'];
			$cache_file .= $attributes['limit'];
			$cache_file .= '.json';
			
			if (
				(!file_exists($cache_file))
				||
				(!is_readable($cache_file))
				||
				(filemtime($cache_file) < strtotime('1 hour ago'))
			) {
				
				wp_remote_get(
					$source,
					array(
						'blocking'=>false,
						'compress'=>true,
						'timeout'=>2,
						'redirection'=>0,
						'user-agent'=>'WordPress; '.home_url(),
						'limit_response_size'=>15000,
						'stream'=>true,
						'filename'=>$cache_file,
					)
				);
			}
			
			if (
				(file_exists($cache_file))
				&&
				(is_readable($cache_file))
			) {
			
				$response = file_get_contents($cache_file);
			}
			
			$response = larjacfee_make_feed(
				$response,
				$attributes['wraptag']
			);
			
			return $response;
		}
	}
	
	if (!function_exists('larjacfee_make_feed')) {
		
		function larjacfee_make_feed($jackpots = null,$wraptag) {
			
			$return = '';
			
			# BEGINS: $jackpots WAS RECEIVED
			if ((!is_null($jackpots)) && (isset($wraptag))) {
				
				$jackpots = json_decode(
					$jackpots,
					true,
					3,
					JSON_PARTIAL_OUTPUT_ON_ERROR
				);
				
				# BEGINS: ITERABLE DATA
				if (
					(is_array($jackpots))
					&&
					(!empty($jackpots))
				) {
					
					# BEGINS: FOREACH
					foreach ($jackpots as $jackpots) {
						
						if (
							(isset(
								$jackpots['name'],
								$jackpots['currency'],
								$jackpots['value'],
								$jackpots['url'])
							)
							&&
							(is_numeric($jackpots['value']))
						) {
							
							if (strlen($jackpots['name']) > 50) {
								$jackpots['name'] = 
									substr($jackpots['name'],0,50);
							}
							
							$jackpots['name'] = esc_html($jackpots['name']);
							
							$jackpots['currency'] = esc_html($jackpots['currency']);
						
							$jackpots['value'] = number_format_i18n($jackpots['value'],2);
								
							$jackpots['url'] = esc_url($jackpots['url']);
							
							$return .= '<li>';
							$return .= '<a href="'.$jackpots['url'].'" ';
							$return .= 'rel="nofollow"><strong>';
							$return .= $jackpots['name'];
							$return .= '</strong><br />';
							$return .= $jackpots['value'].' ';
							$return .= $jackpots['currency'].'</a>';
							$return .= '</li>';
						}
					}
					# ENDS: FOREACH
					
					$return = 
						'<'.$wraptag.' class="larjacfee_largest_jackpots">'.
							$return.'</'.$wraptag.'>';
				}
				# ENDS: ITERABLE DATA
			}
			# ENDS: $jackpots WAS RECEIVED
			
			return $return;
		}
	}

	add_shortcode(
		'largest_jackpots_feed',
		'larjacfee_largest_jackpots'
	);
}

add_action(
	'init',
	'larjacfee_shortcodes_init'
);