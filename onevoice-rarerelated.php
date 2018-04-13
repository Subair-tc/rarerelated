<?php
/*
 * Plugin Name:Onevoice rarerelated
 * Description: Plugin for displaying rare related items.
 * Version: 1.0
 * Author: subair TC
 * Author URI: 
 */



/* Set constant path to the plugin directory. */
define( 'ONEVOICE_RARERELATED_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

define( 'ONEVOICE_RARERELATED_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
/* Set the constant path to the plugin's includes directory. */
define( 'ONEVOICE_RARERELATED_PLUGIN_INC', ONEVOICE_RARERELATED_PLUGIN_PATH . trailingslashit( 'inc' ), true );


add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'onevoice_rarerelated_add_action_links' );

function onevoice_rarerelated_add_action_links ( $links ) {
	$mylinks = array(
	'<a href="' . admin_url( 'admin.php?page=onevoice-rarerelated.php' ) . '">Settings</a>',
	);
	return array_merge( $links, $mylinks );
}


/*
*	Function to Enqueue required Styles.
*/
function add_onevoice_rarerelated_style() {
	
	wp_register_style( 'rarerelated-custom-css', plugins_url( '/css/custom.css', __FILE__ ) );
	wp_enqueue_style( 'rarerelated-custom-css' );
	
	wp_register_script( 'rarerelated-custom-js', plugins_url( '/js/custom.js', __FILE__ ), true );
	wp_enqueue_script( 'rarerelated-custom-js' );
	
	wp_localize_script('rarerelated-custom-js', 'Ajax', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
	));
}
add_action( 'admin_enqueue_scripts', 'add_onevoice_rarerelated_style' );



add_action('admin_menu', 'onevoice_rarerelated_options_page');

function onevoice_rarerelated_options_page() {
	if ( function_exists('add_options_page') ) {
		add_options_page('Onevoice Rarerelated', 'Onevoice Rarerelated', 'manage_options', basename(__FILE__), 'onevoice_print_rarerelated_options');
	}
}

