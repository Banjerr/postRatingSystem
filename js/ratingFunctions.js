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
                // if user hasnt voted and there are votes already
                if( response != "already voted" && response != "0%")
                {
                    // add the percentage
                    jQuery( '.post-rate .ratePercentage h2' ).html( response );
                    jQuery( '.post-rate .ratePercentage .percentageChart' ).animate({
                        'width' : response
                    }, 750);

                    // remove the buttons
                    jQuery( '.post-rate a' ).each( function() {
                      jQuery( this ).animate({
                          'opacity' : '0'
                      }, 750);
                    } );
                }
                // if the user hasnt already voted and there arent any votes yet
                else if( response != "already voted" && response === "0%" )
                {
                    // tell em how it is
                    jQuery( '.post-rate .ratePercentage h2' ).html( '0%' );
                    jQuery( '.post-rate .ratePercentage .percentageChart' ).animate({
                        'width' : '0'
                    }, 750);

                    // remove the buttons
                    jQuery( '.post-rate a' ).each( function() {
                      jQuery( this ).animate({
                          'opacity' : '0'
                      }, 750);
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
            // If vote successful
            success: function( response ) {
                // if user hasnt voted and there are votes already
                if( response != "already voted" && response != "0%")
                {
                    // add the percentage
                    jQuery( '.post-rate .ratePercentage h2' ).html( response );
                    jQuery( '.post-rate .ratePercentage .percentageChart' ).animate({
                        'width' : response
                    }, 750);

                    // remove the buttons
                    jQuery( '.post-rate a' ).each( function() {
                      jQuery( this ).animate({
                          'opacity' : '0'
                      }, 750);
                    } );
                }
                // if the user hasnt already voted and there arent any votes yet
                else if( response != "already voted" && response === "0%" )
                {
                    // tell em how it is
                    jQuery( '.post-rate .ratePercentage h2' ).html( '0%' );
                    jQuery( '.post-rate .ratePercentage .percentageChart' ).animate({
                        'width' : '0'
                    }, 750);

                    // remove the buttons
                    jQuery( '.post-rate a' ).each( function() {
                      jQuery( this ).animate({
                          'opacity' : '0'
                      }, 750);
                    } );
                }
            }
        } );
        return false;
    } );
} );
