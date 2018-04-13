<?php

class rareRelated {
    
    private $default_taxonamy;
    private $rarerelated_taxonamy ;
    private $default_tags;

    function __construct() {
        $this->default_taxonamy       = 'rarerelated-defaulttag';
        $this->rarerelated_taxonamy   = 'rarerelated-tag';
    }

    /* Create plugin options added deafult options on install*/
    public function get_onevoice_rarerelated_options() {
        $onevoice_rarerelated_admin_options = array(
            'default_tags'  => array('resourcespatient-support','disease-awarenesssupport'),
            'post_types'    => array( 
                    array(
                        'name'=>'evidance-education',
                        'no_of_posts'=>2,
                        'icon_class'=>'icon-evidence-and-education',
                        'color' =>'',
                        'binder_class'=>'rare-evidence',
                        'item_text'=>'education & research'
                    )
            ),
            'rarecourage'       => array(
                'no_of_posts'=>2,
                'icon_class'    => 'icon-rarecourage',
                'item_text'     =>'SMART Social Wall',
                'color'         => '#870011',
                'binderclass'   => ''
                )
        );
        $onevoice_rarerelated_options = get_option("onevoice_rarerelated_admin_options");
        if ( !empty($onevoice_rarerelated_options) ) {
            foreach ( $onevoice_rarerelated_options as $key => $option ) {
                $onevoice_rarerelated_admin_options[$key] = $option;
            }
        }
        update_option("onevoice_rarerelated_admin_options", $onevoice_rarerelated_admin_options);
        return $onevoice_rarerelated_options;
    }

    // function for getting rarerelated taxonamy items
    public function get_rarerelated_taxonamy() {
        $taxonomies = array('rarerelated-tag');
        $args = array(
            'orderby'       =>'name',
            'order'         =>'ASC',
            'hierarchical'  =>true,
            'hide_empty'    =>false,
            'fields'        =>'all'
        );
        return get_terms($taxonomies, $args);
    }

    // function for fetching defualt tags from options.
    public function get_default_tags() {
        $options = $this->get_onevoice_rarerelated_options();
        $default_tags =  $options['default_tags'];
        return $default_tags;
    }

    // function for getting courage posts related to the tags provided.
    public function get_courage_post( $related_tag_terms , $limit = 2,$args = array(),$selectd_items = NULL ) {
        global $wpdb;
        if( $limit < 1 ) {
            return false;
        }

       //echo '<pre>'; var_dump($related_tag_terms);echo '</pre>';

        if( empty( array_filter( $related_tag_terms ) ) ) {
            $related_tag_terms = $this->get_default_tags();
        }

        if( isset( $args['activity_id'] ) ) {
            $query_notin = "AND activty.id NOT IN ('".$args['activity_id'] . "')";
        } else {
             $query_notin = '';
        }

        //echo '<pre>';var_dump($related_tag_terms);echo '</pre>';

        $courage_maping = '"'.implode('", "', $related_tag_terms) . '"'; //echo '<pre>';var_dump($courage_maping);echo '</pre>';
        $courageresult = '';
        if( $courage_maping ) {
            $query_post = 	"SELECT  activty.id,activty.user_id,activty.content  FROM  wp_bp_activity activty
            INNER JOIN onevoice_activity_tags_details tag_details  ON activty.id = tag_details.activity_id
            INNER JOIN onevoice_activity_tags tags  ON tags.tag_id = tag_details.tag_id
            WHERE tags.tag_name IN (".$courage_maping.") AND activty.item_id =1  AND activty.content IS NOT NULL {$query_notin} GROUP BY activty.id ORDER BY tags.tag_type DESC, RAND() LIMIT ".$limit;
            $courageresult = $wpdb->get_results( $query_post );  
            
            //echo '<pre>';var_dump($query_post);echo '</pre>';
            //echo '<pre>';var_dump($courageresult);echo '</pre>';
        }

        if( $courageresult ) {
            $return['taxonamy']         = $related_tag_terms;
            $return['courageresult']    = $courageresult;
           // echo '<pre>';var_dump( $return['courageresult'] );echo '</pre>';
            //echo '<pre>';var_dump( $return );echo '</pre>';
            return $return;
        }  else {
            
             /* $parents = $this->get_parents_of_terms( $related_tag_terms);// echo '<pre>';var_dump($parents);echo '</pre>';
             if( ! empty( array_filter( $parents ) ) ) {
                 //$args = array();
                return $this->get_courage_post( $parents,  $limit, $args, $selectd_items );
            }*/

            $return['taxonamy']         = $related_tag_terms;
            $return['courageresult']    = NULL;
            return $return;
           
        }
        
    }

