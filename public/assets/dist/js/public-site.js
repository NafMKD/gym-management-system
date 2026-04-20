(function ($) {
    function closeMenu($toggle, $nav) {
        $nav.removeClass('is-open');
        $toggle.attr('aria-expanded', 'false');
    }

    function openMenu($toggle, $nav) {
        $nav.addClass('is-open');
        $toggle.attr('aria-expanded', 'true');
    }

    function animateCounter($counter) {
        if ($counter.data('counted')) {
            return;
        }

        $counter.data('counted', true);

        $({ value: 0 }).animate(
            { value: Number($counter.data('counter')) || 0 },
            {
                duration: 1200,
                easing: 'swing',
                step: function (now) {
                    $counter.text(Math.floor(now));
                },
                complete: function () {
                    $counter.text($counter.data('counter'));
                }
            }
        );
    }

    function revealElements() {
        var trigger = $(window).scrollTop() + ($(window).height() * 0.86);

        $('[data-reveal]').each(function () {
            var $item = $(this);

            if ($item.hasClass('is-visible')) {
                return;
            }

            if ($item.offset().top < trigger) {
                $item.addClass('is-visible');
            }
        });

        $('[data-counter]').each(function () {
            var $counter = $(this);

            if ($counter.offset().top < trigger) {
                animateCounter($counter);
            }
        });
    }

    $(function () {
        var $menuToggle = $('[data-menu-toggle]');
        var $publicNav = $('[data-public-nav]');

        if ($menuToggle.length && $publicNav.length) {
            $menuToggle.on('click', function () {
                if ($publicNav.hasClass('is-open')) {
                    closeMenu($menuToggle, $publicNav);
                    return;
                }

                openMenu($menuToggle, $publicNav);
            });

            $publicNav.find('a').on('click', function () {
                closeMenu($menuToggle, $publicNav);
            });
        }

        $('a[href*="#"]').on('click', function (event) {
            var href = $(this).attr('href') || '';

            if (href.charAt(0) !== '#') {
                return;
            }

            var $target = $(href);

            if (! $target.length) {
                return;
            }

            event.preventDefault();

            $('html, body').animate({
                scrollTop: $target.offset().top - 110
            }, 550);
        });

        revealElements();
        $(window).on('scroll resize', revealElements);
    });
})(jQuery);
