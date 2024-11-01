<?php 

class Single_Term_Taxonomies {

	private $plugin_name;
	private $plugin_slug;
	private $plugin_dir_url;
	private $settings_option_key = 'single_term_taxonomies';
	private $nonce_action = 'stt-settings-save';
	private $nonce_value;
	private $output_buffering = 0;

	public function __construct() {
		$this->plugin_name = __('Single-Term Taxonomies', 'stt');
		$this->plugin_slug = 'single-term-taxonomies';
		$this->plugin_dir_url = plugin_dir_url( dirname(__FILE__) );
		$this->nonce_value = wp_create_nonce( $this->nonce_action );

		load_plugin_textdomain( 'stt', false, basename($this->plugin_dir_url) . '/languages' );

		$nonce = isset( $_POST['_stt_settings_save_nonce'] ) ? $_POST['_stt_settings_save_nonce'] : 0;
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' && wp_verify_nonce($nonce, $this->nonce_action) ) {
			$raw_settings = isset( $_POST['single_term_taxonomies'] ) ? $_POST['single_term_taxonomies'] : array();
			$nice_settings = $this->raw_to_nice_settings( $raw_settings );
			
			$this->update_settings( $nice_settings );

			add_action( 'admin_notices', array(&$this, 'show_settings_saved_message') );
		}

		add_action( 'admin_init', array(&$this, 'include_extended_walker') );
		add_action( 'admin_menu', array(&$this, 'add_settings_page_to_menu') );
		add_action( 'admin_enqueue_scripts', array(&$this, 'enqeue_scripts') );
		add_action( 'admin_head', array(&$this, 'print_scripts') );
		add_action( 'set_object_terms', array(&$this, 'remove_extra_terms'), 10, 6 );

		add_filter( 'wp_terms_checklist_args', array(&$this, 'replace_checkboxes_with_radios') );
		add_filter( 'get_terms', array(&$this, 'buffer_output') );
	}

	public function add_settings_page_to_menu() {
		add_options_page( $this->plugin_name, $this->plugin_name, 'manage_options', $this->plugin_slug, array(&$this, 'print_settings_page_content') );
	}

	public function print_settings_page_content() {
		require_once( 'settings-page.php' );
	}

	public function enqeue_scripts() {
		$current_screen = get_current_screen();

		if ( $current_screen->base == 'post' ) {
			wp_enqueue_script( $this->plugin_slug, $this->plugin_dir_url . 'js/single-term-taxonomies.js', array('jquery') );
		}

		if ( $current_screen->base == ('settings_page_' . $this->plugin_slug) ) {
			wp_enqueue_style( $this->plugin_slug, $this->plugin_dir_url . 'css/single-term-taxonomies.css' );
		}
	}

	public function print_scripts() {
		$current_screen = get_current_screen();

		if ( $current_screen->base == 'post' ) :
			?>

			<script type="application/json" class="stt-settings-json">
				<?php echo json_encode( $this->get_single_term_taxonomies() ) ?>
			</script>

			<?php
		endif;
	}

	public function include_extended_walker() {
		require_once('class-stt-walker-category-radiolist.php');
	}

	public function replace_checkboxes_with_radios($args) {
		$settngs = $this->get_single_term_taxonomies();

		if ( !in_array($args['taxonomy'], $settngs) ) return $args;

		$args['walker'] = new STT_Walker_Category_Radiolist;
		$input_field_name = $this->get_input_field_name_by_tax( $args['taxonomy'] );

		if ( $this->get_output_buffering() ) {
			echo str_replace('type="checkbox"', 'type="radio" name="' . $input_field_name . '"', $this->turn_off_output_buffering());
		}

		return $args;
	}

	public function buffer_output($terms) {
		if ( $this->is_buffered_output_necessary() ) {
			$this->turn_on_output_buffering();
		}

		return $terms;
	}

	public function is_buffered_output_necessary() {
		$backtrace = debug_backtrace();

		foreach ($backtrace as $i => $backtrace_item) {
			if ( $backtrace_item['function'] == 'wp_popular_terms_checklist' && $backtrace[$i + 1]['function'] == 'post_categories_meta_box' ) {
				return true;
			}
		}

		return false;
	}

	public function get_output_buffering() {
		return $this->output_buffering;
	}

	public function turn_on_output_buffering() {
		$this->output_buffering = 1;

		ob_start();
	}

	public function turn_off_output_buffering() {
		$this->output_buffering = 0;

		return ob_get_clean();
	}

	public function get_single_term_taxonomies() {
		return (array)get_option( $this->settings_option_key );
	}

	public function update_settings($new_settings) {
		if ( !is_array($new_settings) ) return false;

		return update_option( $this->settings_option_key, $new_settings );
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function show_settings_saved_message() {
		?>

		<div class="updated">
	        <p><?php _e('Settings updated!', 'stt'); ?></p>
	    </div>

		<?php
	}

	public function get_nonce_action() {
		return $this->nonce_action;
	}

	public function get_nonce_value() {
		return $this->nonce_value;
	}

	public function get_taxonomies() {
		$taxonomy_slugs = get_taxonomies();
		$excludes = apply_filters( 'stt_exclude_taxonomies', array('nav_menu', 'link_category', 'post_format') );

		$filtered_taxonomy_slugs = array_diff( $taxonomy_slugs, $excludes );
		$filtered_taxonomies = array();

		foreach ($filtered_taxonomy_slugs as $tax_slug) {
			$filtered_taxonomies[ $tax_slug ] = get_taxonomy( $tax_slug );
		}

		return $filtered_taxonomies;
	}

	public function remove_extra_terms($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids) {
		$st_taxonomies = $this->get_single_term_taxonomies();
		
		if ( in_array($taxonomy, $st_taxonomies) && count($tt_ids) > 1 ) {
			unset( $tt_ids[0] );

			$tt_ids = array_map('intval', $tt_ids);

			wp_remove_object_terms( $object_id, $tt_ids, $taxonomy);
		}
	}

	private function raw_to_nice_settings($raw_settings) {
		$nice_settings = array();

		foreach ((array)$raw_settings as $tax_slug => $val) {
			$nice_settings[] = $tax_slug;
		}

		return $nice_settings;
	}

	private function get_input_field_name_by_tax($taxonomy) {
		if ( $taxonomy == 'category' ) {
			return 'post_category[]';
		}
		
		return 'tax_input[' . $taxonomy . '][]';
	}

}