    // Function for searching courage posts by content. (planned to implement using NLP)
    public function get_content_search_courage( $related_tag_terms , $limit = 2 ){
        return null;
    }

    // get wp posts from given post types that related to given tags.
    public function get_related_posts( $related_tag_terms, $post_type, $limit = 5,$args=array() ) {
        
        //echo '<pre>';var_dump( $related_tag_terms);echo '</pre>';
       
        
        if( empty( array_filter( $related_tag_terms ) ) ) {
            $related_tag_terms = $this->get_default_tags();

            if( empty( array_filter( $related_tag_terms ) ) ) {
                return null;
            }
        }
        
        $postargs = array(
            'post_type' =>  $post_type,
            'tax_query' => array(
                array(
                    'taxonomy' => $this->rarerelated_taxonamy,
                    'field'    => 'slug',
                    'terms'    => $related_tag_terms
                ),
            ),
            'posts_per_page'   => $limit,
            'orderby'           => 'rand'  
        );
         //echo '<pre>';var_dump( $args );echo '</pre>';
         if( isset($args['post_displayed'])) {
            $postargs['post__not_in'] = $args['post_displayed'];
        }

        //echo '<pre>';var_dump( $postargs);echo '</pre>';
        $posts = new WP_Query( $postargs );
         //echo '<pre>';var_dump( $posts->request);echo '</pre>';
         //echo '<pre>';var_dump( $posts);echo '</pre>';

        
        $return['taxonamy'] = $related_tag_terms;
        $return['posts']    = $posts;
        return $return;

        // Removed reccusrive call due to special case for rareCourage.
       /* if( $posts->post_count > 0 ){
            $return['taxonamy'] = $related_tag_terms;
            $return['posts']    = $posts;
            return $return;
        } else {
            $parents = $this->get_parents_of_terms( $related_tag_terms);
             if( ! empty( array_filter( $parents ) ) ) {
                return  $this->get_related_posts( $parents );
            }
            return;
        }*/
       
    }

    // function for displaying rare related content.

