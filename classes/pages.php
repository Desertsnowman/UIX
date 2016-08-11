<?php
/**
 * UIX Pages.
 *
 * @package   uix
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2016 David Cramer
 */
namespace uixv2;

/**
 * Settings class
 * @package uix
 * @author  David Cramer
 */
class pages extends core{

	/**
	 * The type of object
	 *
	 * @since 1.0.0
	 *
	 * @var      string
	 */
	protected $type = 'page';

	/**
	 * Holds a pages instance
	 *
	 * @since 1.0.0
	 *
	 * @var      object|\uix\pages
	 */
	private static $instance = null;

	/**
	 * Holds the option screen prefix
	 *
	 * @since 1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Holds the current page slug
	 *
	 * @since 1.0.0
	 *
	 * @var      string
	 */
	protected $current_page = null;	

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return    object|\uix\pages    A single instance of pages
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

	/**
	 * register add settings pages
	 *
	 * @since 1.0.0
	 *
	 */
	protected function actions() {
		// add settings page
		add_action( 'admin_menu', array( $this, 'add_settings_pages' ), 25 );
		// save config
		add_action( 'wp_ajax_uix_' . $this->type . '_save_config', array( $this, 'save_config') );
	}

	/**
	 * Saves a config
	 *
	 * @uses "wp_ajax_uix_save_config" hook
	 *
	 * @since 0.0.1
	 */
	public function save_config(){

		if( ! empty( $_POST[ 'config' ] ) ){

			$config = json_decode( stripslashes_deep( $_POST[ 'config' ] ), true );
			if(	!wp_verify_nonce( $_POST['uix_setup'], $this->type ) ){
				wp_send_json_error( esc_html__( 'Could not verify nonce', $this->type ) );
			}

			$page_slug = sanitize_text_field( $_POST['page_slug'] );
			$page = $this->get( $page_slug );

			if( !empty( $page ) ){

				$saved = $this->save_data( $page_slug, $config );

				wp_send_json_success( );

			}
		}

		// nope
		wp_send_json_error( esc_html__( 'Could not save, sorry.' ) );
	}

	/**
	 * get page slug
	 * @since 0.0.1
	 *
	 * @return string $slug of current UIX page
	 */
	protected function get_page_slug(){
		// check that the scrren object is valid to be safe.
		$screen = get_current_screen();
			
		if( empty( $screen ) || !is_object( $screen ) ){
			return null;
		}

		// get the page slug from base ID
		return array_search( $screen->base, $this->plugin_screen_hook_suffix );
	}


	/**
	 * Determin if a UIX [page] should be loaded for this screen
	 * @since 0.0.1
	 *
	 * @return string $slug slug of a registered structure relating to this screen
	 */
	protected function locate(){

		// get the page slug from base ID
		$page_slug = $this->get_page_slug();

		if( !empty( $page_slug ) && !empty( $this->objects[ $page_slug ] ) ){
			$this->current_page = $page_slug;
		}


		return $page_slug;
	}

	/**
	 * Add settings page
	 *
	 * @since 0.0.1
	 *
	 * @uses "admin_menu" hook
	 */
	public function add_settings_pages(){

		foreach( (array) $this->objects as $page_slug => $page ){

			if( empty( $page[ 'page_title' ] ) || empty( $page['menu_title'] ) ){
				continue;
			}

			$args = array(
				'capability'	=> 'manage_options',
				'icon'			=>	null,
				'position'		=> null
			);
			$args = array_merge( $args, $page );

			if( !empty( $page['parent'] ) ){

				$this->plugin_screen_hook_suffix[ $page_slug ] = add_submenu_page(
					$args[ 'parent' ],
					$args[ 'page_title' ],
					$args[ 'menu_title' ],
					$args[ 'capability' ], 
					$this->type . '-' . $page_slug,
					array( $this, 'create_admin_page' )
				);

			}else{

				$this->plugin_screen_hook_suffix[ $page_slug ] = add_menu_page(
					$args[ 'page_title' ],
					$args[ 'menu_title' ],
					$args[ 'capability' ], 
					$this->type . '-' . $page_slug,
					array( $this, 'create_admin_page' ),
					$args[ 'icon' ],
					$args[ 'position' ]
				);
			}
			
			//add help if defined
			if( !empty( $page['help'] ) ){
				add_action( 'load-' . $this->plugin_screen_hook_suffix[ $page_slug ], array( $this, 'add_help' ) );
			}
			
		}
	}

	/**
	 * Add defined contextual help to admin page
	 *
	 * @since 1.0.0
	 */
	public function add_help(){
		

		$page = $this->get_page();
		
		$screen = get_current_screen();
		
		foreach( (array) $page['help'] as $help_slug => $help ){

			if( is_file( $help['content'] ) && file_exists( $help['content'] ) ){
				ob_start();
				include $help['content'];
				$content = ob_get_clean();
			}else{
				$content = $help['content'];
			}

			$screen->add_help_tab( array(
				'id'       =>	$help_slug,
				'title'    =>	$help['title'],
				'content'  =>	$content
			));
		}
		
		// Help sidebars are optional
		if(!empty( $page['help_sidebar'] ) ){
			$screen->set_help_sidebar( $page['help_sidebar'] );
		}

	}
	
	/**
	 * fetch UIX object for current admin page
	 *
	 * @since 0.0.1
	 */
	protected function get_page(){
			
		return $this->get( $this->current_page );

	}

