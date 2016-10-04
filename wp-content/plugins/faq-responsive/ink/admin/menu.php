<?php
class wpsm_faq {
	private static $instance;
    public static function forge() {
        if (!isset(self::$instance)) {
            $className = __CLASS__;
            self::$instance = new $className;
        }
        return self::$instance;
    }
	
	private function __construct() {
		add_action('admin_enqueue_scripts', array(&$this, 'wpsm_faq_admin_scripts'));
        if (is_admin()) {
			add_action('init', array(&$this, 'wpsm_faq_register_cpt'), 1);
			add_action('add_meta_boxes', array(&$this, 'wpsm_faq_meta_boxes_group'));
			add_action('admin_init', array(&$this, 'wpsm_faq_meta_boxes_group'), 1);
			add_action('save_post', array(&$this, 'add_faq_meta_box_save'), 9, 1);
			add_action('save_post', array(&$this, 'faq_settings_meta_box_save'), 9, 1);
		}
    }
	
	// admin scripts
	public function wpsm_faq_admin_scripts(){
		if(get_post_type()=="wpsm_faq_r"){
			
			wp_enqueue_media();
			wp_enqueue_script('jquery-ui-datepicker');
			//color-picker css n js
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wpsm_faq_sh-color-pic', wpshopmart_faq_directory_url.'assets/js/color-picker.js', array( 'wp-color-picker' ), false, true );
			 wp_enqueue_style('wpsm_faq_sh-panel-style', wpshopmart_faq_directory_url.'assets/css/panel-style.css');
			  
			 wp_enqueue_style('wpsm_faq_sh_remodal-css', wpshopmart_faq_directory_url .'assets/modal/remodal.css');
			wp_enqueue_style('wpsm_faq_sh_remodal-default-theme-css', wpshopmart_faq_directory_url .'assets/modal/remodal-default-theme.css');
			 
			  
			//font awesome css
			wp_enqueue_style('wpsm_faq_sh-font-awesome', wpshopmart_faq_directory_url.'assets/css/font-awesome/css/font-awesome.min.css');
			wp_enqueue_style('wpsm_faq_sh_bootstrap', wpshopmart_faq_directory_url.'assets/css/bootstrap.css');
			wp_enqueue_style('wpsm_faq_sh_font-awesome-picker', wpshopmart_faq_directory_url.'assets/css/fontawesome-iconpicker.css');
			wp_enqueue_style('faq_jquery-css', wpshopmart_faq_directory_url .'assets/css/ac_jquery-ui.css');
			
			//line editor
			wp_enqueue_style('wpsm_faq_sh_line-edtor', wpshopmart_faq_directory_url.'assets/css/jquery-linedtextarea.css');
			wp_enqueue_script( 'wpsm_faq_sh-line-edit-js', wpshopmart_faq_directory_url.'assets/js/jquery-linedtextarea.js');
			
			wp_enqueue_script( 'wpsm_faq_sh-bootstrap-js', wpshopmart_faq_directory_url.'assets/js/bootstrap.js');
			
			//tooltip
			wp_enqueue_style('wpsm_faq_sh_tooltip', wpshopmart_faq_directory_url.'assets/tooltip/darktooltip.css');
			wp_enqueue_script( 'wpsm_faq_sh-tooltip-js', wpshopmart_faq_directory_url.'assets/tooltip/jquery.darktooltip.js');
			// settings
			wp_enqueue_style('wpsm_faq_sh_settings-css', wpshopmart_faq_directory_url.'assets/css/settings.css');
			wp_enqueue_script('faq_sh_font-icon-picker-js', wpshopmart_faq_directory_url.'assets/js/fontawesome-iconpicker.js',array('jquery'));
			wp_enqueue_script('faq_sh_call-icon-picker-js', wpshopmart_faq_directory_url.'assets/js/call-icon-picker.js',array('jquery'), false, true);
			wp_enqueue_script('faq_sh_remodal-min-js',wpshopmart_faq_directory_url.'assets/modal/remodal.min.js',array('jquery'), false, true);
	
		
			}
	}
	
