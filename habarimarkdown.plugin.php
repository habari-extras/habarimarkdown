<?php

/**
 * A Markdown plugin for Habari
 *
 * @package habarimarkdown
 */

require_once "markdown.php";

class HabariMarkdown extends Plugin
{
	const VERSION= '0.3';
	
	/**
	* Return plugin metadata for this plugin
	*
	* @return array Plugin metadata
	*/
	public function info()
	{
		return array(
			'url' => 'http://habariproject.org/',
			'name' => 'Habari Markdown',
			'description' => 'Enables John Gruber\'s Markdown syntax for posts.',
			'license' => 'Apache License 2.0 and BSD (see NOTICE)',
			'author' => 'Habari Community',
			'version' => self::VERSION,
		);
	}
	
	public function action_init()
	{
		Format::apply( 'markdown', 'post_content_out' );
		Format::apply( 'markdown', 'post_content_summary' );
		Format::apply( 'markdown', 'post_content_more' );
		Format::apply( 'markdown', 'post_content_excrept' );
		Format::apply( 'markdown', 'post_content_atom' );
		Format::apply( 'markdown', 'comment_content_out' );
	}
}

class MarkdownFormat extends Format
{
	// try and take over autop to prevent conflicts...
	// there really should be a "remove" in Format!
	public static function autop( $content )
	{
		return $content;
	}
	
	public static function markdown( $content )
	{
		return Markdown( $content );
	}
}

?>