	/**
	 * Options page callback
	 *
	 * @since 0.0.1
	 */
	public function create_admin_page(){

		$uix = $this->get_page();
		if( !empty( $uix['base_color'] ) ){
		?><style type="text/css">.contextual-help-tabs .active {border-left: 6px solid <?php echo $uix['base_color']; ?>;}.wrap > h1.uix-title {box-shadow: 0 0 2px rgba(0, 2, 0, 0.1),11px 0 0 <?php echo $uix['base_color']; ?> inset;}.uix-modal-wrap .uix-modal-title > h3,.wrap .uix-title a.page-title-action:hover{background: <?php echo $uix['base_color']; ?>; border-color: <?php echo $uix['base_color']; ?>;}.wrap .uix-title a.page-title-action:focus{box-shadow: 0 0 2px <?php echo $uix['base_color']; ?>;border-color: <?php echo $uix['base_color']; ?>;}</style>
		<?php
		}
		?>
		<div class="wrap uix-item" data-uix="<?php echo esc_attr( $this->current_page ); ?>">
			<h1 class="uix-title"><?php esc_html_e( $uix['page_title'] , $this->type ); ?>
				<?php if( !empty( $uix['version'] ) ){ ?><small><?php esc_html_e( $uix['version'], $this->type ); ?></small><?php } ?>
				<?php if( !empty( $uix['save_button'] ) ){ ?>
				<a class="page-title-action" href="#save-object" data-save-object="true">
					<span class="spinner uix-save-spinner"></span>
					<?php esc_html_e( $uix['save_button'], $this->type ); ?>
				</a>
				<?php } ?>
			</h1>
			<?php if( !empty( $uix['tabs'] ) ){ ?>
			<nav class="uix-sub-nav" <?php if( count( $uix['tabs'] ) === 1 ){ ?>style="display:none;"<?php } ?>>
				<?php foreach( (array) $uix['tabs'] as $tab_slug => $tab ){ ?><a data-tab="<?php echo esc_attr( $tab_slug ); ?>" href="#<?php echo esc_attr( $tab_slug ) ?>"><?php echo esc_html( $tab['menu_title'] ); ?></a><?php } ?>
			</nav>
			<?php } ?>
			<?php wp_nonce_field( $this->type, 'uix_setup' ); ?>
			<?php 
			if( !empty( $uix['tabs'] ) ){
				foreach( (array) $uix['tabs'] as $tab_slug => $tab ){ ?>
					<div class="uix-tab-canvas" data-app="<?php echo esc_attr( $tab_slug ); ?>"></div>
					<script type="text/html" data-template="<?php echo esc_attr( $tab_slug ); ?>">
						<?php 
							if( !empty( $tab['page_title'] ) ){ echo '<h4>' . $tab['page_title']; }
							if( !empty( $tab['page_description'] ) ){ ?> <small><?php echo $tab['page_description']; ?></small> <?php } 
							if( !empty( $tab['page_title'] ) ){ echo '</h4>'; }
							// include this tabs template
							if( !empty( $tab['template'] ) && file_exists( $tab['template'] ) ){
								include $tab['template'];
							}else{
								echo esc_html__( 'Template not found: ', $this->type ) . $tab['page_title'];
							}
						?>
					</script>
					<?php if( !empty( $tab['partials'] ) ){
						foreach( $tab['partials'] as $partial_id => $partial ){
							?>
							<script type="text/html" id="__partial_<?php echo esc_attr( $partial_id ); ?>" data-handlebars-partial="<?php echo esc_attr( $partial_id ); ?>">
								<?php
									// include this tabs template
									if( !empty( $partial ) && file_exists( $partial ) ){
										include $partial;
									}else{
										echo esc_html__( 'Partial Template not found: ', $this->type ) . $partial_id;
									}
								?>
							</script>
							<?php
						}
					}
				}
			}else{
				if( !empty( $uix['template'] ) && file_exists( $uix['template'] ) ){
					include $uix['template'];
				}
			}
			if( !empty( $uix['modals'] ) ){
				foreach( $uix['modals'] as $modal_id => $modal ){
					?>
					<script type="text/html" id="__modal_<?php echo esc_attr( $modal_id ); ?>" data-handlebars-partial="<?php echo esc_attr( $modal_id ); ?>">
						<?php
							// include this tabs template
							if( !empty( $modal ) && file_exists( $modal ) ){
								include $modal;
							}else{
								echo esc_html__( 'Modal Template not found: ', $this->type ) . $modal_id;
							}
						?>
					</script>
					<?php
				}
			}
			?>			
		</div>

		<script type="text/html" data-template="__notice">
		<div class="{{#if success}}updated{{else}}error{{/if}} notice uix-notice is-dismissible">
			<p>{{{data}}}</p>
			<button class="notice-dismiss" type="button">
				<span class="screen-reader-text">Dismiss this notice.</span>
			</button>
		</div>
		</script>
		<script type="text/html" id="__partial_save">		
			<button class="button" type="button" data-modal-node="{{__node_path}}" data-app="{{__app}}" data-type="save" 
				{{#if __callback}}data-callback="{{__callback}}"{{/if}}
				{{#if __before}}data-before="{{__before}}"{{/if}}
			>
				Save Changes
			</button>
		</script>
		<script type="text/html" id="__partial_create">
			<button class="button" type="button" data-modal-node="{{__node_path}}" data-app="{{__app}}" data-type="add" 
				{{#if __callback}}data-callback="{{__callback}}"{{/if}}
				{{#if __before}}data-before="{{__before}}"{{/if}}
			>
				Create
			</button>
		</script>
		<script type="text/html" id="__partial_delete">
			<button style="float:left;" class="button" type="button" data-modal-node="{{__node_path}}" data-app="{{__app}}" data-type="delete" 
				{{#if __callback}}data-callback="{{__callback}}"{{/if}}
				{{#if __before}}data-before="{{__before}}"{{/if}}
			>
				Remove
			</button>
		</script>
		<?php
	}
	
}