	public function wpsm_faq_register_cpt(){
		require_once('cpt-reg.php');
		add_filter( 'manage_edit-wpsm_faq_r_columns', array(&$this, 'faq_panels_columns' )) ;
		add_action( 'manage_wpsm_faq_r_posts_custom_column', array(&$this, 'faq_panels_manage_columns' ), 10, 2 );
	}
	
	function faq_panels_columns( $columns ){
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'title' => __( 'Faq' ),
            'shortcode' => __( 'Faq Shortcode' ),
            'date' => __( 'Date' )
        );
        return $columns;
    }

    function faq_panels_manage_columns( $column, $post_id ){
        global $post;
        switch( $column ) {
          case 'shortcode' :
            echo '<input style="width:225px" type="text" value="[WPSM_FAQ id='.$post_id.']" readonly="readonly" />';
            break;
          default :
            break;
        }
    }
	
	// metaboxes
	public function wpsm_faq_meta_boxes_group(){
		add_meta_box('add_faq', __('Add Faq Panel', wpshopmart_faq_text_domain), array(&$this, 'wpsm_add_faq_meta_box_function'), 'wpsm_faq_r', 'normal', 'low' );
		add_meta_box ('faq_shortcode', __('Faq Shortcode', wpshopmart_faq_text_domain), array(&$this, 'wpsm_pic_faq_shortcode'), 'wpsm_faq_r', 'normal', 'low');
		add_meta_box('faq_follow', __('Follow Us', wpshopmart_faq_text_domain), array(&$this, 'wpsm_faq_follow_meta_box_function'), 'wpsm_faq_r', 'side', 'low');
		
		add_meta_box('faq_rateus', __('Rate Us If You Like This Plugin', wpshopmart_faq_text_domain), array(&$this, 'wpsm_faq_rateus_meta_box_function'), 'wpsm_faq_r', 'side', 'low');
		add_meta_box('faq_setting', __('Faq Settings', wpshopmart_faq_text_domain), array(&$this, 'wpsm_add_faq_setting_meta_box_function'), 'wpsm_faq_r', 'side', 'low');
		add_meta_box('accordion_designs', __('Accordion/Faq Design', wpshopmart_faq_text_domain), array(&$this, 'wpsm_add_faq_sh_designs_meta_box_function'), 'wpsm_faq_r', 'normal', 'low');
		add_meta_box ('faq_more_pro', __('MOre Pro Plugin From Wpshopmart', wpshopmart_faq_text_domain), array(&$this, 'wpsm_pic_faq_more_pro'), 'wpsm_faq_r', 'normal', 'low');
		
	}
	
	public function wpsm_add_faq_meta_box_function($post){
		require_once('add-faq.php');
	}
	
	public function wpsm_pic_faq_shortcode(){
		?>
		<style>
			#faq_shortcode{
			background:#fff!important;
			box-shadow: 0 0 20px rgba(0,0,0,.2);
			}
			#faq_shortcode .hndle , #faq_shortcode .handlediv{
			display:none;
			}
			#faq_shortcode p{
			color:#000;
			font-size:15px;
			}
			#faq_shortcode input {
			font-size: 16px;
			padding: 8px 10px;
			width:100%;
			}
			
			
		</style>
		<h3>Faq Shortcode</h3>
		<p><?php _e("Use below shortcode in any Page/Post to publish your FAQ", wpshopmart_faq_text_domain);?></p>
		<input readonly="readonly" type="text" value="<?php echo "[WPSM_FAQ id=".get_the_ID()."]"; ?>">
		<?php
		 $PostId = get_the_ID();
		$Settings = unserialize(get_post_meta( $PostId, 'Wpsm_Faq_Shortcode_Settings', true));
		if(isset($Settings['custom_css'])){  
		     $custom_css   = $Settings['custom_css'];
		}
		else{
		$custom_css="";
		}		
		?>
			<div>
			<h3>To activate widget into any widget area</H3>
			<p><a href="<?php get_site_url();?>./widgets.php" >Click Here</a>. </p>
			<p>Find <b>FAQ Widget </b> and place it to your widget area. Select any FAQ from the list and then save changes.</p>
		</div>	
		
		<h3>Custom Css</h3>
		<textarea name="custom_css" id="custom_css" style="width:100% !important ;height:300px;background:#ECECEC;"><?php echo $custom_css ; ?></textarea>
		<p>Enter Css without <strong>&lt;style&gt; &lt;/style&gt; </strong> tag</p>
		<br>
		<?php if(isset($Settings['custom_css'])){ ?> 
		<h3>Add This FAQ settings as default setting for new FAQs</h3>
		<div class="">
			<a  class="button button-primary button-hero" name="updte_wpsm_faq_default_settings" id="updte_wpsm_faq_default_settings" onclick="wpsm_update_default()">Update Default Settings</a>
		</div>	
		<?php } ?>
		<?php 
	}
	
	public function wpsm_add_faq_sh_designs_meta_box_function(){
		?>
		
		<h1>Accordion/Faq Pro Designs</h1>
			<div style="overflow:hidden;display:block;width:100%;padding-top:20px">
				<?php for($i=1;$i<=8;$i++){ 
				if($i==2){
					$var = "Selected";
					
				}
				else{
					$var = "Available In Pro";
				}
				?>
				<div class="col-md-3">
					<div class="demoftr">	
						
						<div class="">
							<div class="wpsm_home_portfolio_showcase">
								<div class="wpsm_ribbon"><a target="_blank" href="http://wpshopmart.com/plugins/accordion-pro/"><span><?php echo $var; ?></span></a></div>
								<img class="wpsm_img_responsive ftr_img" src="<?php echo wpshopmart_faq_directory_url.'assets/images/design/accordion-'.$i.'.png'?>">
								</div>
						</div>
						<div style="padding:13px;overflow:hidden; background: #EFEFEF; border-top: 1px dashed #ccc;">
							<h3 class="text-center pull-left" style="margin-top: 10px;margin-bottom: 10px;font-weight:900">Design <?php echo $i; ?></h3>
							<a type="button"  class="pull-right btn btn-danger design_btn" id="templates_btn<?php echo $i; ?>" target="_blank" href="http://demo.wpshopmart.com/accordion-pro/accordion-template-<?php echo $i; ?>/" >Check Demo</a>
								</div>		
					</div>	
				</div>
				<?php } ?>
				<a class="btn btn-success btn-lg " href="http://wpshopmart.com/plugins/accordion-pro/" target="_blank">Get Accordion/Faq Pro Only In $6</a>
			</div>
		<?php		
		
	}
	public function wpsm_pic_faq_more_pro(){
		?>
		<style>
			#faq_more_pro{
			background:#fff!important;
			box-shadow: 0 0 20px rgba(0,0,0,.2);
			margin-top:40px;
			}
			#faq_more_pro .hndle , #faq_more_pro .handlediv{
			display:none;
			}
			#faq_more_pro p{
			color:#000;
			font-size:15px;
			}
			.wpsm-theme-container {
				background: #fff;
				padding-left: 0px;
				padding-right: 0px;
				box-shadow: 0 0 20px rgba(0,0,0,.2);
			}
			.wpsm_site-img-responsive {
				display: block;
				width: 100%;
				height: auto;
			}
			.wpsm_product_wrapper {
				padding: 20px;
				overflow: hidden;
			}
			.wpsm_product_wrapper h3 {
				float: left;
				margin-bottom: 0px;
				color: #000 !important;
				letter-spacing: 0px;
				text-transform: uppercase;
				font-size: 18px;
				font-weight: 700;
				text-align: left;
				margin:0px;
			}
			.wpsm_product_wrapper h3 span {
				display: block;
				float: left;
				width: 100%;
				overflow: hidden;
				font-size: 14px;
				color: #919499;
				margin-top: 6px;
			}
			.wpsm_product_wrapper .price {
				float: right;
				font-size: 24px;
				color: #000;
				font-family: sans-serif;
				font-weight: 500;
			}
			.wpsm-btn-block {
				overflow: hidden;
				float: left;
				width: 100%;
				margin-top: 20px;
				display: block;
			}
			.portfolio_read_more_btn {
				border: 1px solid #1e73be;
				border-radius: 0px;
				margin-bottom: 10px;
				text-transform: uppercase;
				font-weight: 700;
				font-size: 15px;
				padding: 12px 12px;
				display: block;
				text-align:center;
				width:100%;
				border-radius: 2px;
				cursor: pointer;
				letter-spacing: 1px;
				outline: none;
				position: relative;
				text-decoration: none !important;
				color: #fff !important;
				-webkit-transition: all ease 0.5s;
				-moz-transition: all ease 0.5s;
				transition: all ease 0.5s;
				background: #1e73be;
				padding-left: 22px;
				padding-right: 22px;
			}
			.portfolio_demo_btn {
				border: 1px solid #919499;
				border-radius: 0px;
				margin-bottom: 10px;
				text-transform: uppercase;
				font-weight: 700;
				font-size: 15px;
				padding: 12px 12px;
				display: block;
				text-align:center;
				width:100%;
				border-radius: 2px;
				cursor: pointer;
				letter-spacing: 1px;
				outline: none;
				position: relative;
				text-decoration: none !important;
				background-color: #242629;
				border-color: #242629;
				color: #fff !important;
				-webkit-transition: all ease 0.5s;
				-moz-transition: all ease 0.5s;
				transition: all ease 0.5s;
				padding-left: 22px;
				padding-right: 22px;
			}
		</style>
		<h1>More Recommended Premium Plugin From Wpshopmart</h1>
			<div style="overflow:hidden;display:block;width:100%;padding-top:20px;padding-bottom:20px">
				<div class="col-md-12">
					<div class="col-md-4"> 
						<a href="http://wpshopmart.com/plugins/colorbox-pro/" target="_blank" title="ColorBox Pro">
							<div class="wpsm-theme-container" style="">
								<img width="700" height="394" src="<?php echo wpshopmart_faq_directory_url.'assets/images/cb.png'; ?>" class="wpsm_site-img-responsive wp-post-image" alt="Colorbox and panels pro plugin">
								<div class="wpsm_product_wrapper">
									<h3>ColorBox Pro <span>wordpress</span></h3>
									<span class="price"><span class="amount">$5</span></span>
									<div class="wpsm-btn-block" style="">
																		
										<a title="Check Detail" target="_blank" href="http://wpshopmart.com/plugins/colorbox-pro/" class="portfolio_read_more_btn pull-left">Check Detail</a>
										<a title="View Demo" target="_blank" href="http://demo.wpshopmart.com/colorbox-pro/" class="portfolio_demo_btn pull-right">View Demo</a>
									</div>
								</div>
							</div>
						</a>
					</div>
					<div class="col-md-4"> 
						<a href="http://wpshopmart.com/plugins/accordion-pro/" target="_blank" title="Accordion Pro">
							<div class="wpsm-theme-container" style="">
								<img width="700" height="394" src="<?php echo wpshopmart_faq_directory_url.'assets/images/ac.png'; ?>" class="wpsm_site-img-responsive wp-post-image" alt="Colorbox and panels pro plugin">
								<div class="wpsm_product_wrapper">
									<h3>Accordion Pro<span>wordpress</span></h3>
									<span class="price"><span class="amount">$6</span></span>
									<div class="wpsm-btn-block" style="">
																		
										<a title="Check Detail" target="_blank" href="http://wpshopmart.com/plugins/accordion-pro/" class="portfolio_read_more_btn pull-left">Check Detail</a>
										<a title="View Demo" target="_blank" href="http://demo.wpshopmart.com/accordion-pro/" class="portfolio_demo_btn pull-right">View Demo</a>
									</div>
								</div>
							</div>
						</a>
					</div>
					
					<div class="col-md-4"> 
						<a href="http://wpshopmart.com/plugins/coming-soon-pro/" target="_blank" title="Coming Soon Pro">
							<div class="wpsm-theme-container" style="">
								<img width="700" height="394" src="<?php echo wpshopmart_faq_directory_url.'assets/images/csp.png'; ?>" class="wpsm_site-img-responsive wp-post-image" alt="Colorbox and panels pro plugin">
								<div class="wpsm_product_wrapper">
									<h3>Coming Soon Pro <span>wordpress</span></h3>
									<span class="price"><span class="amount">$19</span></span>
									<div class="wpsm-btn-block" style="">
										<a title="Check Detail" target="_blank" href="http://wpshopmart.com/plugins/coming-soon-pro/" class="portfolio_read_more_btn pull-left">Check Detail</a>
										<a title="View Demo" target="_blank" href="http://wpshopmart.com/coming-soon-pro-demo-page/" class="portfolio_demo_btn pull-right">View Demo</a>
									</div>
								</div>
							</div>
						</a>
					</div>
				</div>
			</div>		
	<?php 	
		
		
		
	}
	public function wpsm_faq_follow_meta_box_function(){
		?>
		<style>
		.follow_icon li {   
			margin-right: 8px;
			margin-left: 8px;
			font-size: 20px;
			display: inline-block;
			width:40px;
			height:40px;
			text-align:center;
			line-height: 38px;
		}
		.follow_icon li a {
			color:#fff;
		}
		
		</style>
		<ul class="follow_icon">
			<li  style="background: rgb(59, 89, 152);">
				<a href="https://www.facebook.com/wpshopmart/" target="_blank"><i class="fa fa-facebook"></i></a>
			</li>
			<li  style="background: rgb(0, 172, 237);">
				<a href="https://twitter.com/wpshopmart1" target="_blank"><i class="fa fa-twitter"></i></a>
			</li>
			<li  style="background: rgb(196, 50, 32);">
				<a href="https://plus.google.com/u/0/108719707535083220625" target="_blank"><i class="fa fa-google-plus"></i></a>
			</li>
			<li  style="background: rgb(178, 7, 14)">
				<a href="https://www.youtube.com/channel/UCkXM_n1r869EVpAaatHnXcw" target="_blank"><i class="fa fa-youtube"></i></a>
			</li>
		</ul>
		
		<?php
	}
	
	public function wpsm_faq_rateus_meta_box_function(){
		?>
		<style>
		#faq_rateus{
			background-color: #E74B42;
			   text-align:center;
			}
			#faq_rateus .hndle , #faq_rateus .handlediv{
			display:none;
			}
			#faq_rateus h1{
			color:#fff;
			
			}
			 #faq_rateus h3 {
			color:#fff;
			font-size:15px;
			}
			#faq_rateus .button-hero{
			display:block;
			text-align:center;
			margin-bottom:15px;
			}
			.wpsm-rate-us{
			text-align:center;
			}
			.wpsm-rate-us span.dashicons {
				width: 40px;
				height: 40px;
				font-size:20px;
				color : #ffffff !important;
			}
			.wpsm-rate-us span.dashicons-star-filled:before {
				content: "\f155";
				font-size: 40px;
			}
			#faq_rateus .button-hero{
				    background: #fff;
					color: #000;
					box-shadow: none;
					text-shadow: none;
					font-weight: 900;
					font-size: 23px;
					border:1px solid #000;
				
			}
		</style>
		   <h1>Rate Us </h1>
			<h3>Show us some love, If you like our product then please give us some valuable feedback on wordpress</h3>
			<a href="https://wordpress.org/plugins/faq-responsive/" target="_blank" class="button button-primary button-hero ">RATE HERE</a>
			<a class="wpsm-rate-us" style=" text-decoration: none; height: 40px; width: 40px;" href="https://wordpress.org/plugins/faq-responsive/" target="_blank">
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
			</a>
		<?php 
	}
	
	public function wpsm_add_faq_setting_meta_box_function($post){
		require_once('settings.php');
	}
	
	public function add_faq_meta_box_save($PostID) {
		require('data-post/faq-save-data.php');
    }
	
	public function faq_settings_meta_box_save($PostID){
		require('data-post/faq-settings-save-data.php');
	}
	
	
}
global $wpsm_faq;
$wpsm_faq = wpsm_faq::forge();

 ?>