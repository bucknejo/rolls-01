;(function( $ ){
    
    var defaults = {
        
    }
    
    var methods = {
        init : function( options ) {
                        
            options = $.extend(defaults, options);           
                        
            return this.each(function(){
                                
                console.log("Please f-ing work for me mutha fucka.");
                
                
                

            });

        },
        destroy : function( ) {

            return this.each(function(){

            });

        },     
        create: function() {

        },
        read: function() {

        },
        update: function() {

        },
        remove: function() {

        },     
        show : function( ) { 
        // ... 
        },
        hide : function( ) {
        // ... 
        }

    };

    $.fn.trainingSessions = function( method ) {

        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.tooltip' );
        }    

    };

})( jQuery );