(function($){

    function adjustWidgetWidth(){
        if ( $(window).width() > 641) {
            $('.customize-control-widget_form').addClass( 'wide-widget-control' );
        }
        else {
            $('.customize-control-widget_form').removeClass( 'wide-widget-control' );
        }
    }

    window.addEventListener('resize', _.debounce( adjustWidgetWidth, 200 ) );

    $( document ).ready(function(){
        wp.customize.Events.bind('ready', function(){
            adjustWidgetWidth();
        });

        adjustWidgetWidth();
    });
})(jQuery);