    // for rareC?ourage section
    /**** 
    @param section should be rarecourage
    @param related_tag_terms used for system tags
    @param $args['user_tags'] used for the user tags
    **/
    public function display_related_posts( $related_tag_terms , $section="inner_post", $args ) { 
        $tags_shared = $related_tag_terms;
        //echo '<pre>';var_dump($related_tag_terms);echo '</pre>';
        //echo '<pre>';var_dump($section);echo '</pre>';
        //echo '<pre>';var_dump($args);echo '</pre>';

        $options            = $this->get_onevoice_rarerelated_options();
        $post_types         = $options['post_types'];
        $post_type_names    = array_column($options['post_types'], 'name');
        $no_of_posts        = array_column($post_types, 'no_of_posts', 'name');
        $icon_class         = array_column($post_types, 'icon_class', 'name');
        $color_name         = array_column($post_types, 'color', 'name');
        $binderclass_name   = array_column($post_types, 'binder_class', 'name');
        $icon_text_val      = array_column($post_types, 'item_text', 'name');
        $no_of_post_displayed = 0;

        
        echo '<ul class="rare-list"> ';
        $rareCourage_settings = $options['rarecourage'];
        //var_dump($options['rarecourage']);
        if( is_user_logged_in() && $rareCourage_settings ) {
            
            $limit = $rareCourage_settings["no_of_posts"];
            if( !$limit ) {
                $limit =2;
            }
            $user_tag_flag = 0;
            $user_tag_flag_curate = 0;
            if( empty( array_filter( $related_tag_terms ) ) && $section='rarecourage'  ) {
                     $related_tag_terms = $args['user_tags'];
                     $user_tag_flag = 1;
                     $user_tag_flag_curate = 1;
            }
       
            $couragepost_items = $this->get_courage_post( $related_tag_terms, $limit,$args ); //var_dump( $couragepost);
            
            $couragepost = $couragepost_items['courageresult'];
            $no_of_post_displayed =  count($couragepost);

            //echo '<pre>';var_dump($couragepost);echo '</pre>';
            //echo '<pre>';var_dump($no_of_post_displayed);echo '</pre>';

           // Post using user tags for rareCourage related panel.
            if( $no_of_post_displayed < $limit &&   ! empty( array_filter( $args['user_tags'] ) ) && $section='rarecourage' && ! $user_tag_flag ) { 
                $couragepost_items_user_tag = $this->get_courage_post( $args['user_tags'] , $limit - $no_of_post_displayed, $args ); //var_dump( $couragepost);
                //echo '<pre>';var_dump( $couragepost_items_user_tag);echo '</pre>';
                $post_items = $couragepost_items_user_tag['courageresult'];

               // var_dump($post_items);
                
                $couragepost    = (array)$couragepost;
                $post_items     = (array)$post_items;
                
                $arr_merge = array_merge ($couragepost, $post_items );
                $couragepost    =  $this->arrayUnique($arr_merge);
                $no_of_post_displayed = count( $couragepost );
            }

           // echo '<pre>';var_dump($no_of_post_displayed);echo '</pre>';
            
            // Already added with the query
           /* if( $section='rarecourage' ) {
                //remove the same post if rareCouragepost
                if(is_array($couragepost )) {
                    foreach($couragepost as $subKey => $subArray){
                        //var_dump($subArray);
                        if( $subArray->id == $args['activity_id'] ){
                            unset($couragepost[$subKey]);
                        }
                    }
                    $no_of_post_displayed = count( $couragepost );
                }
                
            }
            echo '<pre>';var_dump($no_of_post_displayed);echo '</pre>';
            */
            
            while( $no_of_post_displayed < $limit ) { 
                $parent_taxonamy = $this->get_parents_of_terms( $related_tag_terms); 
               // echo '<pre>';var_dump($parent_taxonamy); echo '</pre>';
               // break;
                if( ! empty( array_filter( $parent_taxonamy )  ) ) {
                    $couragepost_items_parent = $this->get_courage_post( $parent_taxonamy,  $limit - $no_of_post_displayed, $args);
                    $post_items     =  $couragepost_items_parent['couragepost'];

                  

                    if( ! empty($post_items )) {

                        //echo '<pre>'; var_dump($couragepost); echo '</pre>';
                       //echo '<pre>'; var_dump($post_items); echo '</pre>';
                        $couragepost = (array)$couragepost;
                        $post_items = (array)$post_items;
                        $arr_merge = array_merge ($couragepost, $post_items );
                        
                        
                        $couragepost = $this->arrayUnique($arr_merge);
                        $no_of_post_displayed = count( $couragepost );
                    }  else {
                        // checking default tags for remaining posts.
                        $related_tag_terms = $this->get_default_tags();
                        $couragepost_default_tags = $this->get_courage_post( $related_tag_terms,  $limit - $no_of_post_displayed,$args );
                        
                        //echo '<pre>'; var_dump($couragepost_default_tags); echo '</pre>';
                        
                        $post_items     =  $couragepost_default_tags['courageresult'];
                        if( ! empty($post_items )) {

                           // echo '<pre>'; var_dump($couragepost); echo '</pre>';
                            //echo '<pre>'; var_dump($post_items); echo '</pre>';

                            $couragepost = (array)$couragepost;
                            $post_items = (array)$post_items;
                            $arr_merge = array_merge ($couragepost, $post_items );
                            
                            
                            $couragepost = $this->arrayUnique($arr_merge);
                            $no_of_post_displayed = count( $couragepost );
                            
                        }
                        break;
                    }
                    
                } else {
                     // checking default tags for remaining posts.
                    $related_tag_terms = $this->get_default_tags();
                    $couragepost_default_tags = $this->get_courage_post( $related_tag_terms,  $limit - $no_of_post_displayed,$args );
                    $post_items     =  $couragepost_default_tags['courageresult'];

                    //echo '<pre>'; var_dump($couragepost); echo '</pre>';
                    //echo '<pre>'; var_dump($post_items); echo '</pre>';


                    if( ! empty($post_items )) {
                        $couragepost = (array)$couragepost;
                        $post_items = (array)$post_items;

                        
                        $arr_merge = array_merge ($couragepost, $post_items );
                        
                        
                        $couragepost = $this->arrayUnique($arr_merge);
                        $no_of_post_displayed = count( $couragepost );
                        
                    }
                    break;
      
                }
            }

           // echo '<pre>'; var_dump($couragepost); echo '</pre>';

            $icon   = 'icon-rarecourage';
            $color  = '#870011;';
            $binderclass = '';
            $icon_text = 'SMART Social Wall';

            if( isset( $rareCourage_settings ) ) {
                $icon           = $rareCourage_settings['icon_class'];
                $color          = $rareCourage_settings['color'];
                $binderclass    = $rareCourage_settings['binder_class'];
                $icon_text      = $rareCourage_settings['item_text'];
            }

            foreach( $couragepost as $courage ) {
                $post_url = bp_activity_get_permalink( $courage->id ); 
                ?>
                <li>
                    <a href = "<?php echo $post_url; ?>">
                        <div class="rel-icon">
                            <span style="<?php echo $color; ?>"   class="<?php echo $icon; ?>"></span>
                            <div class="rel-text" style="color:<?php echo  esc_attr( $color ); ?> "> <?php echo esc_attr( $icon_text ); ?> </div>
                        </div>
                    </a>
                    <span class="rel-title">
                        <?php $user = get_user_by('id', $courage->user_id); ?>
                        <a class = "relatedclick" href=" <?php echo $post_url ?> "> 
                            <?php 
                            if ( strlen( $user->display_name." posted in SMART Social Wall" ) > 116 ) {
                                echo substr( $user->display_name." posted in SMART Social Wall..",0,116 ) . '...';
                            } else {
                                echo  $user->display_name." posted in SMART Social Wall" ; 
                            } 
                            ?>
                            
                        </a>
                    
                    </span>

                    <?php

                        $content = stripslashes( do_shortcode( bp_create_excerpt(  wp_filter_nohtml_kses( stripslashes( $courage->content)  ) ,150, array( 'ending' => __( '...', 'buddypress' ) ) ) ));
                        echo '<div class="rel-source">'.$content.'</div>';
                    ?>
                </li>
            <?php
            }

        }

        if ( ! $post_types ) {
            $post_type_names = array('evidance-education','people','place','media-social','protocol','rarehub','research-grant','video-visual','news-meeting');// need add an option for choosing post type
        }

        // end displaying rareRelated tags
        
        $total = 7;

        //  checking for WP posts
        $limit = $total - $no_of_post_displayed;

        

        $related_tag_terms = $tags_shared;
        if( empty( array_filter( $related_tag_terms ) ) && $section='rarecourage'  ) {
                    $related_tag_terms = $args['user_tags'];
                    $user_tag_flag = 1;
                    $user_tag_flag_curate = 1;
        }
        
        $no_of_post_displayed = 0;
        $icon   = 'icon-social-media';
        $color  = '#870011;';
        $binderclass = '';
        $icon_text   = 'social & media';
        $displayed_posts = array();
        $post_types_exhausted = array();
        $post_displayed = array();
        
        
        $default_tags_flag = 0;
        $no_of_post_returned = 0;

        $loop = 1;
        while( $no_of_post_displayed < $limit ) {
         // echo '-------> '.$loop++.' <------ <br/>';
          /*if( $loop > 50 ) {
               break;
           }*/
           // echo '<pre> related_tag_terms:  '; var_dump($related_tag_terms); echo '</pre>';

            //$limit = $limit - $no_of_post_displayed;
            //$no_of_post_displayed = 0;
            if( ! empty( array_filter( $related_tag_terms ) ) ) {

                $args['post_displayed'] = $post_displayed; 
                $related_posts = $this->get_related_posts( $related_tag_terms,$post_type_names,  $limit - $no_of_post_displayed, $args ); //var_dump( $related_posts);
                $result = $related_posts['posts'];
                
                
                
                $no_of_post_returned = $result->post_count;

                //echo '<pre> no_of_post_returned: '; var_dump($no_of_post_returned); echo '</pre>';
                //echo '<pre>'; var_dump($result); echo '</pre>';

                if ( $result->have_posts() ) {
                    while ( $result->have_posts() ) {
                        $result->the_post();

                        $post_type      = get_post_type();

                        //echo '<pre>'; var_dump($post_type); echo '</pre>';

                        if(  isset( $displayed_posts[ $post_type  ] ) && !empty( $displayed_posts[ $post_type  ]  ) ) {
                            $displayed_posts[ $post_type  ]  +=  1;
                        } else {
                            $displayed_posts[ $post_type  ]  = 1;
                        }
                        
                        //echo '<pre>'; var_dump($displayed_posts); echo '</pre>'; 

                        if(  ( isset( $no_of_posts[ $post_type ] ) && ! empty( $no_of_posts[ $post_type ]) ) && ( $displayed_posts[ $post_type  ] > $no_of_posts[ $post_type ] ) ) {
                            //$displayed_posts[ $post_type  ] = $displayed_posts[ $post_type  ] - 1;
                            array_push($post_types_exhausted, $post_type);
                            continue;
                        }

                        
                        //echo '<pre>'; var_dump($post_type); echo '</pre>'; 

                        $no_of_post_displayed ++;

                        $icon           = ( $icon_class[$post_type] )? $icon_class[$post_type]  : 'icon-social-media';
                        $color          = ( $color_name[$post_type] )? $color_name[$post_type]  : '#870011';
                        $binderclass    = ( $binderclass_name[$post_type] )? $binderclass_name[$post_type]  : '';
                        $icon_text      = ( $icon_text_val[$post_type] )? $icon_text_val[$post_type]  : 'social & media';
                        array_push( $post_displayed, get_the_ID() );

                        ?>
                    
                    <li>
                        <?php //var_dump($post_type); ?>
                    <?php  $post_url= get_the_permalink(); ?>
                        <a href = "<?php echo $post_url; ?>">
                            <div class="rel-icon">
                                <span style="color:<?php echo $color; ?>"   class="<?php echo $icon; ?>"></span>
                                <div class="rel-text" style="color:<?php echo  esc_attr( $color ); ?> "> <?php echo esc_attr( $icon_text ); ?> </div>
                            </div>
                        </a>
                        <span class="rel-title">
                            <?php $user = get_user_by('id', get_the_author() ); ?>
                        <div class="rel-title">
                                <a  class="social_link relatedclick" id="<?php echo esc_attr( get_the_ID() ); ?>" href="<?php echo $post_url; ?>">
                                    <?php
                                    
                                    if ( strlen( get_the_title() ) > 116 ) {
                                        echo esc_attr( substr( get_the_title() ,0,116 ) ) . '...';
                                    } else { 
                                        echo  esc_attr( get_the_title() ); 
                                    } ?>
                                </a>

                                <div class="rel-source">
                                    <?php echo esc_attr( substr( strip_tags( get_the_content() ),0,40 ) ).'...'; ?> 
                                </div>
                            </div>
                        
                        </span>
                    </li>
                <?php
                    }
                    
                //var_dump($post_types_exhausted);
                    wp_reset_postdata();
                }

            }
            

            if( $no_of_post_returned >= $limit - $no_of_post_displayed ) {
                //echo '<pre>'; var_dump($post_types_exhausted); echo '</pre>';

                $post_type_names = array_diff($post_type_names,$post_types_exhausted );

               // echo '<pre> post_type_names:  '; var_dump($post_type_names); echo '</pre>';  
                
                if( empty( $post_type_names ) ) {
                    break;
                }

            } else {
                if( $args['user_tags'] && $section = 'rarecourage' && !$user_tag_flag_curate ) {
                     $related_tag_terms =$args['user_tags'];
                     $user_tag_flag_curate = 1;
               
                } else{
                    $related_tag_terms = $this->get_parents_of_terms( $related_tag_terms);
                    if( empty($related_tag_terms) &&  !empty( array_filter( $args['user_tags'] ) ) && $section = 'rarecourage' && $user_tag_flag_curate ) {
                        $related_tag_terms = $this->get_parents_of_terms( $args['user_tags'] );
                    }
                    if( empty( array_filter( $related_tag_terms ) ) ) {
                        
                        if( ! $default_tags_flag ) {
                            $related_tag_terms = $this->get_default_tags();
                            $default_tags_flag = 1;
                        } else {
                            break;
                        }
                    }

                }
            } 
        

        //echo '<pre> limit: '; var_dump($limit); echo '</pre>';
       // echo '<pre> no_of_post_displayed: '; var_dump($no_of_post_displayed); echo '</pre>';
            
        }  
        echo '</ul>';

       
    }

