const jQuery = window.jquery = require('jquery');

var menuButton = document.querySelector('.menu-toggle');
var navBar = document.querySelector('.navbar-container');

menuButton.addEventListener('click', menuToggle);

function menuToggle() {
    if (menuButton.classList.contains('close')) {
        menuButton.classList.remove('close');
        menuButton.classList.add('expand');
        navBar.classList.add('menu__open');
    } else {
        menuButton.classList.remove('expand');
        menuButton.classList.add('close');
        navBar.classList.remove('menu__open');
    }
}

(function($) {

    $(window).on('load', function() {
        $('.sproket').remove();
    });

    var rx,ry,nx1,nx2,ny1,ny2, nx3, ny3;
    var x1 = $('.coin, .cart');
    var x2 = $('.carry, .packet');
    var x3 = $('.david, .rusty');
    if ($('.parallax-it').length) {
        $('body').on('mousemove', function(e) {
            var $that = $(this);
            rx = e.pageX - $that.offset().left - window.outerWidth/2;
            ry = e.pageY - $that.offset().top;
            nx1 = rx/40;
            ny1 = ry/40;
            nx2 = rx/24;
            ny2 = ry/24;
            nx3 = rx/16;
            ny3 = ry/16;
            x1.css({transform: 'translate3d('+nx1+'px,'+ny1+'px, 0)'});
            x2.css({transform: 'translate3d('+nx2+'px,'+ny2+'px, 0)'});
            x3.css({transform: 'translate3d('+nx3+'px,'+ny3+'px, 0)'})
        });
    }


    var hf = {
        $forms: $('.form-hubspot'),
        apiUrl: 'https://api.hsforms.com/submissions/v3/integration/submit/:portalId/:formGuid',
        init: function() {
            if (this.$forms.length) {
                this.bind();
            }
        },
        bind: function() {
            this.$forms.each(function(index, form) {
                var $form = $(form); 
                $($form).on('submit', function() {
                    if (hf.validate($form)) {
                        $($form).parent().addClass('success');
                    }
                    return false;
                });
            });
        },
        clear: function($form) {},
        validate: function($form) {
            return true;
        }
    };

    hf.init();

})(jQuery);