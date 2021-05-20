<?php
/**
 * Autolink Plugin
 * This plugin auto-detects file keywords and generates links
 * Authors: Xavi Esteve (@luckyshot), Igor Gaffling (@gaffling)
 */
class PluginAutoLink {

	static $version = '1.1.0';

	static function run( $wiki ) {
		$wiki->event('view_after', NULL, function($wiki) {
			$use_target_blank = TRUE; // CHANGE IF YOU LIKE
			$use_nofollow_tag = TRUE; // CHANGE IF YOU LIKE

			$filenames = [];
			foreach ($wiki->file_list as $file) {
				if ( !$file ) continue;
				$filenames[ $file ] = self::cleanName( $file );
			}
			// dd($filenames);
			foreach ($filenames as $path => $name) {
				$wiki->html = preg_replace(
					'#([ \n])('.$name.')([ .,])#i',
					'$1[$2]('.$path.')$3',
					$wiki->html
				);
/* 				$wiki->html = preg_replace(
					'#([ \n])('.$name.')([ .,])#i',
					'$1[$2]('.BASE_URL.'/'.$path.')$3',
					$wiki->html
				); */
			}

			$wiki->html = preg_replace(
				'#\[(.*?)\]\(([a-z0-9-_/]+)\)#i',
				'[$1]('.BASE_URL.'/$2)',
				$wiki->html
			);
			/* if(preg_match_all('#\[(.*?)\]\(([a-z0-9-_/]+)\)#i', $wiki->html, $m)) {
				$local_links = array_map(function($l) {
					// return str_replace($m[0], $local_links, $wiki->html);
					return BASE_URL.'/'.$l;
				}, $m[2]);
				$wiki->html = preg_replace($m[0], $local_links, $wiki->html);
				dd($m, $local_links);
			} */



			// Detect URLs and autolink them
			$attribute = '';
			if ( $use_target_blank == TRUE ) $attribute .= ' target="_blank"';
			if ( $use_nofollow_tag == TRUE ) $attribute .= ' rel="nofollow"';

			$linkregex = '/^((?:tel|https?|ftps?|mailto):.*?)$/im';

			if ( preg_match_all($linkregex, $wiki->html, $match) ) {
				foreach ($match[0] as $url) {
				$linktext = str_replace('mailto:', '', $url);
				$wiki->html=str_replace($url, '<a href="'.$url.'"'.$attribute.'>'.$linktext.'</a>', $wiki->html);
				}
			}

			return $wiki;
		});
	}

	static function cleanName($string){
		return str_replace('-', ' ', pathinfo($string)['filename']);
	}
}
