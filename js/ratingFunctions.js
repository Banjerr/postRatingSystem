jQuery( document ).ready( function() {
    // when a post is voted up
    jQuery( ".post-rate .voteUp" ).click( function() {

        heart = jQuery( this );

        // Retrieve post ID from data attribute
        post_id = heart.data( "post_id" );

        // Ajax call
        jQuery.ajax( {
            type: "post",
            url: ajax_var.url,
            data: "action=post_up&nonce="+ajax_var.nonce+"&post_id="+post_id,
            success: function( response ) {
                // If vote successful
                if( response != "already voted" )
                {
                    //heart.addClass( "voted" );
                    //heart.siblings( ".count" ).text( count );

                    // add the percentage
                    jQuery( '.ratePercentage' ).html( response );

                    // remove the buttons
                    jQuery( '.post-rate a' ).each( function() {
                      jQuery( this ).fadeOut( 750, function() {
                        jQuery( this ).remove();
                      } );
                    } );
                }
            }
        } );
        return false;
    } );

    // when a post is voted down
    jQuery( ".post-rate .voteDown" ).click( function(){

        heart = jQuery( this );

        // Retrieve post ID from data attribute
        post_id = heart.data( "post_id" );

        // Ajax call
        jQuery.ajax( {
            type: "post",
            url: ajax_var.url,
            data: "action=post_down&nonce="+ajax_var.nonce+"&post_id="+post_id,
            success: function( response ) {
                // If vote successful
                if( response != "already voted" )
                {
                    // heart.addClass( "voted" );
                    // heart.siblings( ".count" ).text( count );

                    // add the percentage
                    jQuery( '.ratePercentage' ).html( response );

                    // remove the buttons
                    jQuery( '.post-rate a' ).each( function() {
                      jQuery( this ).fadeOut( 750, function() {
                        jQuery( this ).remove();
                      } );
                    } );
                }
            }
        } );
        return false;
    } );
} );
