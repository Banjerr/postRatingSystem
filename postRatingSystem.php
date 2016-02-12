<?php
/**
* Plugin Name: Post Rating System
* Plugin URI: http://countryfriedcoders.me
* Description: Gives ability to rate posts
* Version: 1.0
* Author: Ben Redden
* Author URI: http://benjaminredden.we.bs
* License: GPL2.0
*/

// add actions
add_action( 'wp_enqueue_scripts', 'enqueuePostRatingScripts' );
add_action( 'wp_ajax_nopriv_post_up', 'post_rateUp' );
add_action( 'wp_ajax_post_up', 'post_rateUp' );
add_action( 'wp_ajax_nopriv_post_down', 'post_rateDown' );
add_action( 'wp_ajax_post_down', 'post_rateDown' );

// enqueue the js/styles
function enqueuePostRatingScripts() {
  wp_enqueue_style( 'font-awesome', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css' );
  wp_enqueue_style( 'post-rate-styles', plugin_dir_url( __FILE__ ) . 'styles/style.css' );
  wp_enqueue_script( 'jquery' );
  wp_enqueue_script( 'rate-post', plugin_dir_url( __FILE__ ) . 'js/ratingFunctions.js', array( 'jquery' ), '1.0.0', true );
  // localize var for the admin-ajax url and the nonce
  wp_localize_script( 'rate-post', 'ajax_var', array(
    'url' => admin_url( 'admin-ajax.php' ),
    'nonce' => wp_create_nonce( 'ajax-nonce' )
  ) );
}

// when a post is liked
function post_rateDown()
{
    // Check for nonce security
    $nonce = $_POST['nonce'];

    // if they aint got a nonce, send em away
    if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) )
    {
        wp_die( 'Busted!'); // YOU SHALL NOT PASS
    }

    // if a post is rated
    if( isset( $_POST['post_id'] ) )
    {
        // Retrieve user/post id
        $userID = get_current_user_id();
        $post_id = $_POST['post_id'];

        // Get voters for the current post
        $userVotes = json_decode(get_post_meta( $post_id, "votes_uservotes", true));

        // Get down vote count for the current post
        $meta_down = get_post_meta( $post_id, "votes_novotes", true );

        // get up vote count for current post
        $meta_up = get_post_meta( $post_id, 'votes_yesvotes', true);

        // if $meta_up is empty
        if( empty($meta_up) ) {
            // set it to 0
            update_post_meta( $post_id, "votes_yesvotes", '0' );
        }

        // if user has not already voted
        if( !hasAlreadyVoted( $post_id ) )
        {
            // push user id into userVotes and increase votes count
            $userVotes[] = $userID;
            update_post_meta($post_id, 'votes_uservotes', json_encode($userVotes));
            update_post_meta( $post_id, "votes_novotes", ++$meta_down );

            // total the up/down votes together
            $updatedMeta_down = get_post_meta( $post_id, "votes_novotes", true );
            $meta_total = $updatedMeta_down + $meta_up;

            // Update the total votes
            update_post_meta( $post_id, "votes_totalvotes", $meta_total );

            // calculate that stuff
            calculatePercentage( $meta_up, $meta_total, $post_id );
        }
        else // if user has already voted
        {
            echo "already voted";
        }
    }
    wp_die();
}