function onevoice_print_rarerelated_options() {
   

    if( isset($_POST['update_rarerelated_options'])) {
        check_admin_referer('rarerelated_update-options');

        if( isset($_POST['default_tag']) ) {
            $default_tags = $_POST['default_tag'];
        }
         if( isset($_POST['post_types_related']) ) {
            $realated_post_types = array();
            $rarecourage_settings = array();
            $post_types = $_POST['post_types_related'];
            foreach( $post_types as $post_type ) {
                $post_type_data = array(
                    'name'          =>  $post_type,
                    'no_of_posts'   =>  $_POST['no_of_posts_'.$post_type],
                    'icon_class'    =>  $_POST['icon_class_'.$post_type],
                    'color'         =>  $_POST['text_color_'.$post_type],
                    'binder_class'  =>  $_POST['binder_class_'.$post_type],
                    'item_text'     =>  $_POST['icon_text_'.$post_type]
                );

                if( $post_type == 'rarecourage') {
                   // array_push($rarecourage_settings,$post_type_data );
                   $rarecourage_settings = $post_type_data;
                } else {
                    array_push($realated_post_types,$post_type_data );
                }
                
            }

        }

        $onevoice_rarerelated_admin_options = array(
            'default_tags'  => $default_tags,
            'post_types'    => $realated_post_types,
            'rarecourage'   => $rarecourage_settings
        );
        //echo '<pre>';  var_dump($onevoice_rarerelated_admin_options);      echo '</pre>';
        update_option("onevoice_rarerelated_admin_options", $onevoice_rarerelated_admin_options);

    }


    $rarerelated = new rareRelated();
    $options            = $rarerelated->get_onevoice_rarerelated_options();
    //echo '<pre>';  var_dump($options);      echo '</pre>';
    $post_type_name     = array_column($options['post_types'], 'name');
    $no_of_posts        = array_column($options['post_types'], 'no_of_posts', 'name');
    $icon_class         = array_column($options['post_types'], 'icon_class', 'name');
    $color              = array_column($options['post_types'], 'color', 'name');
    $binder_class       = array_column($options['post_types'], 'binder_class', 'name');
    $icon_text          = array_column($options['post_types'], 'item_text', 'name');

    $courage_settings   = $options['rarecourage'];
    // create an option for choosing default post types
    $tags = $rarerelated->get_rarerelated_taxonamy();
    // Create an option for choosing the post types.
    $args = array('public'=>true,'_builtin'=>false,'show_ui'=>true);
    $post_types = get_post_types( $args,'names' ); 
    ?>

    <div class="rarerelated_option">

    <?php if( isset($_POST['update_rarerelated_options'])) {  ?>
        <div class="success_block alert alert-success alert-dismissable">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            rareRelated plugin options updated successfully.
        </div>
    <?php } ?>

    <form name="rarerelated_option_form" method="post" action="<?php echo esc_attr($_SERVER["REQUEST_URI"]); ?>">
        <?php
			if ( function_exists('wp_nonce_field') ) {
				wp_nonce_field('rarerelated_update-options');
			}	
		?>
        <div class="row">
            <div class="col-sm-6">
                <h2>Please choose default tags for rareRelated</h2>
                <div class="form-group">
                    <input type="text" id="searchtag-input" onkeyup="getRareRelated( '#searchtag-input','#default-tag-list')" placeholder="Search for tags.." title="Search Tags">
                </div>
                <?php if( $options['default_tags'] ) { ?>
                 <p class="tags-added alert alert-info">
                    <span>Tags added: </span>
                    <?php echo  implode(', ',$options['default_tags']) ?>
                </p>
                <?php } ?>
                <div class="default-tag-list" id="default-tag-list">
                    <?php //echo '<pre>';var_dump($options['default_tags']);  echo '</pre>'; ?>
                    <?php
                    foreach( $tags as $tag ) {
                       // echo '<pre>';var_dump($options['default_tags']);  echo '</pre>';
                       //echo '<pre>';var_dump($tag);  echo '</pre>';
                        if( in_array( $tag->slug, $options['default_tags']  ) ) {
                                $checked = 'checked';
                            } else {
                                $checked = ' ';
                            }
                        echo '<div class="form-group">';
                        echo '<input type="checkbox" name="default_tag[]" value="'.$tag->slug.'" '. $checked.' />'.$tag->name;
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <div class="col-sm-6">
            <h2>Please choose Post types required</h2>

            <div class="form-group">
                <input type="text" id="searchpost_type-input" onkeyup="getRareRelated( '#searchpost_type-input','#posttypes-list')" placeholder="Search for Potst type.." title="Search Post Types">
            </div>
            <div class="posttypes-list"  id="posttypes-list">

                    <?php
                        $post_type = ( $courage_settings['name'] )? $courage_settings['name'] : 'rarecourage';
                        if(  $courage_settings ) {
                            $checked = 'checked';
                            $class ='active';
                        } else {
                            $checked = ' ';
                            $class =' ';
                        }

                    ?>

                    <div class="form-group">
                        <input type="checkbox" class="post_types_related" name="post_types_related[]" value="<?php echo trim($post_type); ?>" <?php echo  $checked; ?>/><?php echo $post_type; ?>
                    </div>

                    <div class="post_type_details <?php echo $class; ?>" >
                        <div class="form-group">
                            <label>No of posts</label>
                            <input type="number" name="no_of_posts_<?php echo $post_type; ?>" value="<?php echo $courage_settings['no_of_posts'] ?>" />
                        </div>
                        <div class="form-group">
                            <label>Icon Class</label>
                            <input type="text" name="icon_class_<?php echo $post_type; ?>" value="<?php echo $courage_settings['icon_class'] ?>"/>
                        </div>
                        <div class="form-group">
                            <label>Icon Text</label>
                            <input type="text" name="icon_text_<?php echo $post_type; ?>" value="<?php echo $courage_settings['item_text'] ?>"/>
                        </div>
                        <div class="form-group">
                            <label>Text Color</label>
                            <input type="text" name="text_color_<?php echo $post_type; ?>" value="<?php echo $courage_settings['color'] ?>"/>
                        </div>
                        <div class="form-group">
                            <label>Binder Class</label>
                            <input type="text" name="binder_class_<?php echo $post_type; ?>" value="<?php echo $courage_settings['binder_class'] ?>"/>
                        </div>
                    </div>

                    <?php
                        foreach( $post_types as $post_type ) { 
                            if( in_array( $post_type, $post_type_name) ) {
                                $checked = 'checked';
                                $class ='active';
                            } else {
                                $checked = ' ';
                                $class =' ';
                            }
                        ?>
                            <div class="form-group">
                                <input type="checkbox" class="post_types_related" name="post_types_related[]" value="<?php echo trim($post_type); ?>" <?php echo  $checked; ?>/><?php echo $post_type; ?>
                            </div>

                            <div class="post_type_details <?php echo $class; ?>" >
                                <div class="form-group">
                                    <label>No of posts</label>
                                    <input type="number" name="no_of_posts_<?php echo $post_type; ?>" value="<?php echo $no_of_posts[$post_type] ?>" />
                                </div>
                                <div class="form-group">
                                    <label>Icon Class</label>
                                    <input type="text" name="icon_class_<?php echo $post_type; ?>" value="<?php echo $icon_class[$post_type] ?>"/>
                                </div>
                                <div class="form-group">
                                    <label>Icon Text</label>
                                    <input type="text" name="icon_text_<?php echo $post_type; ?>" value="<?php echo $icon_text[$post_type] ?>"/>
                                </div>
                                <div class="form-group">
                                    <label>Text Color</label>
                                    <input type="text" name="text_color_<?php echo $post_type; ?>" value="<?php echo $color[$post_type] ?>"/>
                                </div>
                                <div class="form-group">
                                    <label>Binder Class</label>
                                    <input type="text" name="binder_class_<?php echo $post_type; ?>" value="<?php echo $binder_class[$post_type] ?>"/>
                                </div>
                            </div>
                                

                            <?php
                        }
                    ?>
            </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4 col-sm-offset-8">
                <div class="form-group">
                    <input type="submit" name="update_rarerelated_options"/>
                </div>
            </div>
        </div>
    </form>
    </div>

    <?php
}


// Add Shortcode
function or_display_rarerelated( $atts ) {

    //var_dump($atts['related_tag_terms']);

    //var_dump(explode( ',',$atts['related_tag_terms']));

     $rp = new rareRelated();
    $args = array();
    if( $atts['section'] == 'rarecourage') {
        $args = array(
            'user_tags' => explode( ',',$atts['user_tags_terms']),
            'activity_id'   => $atts['activty_id']
        );
         $rp->display_related_posts( explode( ',',$atts['system_tags_terms']), 'rarecourage', $args );
    } else {
        $rp->display_related_posts( explode( ',',$atts['related_tag_terms']),'inner_post',$args );
    }
}
add_shortcode( 'display_raerelated_posts', 'or_display_rarerelated' );



//including rare related class
include_once( ONEVOICE_RARERELATED_PLUGIN_INC . 'class-rarerelated.php' );


