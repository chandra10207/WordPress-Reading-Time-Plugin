<?php 

/*
Plugin Name: CSP Reading Time
Description: Simple Reading time setting API based plugin
Version: 1.0
Author: Chandra Shekhar Pandey
Author Uri:
Text Domain: cspreadingtime
Domain Path: /languages

*/

class CSP_Reading_Time {

	function __construct(){

		add_action('admin_menu',  array($this, 'csp_admin_options'));
		add_action('admin_init', array($this, 'settings'));
		add_filter('the_content', array($this, 'ifWrap'));
		add_action('init', array($this, 'languages'));
	}

	function languages(){
		load_plugin_textdomain('cspreadingtime',false, dirname(plugin_basename(__FILE__)). '/languages');
	}

	function ifWrap($content){
		if( is_main_query() AND is_single() AND
			(
				get_option('wcp_wordcount','1') OR
				get_option('wcp_charcount','1') OR
				get_option('wcp_readtime','1')
			)
		){
			return $this->createHTML($content);
		}

		return $content;

	}

	function createHTML($content){
		$html = '<h3>'.esc_html(get_option('wcp_headline','Post Info')).'</h3><p></p>';

		if(get_option('wcp_wordcount','1') OR get_option('wcp_readtime','1')){

		$word_count = str_word_count(strip_tags($content));

		}

		if(get_option('wcp_wordcount','1') ){
			$html .= esc_html__('This post has','cspreadingtime').' '.$word_count.' word count<br>';
		}

		if(get_option('wcp_charcount','1') ){
			$html .= 'This post has '.strlen($content).' char count<br>';
		}

		if(get_option('wcp_readtime','1') ){
			$html .= 'This post will take '.round($word_count/225).' minute to read<br>';
		}

		$html .= '</p>';


		if(get_option('wcp_location','0') == '0' ){
			return $html.$content;
		}
		return $content.$html;
	}



	function settings(){
		add_settings_section('wcp_first_section',null,'__return_false','word-count-setting-page');

		add_settings_field(
			'wcp_location', 
			'Display Location', 
			array($this, 'locationHTML'),
			'word-count-setting-page',
			'wcp_first_section'
			);
		register_setting(
			'wordcountplugin',
			'wcp_location',
			array('sanitize_callback' => array($this, 'sanitize_location'), 'default' => '0')
		);

		add_settings_field(
			'wcp_headline', 
			'Headline', 
			array($this, 'headlineHTML'),
			'word-count-setting-page',
			'wcp_first_section'
			);
		register_setting(
			'wordcountplugin',
			'wcp_headline',
			array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statictics')
		);

		add_settings_field(
			'wcp_wordcount', 
			'Word Count', 
			array($this, 'checkBoxHTML'),
			'word-count-setting-page',
			'wcp_first_section',
			array('theName' => 'wcp_wordcount', 'label_for'=>'wcp_wordcount', 'class'=> 'csp_wordcount')
			);
		register_setting(
			'wordcountplugin',
			'wcp_wordcount',
			array('sanitize_callback' => 'sanitize_text_field', 'default' => '1')
		);

		add_settings_field(
			'wcp_charcount', 
			'Character Count', 
			array($this, 'checkBoxHTML'),
			'word-count-setting-page',
			'wcp_first_section',
			array('theName' => 'wcp_charcount', 'label_for'=>'wcp_charcount')
			);
		register_setting(
			'wordcountplugin',
			'wcp_charcount',
			array('sanitize_callback' => 'sanitize_text_field', 'default' => '1')
		);

		add_settings_field(
			'wcp_readtime', 
			'Read Time', 
			array($this, 'checkBoxHTML'),
			'word-count-setting-page',
			'wcp_first_section',
			array('theName' => 'wcp_readtime', 'label_for'=>'wcp_readtime')
			);
		register_setting(
			'wordcountplugin',
			'wcp_readtime',
			array('sanitize_callback' => 'sanitize_text_field', 'default' => '1')
		);

	}

	function sanitize_location($input){
		if($input != '0' AND $input != '1'){
			add_settings_error('wcp_location','wcp_location_error','Display location must be 0 or 1');
			return get_option('wcp_location');
		}
		return $input;
	}

	function locationHTML(){ ?>

		<select name="wcp_location">
			<option value="0" <?php selected(get_option('wcp_location'),'0') ?>>Beginning of post</option>
			<option value="1" <?php selected(get_option('wcp_location'),'1') ?>>End of Post</option>
		</select>

		<?php }

	function headlineHTML(){ ?>

		<input type="text" name="wcp_headline" value="<?php echo esc_attr(get_option('wcp_headline')); ?>">

		<?php }


	function checkBoxHTML($args){ ?>

		<input id="<?php echo $args['theName']; ?>"  type="checkbox" name="<?php echo $args['theName']; ?>" value="1" <?php  checked(get_option($args['theName']), '1'); ?>>

		<?php }



	function csp_admin_options(){

		add_options_page('Word Count Setting', __('CSP Reading Time','cspreadingtime'), 'manage_options','word-count-setting-page',
			array($this,'adminOptionHtml' ));

	}

	function adminOptionHtml(){
		?>
		<div class="wrap">
			<h1>Word Count Setting</h1>
			<form action="options.php" method="POST">
				<?php 
				settings_fields('wordcountplugin');
				do_settings_sections('word-count-setting-page');
				submit_button();
				?>
			</form>
		</div>

		<?php
	}

}

$cspReadingTime = new CSP_Reading_Time();