// when a post is liked
function post_rateUp()
{
    // Check for nonce security
    $nonce = $_POST['nonce'];

    // if they aint got a nonce, send em away
    if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) )
    {
        wp_die( 'Busted!'); // YOU SHALL NOT PASS
    }

    // if a post is rated
    if( isset( $_POST['post_id'] ) )
    {
        // Retrieve user/post id
        $userID = get_current_user_id();
        $post_id = $_POST['post_id'];

        // Get voters'IPs for the current post
        $userVotes = json_decode(get_post_meta( $post_id, "votes_uservotes", true ));

        // Get yes votes count for the current post
        $meta_up = get_post_meta( $post_id, "votes_yesvotes", true );

        // if $meta_up is empty
        if( empty($meta_up) ) {
            // set it to 0
            update_post_meta( $post_id, "votes_yesvotes", '0' );
        }

        // Get no votes count for current post
        $meta_down = get_post_meta( $post_id, "votes_novotes", true );

        // if user has not already voted
        if( !hasAlreadyVoted( $post_id ) )
        {
            // push user id into userVotes and increase votes count
            $userVotes[] = $userID;
            update_post_meta($post_id, 'votes_uservotes', json_encode($userVotes));
            update_post_meta( $post_id, "votes_yesvotes", ++$meta_up );

            // total the up/down votes together
            $updatedMeta_up = get_post_meta( $post_id, "votes_yesvotes", true );
            $meta_total = $updatedMeta_up + $meta_down;

            // Update the total votes
            update_post_meta( $post_id, "votes_totalvotes", $meta_total );

            // calculate that stuff
            calculatePercentage( $meta_up, $meta_total, $post_id );
        }
        else // if user has already voted
        {
            echo "already voted";
        }
    }
    wp_die();
}

// calculate the percentage of votes
function calculatePercentage( $meta_up, $meta_total, $post_id )
{
    if( $meta_up && $meta_total ) // if $meta_up and $meta_total already exist
    {
      // calculate the percentage of up votes
      $meta_percentage = round( $meta_up / $meta_total * 100 );

      update_post_meta($post_id, 'votes_percentage', $meta_percentage);

        // if its an AJAX call
      if (  defined( 'DOING_AJAX' ) && DOING_AJAX )
      {
        // Display percentage
        echo $meta_percentage . '%';
      } else // if its not an AJAX call
      {
        // Display percentage
        return $meta_percentage . '%';
      }
    } else // if $meta_up and $meta_total do no exist
    {
        // if its an ajax call
        if (  defined( 'DOING_AJAX' ) && DOING_AJAX )
        {
            echo '0%';
        }
        else // if its not an AJAX call
        {
            return "0%";
        }
    }
}

// if user has already voted
function hasAlreadyVoted($post_id)
{
    // Get list of voters for the current post
    $userVotes = json_decode(get_post_meta( $post_id, "votes_uservotes", true ));

    // if userVotes is an array
    if( !is_array( $userVotes ) )
    {
        $userVotes = array();
    }

    // Retrieve user id
    $userID = get_current_user_id();

    // If user has already voted
    if( in_array( $userID, array_values( $userVotes ) ) )
    {
      return true;
    }
    else
    {
      return false;
    }
}

// generate html markup
function generateRatingHTML($post_id)
{
    // get meta stuff
    $meta_up = get_post_meta( $post_id, "votes_yesvotes", true );

    $meta_total = get_post_meta( $post_id, "votes_totalvotes", true);

    // the html to write
    $output = '<div class="post-rate">';

    // what they'll see if they've already voted
    if( hasAlreadyVoted( $post_id ) )
    {
        $output .= '<div class="ratePercentage"><div class="percentageChart" style="width:'. calculatePercentage($meta_up, $meta_total)
         .';"></div><h2>' . calculatePercentage($meta_up, $meta_total) . '
</h2></div><!--.ratePercentage-->';
        $output .= '<span class="voteUp alreadyVoted" data-post_id="'.$post_id.'"></span>';
        $output .= '<span class="voteDown alreadyVoted" data-post_id="'.$post_id.'"></span>';
        $output .= '</div><!--.post-rate-->';
    }
    else // if they haven't voted yet
    {
        $output .= '<div class="ratePercentage"><div class="percentageChart" style="width:'. calculatePercentage($meta_up, $meta_total) .';"></div><h2>' . calculatePercentage($meta_up, $meta_total) . '</h2></div><!--.ratePercentage-->';
        $output .= '<a class="voteUp" href="#" data-post_id="'.$post_id.'"></a>';
        $output .= '<a class="voteDown" href="#" data-post_id="'.$post_id.'"></a>';
        $output .= '</div><!--.post-rate-->';
    }
    return $output;
}

/**
 *
 * USAGE - echo generateRatingHTML(get_the_ID());
 *
 **/