    public function get_parents_of_terms( $related_tag_terms ) {
        $parents = array();
        if( ! empty( array_filter( $related_tag_terms ) ) ) {
            foreach( $related_tag_terms as $related_tag_term ) {
                $tax = get_term_by( 'slug', $related_tag_term, 'rarerelated-tag' );
                if($tax->parent ){
                // array_push($parents,$tax->parent );
                    $args = array('taxonomy' => 'rarerelated-tag','orderby'=>'name','order'=>'ASC','hierarchical','fields'=>'slugs','include'=>$tax->parent);
                    $terms = get_terms($args);
                    //echo '<pre>';var_dump( $terms[0] ); echo '</pre>';
                    array_push( $parents, $terms[0] );
                }
            }
        }
        //echo '<pre>';var_dump( $parents ); echo '</pre>';
        return array_values($parents) ;
    }


    public function arrayUnique($array, $preserveKeys = false)
    {
        // Unique Array for return
        $arrayRewrite = array();
        // Array with the md5 hashes
        $arrayHashes = array();
        foreach($array as $key => $item) {
            // Serialize the current element and create a md5 hash
            $hash = md5(serialize($item));
            // If the md5 didn't come up yet, add the element to
            // to arrayRewrite, otherwise drop it
            if (!isset($arrayHashes[$hash])) {
                // Save the current element hash
                $arrayHashes[$hash] = $hash;
                // Add element to the unique Array
                if ($preserveKeys) {
                    $arrayRewrite[$key] = $item;
                } else {
                    $arrayRewrite[] = $item;
                }
            }
        }
        return $arrayRewrite;
    }

   
}