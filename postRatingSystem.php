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

// require CPT helper class
require_once( plugin_dir_path( __FILE__ ) . 'brCPTClass.php' );

// add actions
add_action( 'wp_enqueue_scripts', 'enqueuePostRatingScripts' );
add_action('wp_ajax_nopriv_post-like', 'post_like');
add_action('wp_ajax_post-like', 'post_like');

// set up the post type we want the rating system for
$videoPosts = new BR_Post_Type( 'Video Post' );

// taxonomy for videoPosts
$videoPosts->add_taxonomy( 'Video Subject' );

// add custom fields for yes/no/userVotes
$videoPosts->add_meta_box( 'Votes', array(
  'yesVotes' => 'text',
  'totalVotes' => 'text',
  'userVotes' => 'text'
) );

// enqueue the js/styles
function enqueuePostRatingScripts() {
  wp_enqueue_script( 'jquery' );
  wp_enqueue_script( 'like-post', plugin_dir_url( __FILE__ ) . 'js/ratingFunctions.js', array( 'jquery' ), '1.0.0', true );
  wp_localize_script( 'like-post', 'ajax_var', array(
    'url' => admin_url( 'admin-ajax.php' ),
    'nonce' => wp_create_nonce( 'ajax-nonce' )
  ));
}

// when a post is liked
function post_like()
{
    // Check for nonce security
    $nonce = $_POST['nonce'];

    // if they aint got a nonce, send em away
    if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) )
    {
        die ( 'Busted!');
    }

    // if a post is rated
    if(isset($_POST['post_like']))
    {
        // Retrieve user id
        $userID = get_current_user_id();
        $post_id = $_POST['post_id'];

        // Get voters'IPs for the current post
        $userVotes = get_post_meta($post_id, "votes_uservotes");

        if(!is_array($userVotes))
            $userVotes = array();

        // Get votes count for the current post
        $meta_count = get_post_meta($post_id, "votes_totalvotes", true);

        // if user has already voted
        if(!hasAlreadyVoted($post_id))
        {
            $userVotes = $userID;

            // Save ID and increase votes count
            update_post_meta($post_id, "votes_uservotes", $userVotes);
            update_post_meta($post_id, "votes_totalvotes", ++$meta_count);

            // Display count (ie jQuery return value)
            echo $meta_count;
        }
        else
            echo "already voted";
    }
    exit;
}

// time before user can vote again
$timebeforerevote = 10; // = 10 minutes

// if user has already voted
function hasAlreadyVoted($post_id)
{
    global $timebeforerevote;

    // Get voters'IPs for the current post
    $userVotes = get_post_meta($post_id, "votes_uservotes");

    if(!is_array($userVotes))
        $userVotes = array();

    // Retrieve user id
    $userID = get_current_user_id();

    // If user has already voted
    if(in_array($userID, array_keys($userVotes)))
    {
        $time = $userVotes[$userID];
        $now = time();

        // Compare between current time and vote time
        if(round(($now - $time) / 60) > $timebeforerevote)
            return false;

        return true;
    }
    return false;
}

// generate html markup
function generateRatingHTML($post_id)
{
    $themename = "twentyeleven";

    $vote_count = get_post_meta($post_id, "votes_totalcotes", true);

    $output = '<p class="post-like">';
    if(hasAlreadyVoted($post_id))
        $output .= ' <span title="'.__('I like this article', $themename).'" class="like alreadyvoted"></span>';
    else
        $output .= '<a href="#" data-post_id="'.$post_id.'">
                    <span  title="'.__('I like this article', $themename).'"class="qtip like"></span>
                </a>';
        $output .= '<span class="count">'.$vote_count.'</span></p>';

    return $output;
}

/**
 *
 * USAGE - echo generateRatingHTML(get_the_ID());
 *
 **/
