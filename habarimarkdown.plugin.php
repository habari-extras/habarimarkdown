<?php

/**
 * A Markdown plugin for Habari
 *
 * @package habarimarkdown
 */

class HabariMarkdown extends Plugin
{
	public function action_init()
	{
		if ( !class_exists( 'MarkdownExtra' ) ) {
			require_once('php-markdown/Michelf/Markdown.php');
			require_once('php-markdown/Michelf/MarkdownExtra.php');
		}
		if ( !function_exists( 'SmartyPants' ) && Options::get( 'habarimarkdown__smarty', false ) ) {
			require_once('php-smartypants/smartypants.php');
		}

		Format::apply( 'markdown', 'post_content_out_7' );
		Format::apply( 'markdown', 'post_content_summary_7' );
		Format::apply( 'markdown', 'post_content_more_7' );
		Format::apply( 'markdown', 'post_content_excerpt_7' );
		Format::apply( 'comment_safe_markdown', 'comment_content_out_7' );
	}

	/**
	 * Adds a Configure action to the plugin
	 *
	 * @param array $actions An array of actions that apply to this plugin
	 * @param string $plugin_id The id of a plugin
	 * @return array The array of actions
	 */
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $this->plugin_id() == $plugin_id ){
			$actions[]= 'Configure';
		}
		return $actions;
	}

	/**
	 * Creates a UI form to handle the plugin configuration
	 *
	 * @param string $plugin_id The id of a plugin
	 * @param array $actions An array of actions that apply to this plugin
	 */
	public function action_plugin_ui( $plugin_id, $action )
	{
		if ( $this->plugin_id()==$plugin_id && $action=='Configure' ) {
			$form = new FormUI( strtolower(get_class( $this ) ) );
			$form->append( 'checkbox', 'enable SmartyPants', 'option:habarimarkdown__smarty', _t( 'Enable SmartyPants' ) );
			$form->append( 'submit', 'save', _t( 'Save' ) );
			$form->out();
		}
	}

	/**
	 * Filter Atom Feed
	 * @param SimpleXMLElement $feed_entry the Atom feed entry
	 * @param Post $post The post
	 * @return SimpleXMLElement the filtered Atom feed entry
	 */
	public function action_atom_add_post( $feed_entry, $post )
	{
		// Only apply changes to unauthenticated viewers.  This allows markdown to be used in atompub clients too.
		if ( ! User::identify()->loggedin ) {
			$feed_entry->content =  MarkdownFormat::markdown( $post->content );
		}
		return $feed_entry;
	}
}

class MarkdownFormat extends Format
{
	public static $parser;

	// this doesn't work right now
	public static function __static()
	{
		self::$parser = new \Michelf\MarkdownExtra;
		self::$parser->empty_element_suffix = '>';
	}

	// try and take over autop to prevent conflicts...
	// there really should be a "remove" in Format!
	public static function autop( $content )
	{
		return $content;
	}

	public static function markdown( $content )
	{
		$html = \Michelf\MarkdownExtra::defaultTransform($content);
		if ( Options::get( 'habarimarkdown__smarty', false ) ) {
			$html = SmartyPants($html);
		}
		return $html;
	}

	public static function comment_safe_markdown( $content )
	{
		// filter the HTML, just as a normal comment would be filtered before saving to the database
		return InputFilter::filter( self::markdown($content) );
	}
}

?>
