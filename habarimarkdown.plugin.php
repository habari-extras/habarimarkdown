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
		// Added by Caius Durling <dev@caius.name> <http://caius.name/>
		// Escapes unsafe chars in the title
		Format::apply( 'htmlescape', 'post_title_out' );

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
		if ( !function_exists( 'Markdown' ) ) {
			require_once('markdown.php');
		}
		
		// Only apply changes to unauthenticated viewers.  This allows markdown to be used in atompub clients too.
		if ( ! User::identify()->loggedin ) {
			$feed_entry->content = markdown( $post->content );
		}
		return $feed_entry;
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

		if ( !function_exists( 'Markdown' ) ) {
			require_once('markdown.php');
		}

		$smarty_enabled = Options::get( 'habarimarkdown__smarty', false );
		if ( $smarty_enabled ) {
			if ( !function_exists( 'SmartyPants' ) ) {
				require_once('smartypants.php');
			}

			return SmartyPants( Markdown ( $content ) );
		}
		else {



			return Markdown( $content );
		}
	}

	public static function comment_safe_markdown( $content )
	{

		if ( !function_exists( 'Markdown' ) ) {
			require_once('markdown.php');
		}

		$html = '';
		$smarty_enabled = Options::get( 'habarimarkdown__smarty', false );
		if ( $smarty_enabled ) {
			if ( !function_exists( 'SmartyPants' ) ) {
				require_once('smartypants.php');
			}

			$html = SmartyPants( Markdown ( $content ) );
		}
		else {
			$html = Markdown( $content );
		}

		// filter the HTML, just as a normal comment would be filtered before saving to the database
		return InputFilter::filter( $html );

	}
}

?>
