var slideshow_interval = null;
var slideshow_timeout = 4000;
var baseurl = site_url = $.trim($('base').attr('href'));

function slideshow() {
    var active_slide = $('.hero_section .slideshow > ul > li.active').not(".last-active");

    if (active_slide.length === 0) {
        $('.hero_section .slideshow > ul > li.active').removeClass('last-active active');
        active_slide = $('.hero_section .slideshow > ul > li:last-child');
        active_slide.addClass('active');
    }

    var next_slide = active_slide.next();

    if (next_slide.length === 0) {
        next_slide = $('.hero_section .slideshow > ul > li:first-child');
    }

    active_slide.addClass('last-active');
    active_slide.removeClass('active last-active');

    next_slide.css({
        opacity: 0.0
    })
    .addClass('active')
    .animate({
        opacity: 1.0,
    }, 1000, function() {});

    if (slideshow_interval != null) {
        clearTimeout(slideshow_interval);
    }
    slideshow_interval = setTimeout("slideshow()", slideshow_timeout);
}


$(window).on('load', function() {
    if ($('.hero_section .slideshow').length > 0) {
        if ($('.hero_section .slideshow > ul > li').length > 1) {
            slideshow();
        } else {
            $('.hero_section .slideshow > ul > li:first-child').addClass('active');
        }
    }
});

$(document).ready(function() {
    $('body').on('contextmenu', 'img', function(e) {
        return false;
    });
});


$("body").on('click', '.frequently_asked_questions .questions > .item', function(e) {
    if (!$(this).hasClass('open')) {
        $('.frequently_asked_questions .questions > .item').removeClass('open');
        $(this).addClass('open');
    } else {
        $('.frequently_asked_questions .questions > .item').removeClass('open');
    }
});