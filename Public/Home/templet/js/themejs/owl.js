
/* ---------------------------------------------------
 Owl carousel - Slider
 -------------------------------------------------- */
$('.slideshow').owlCarousel2({
    loop:true,
    margin:0,
    responsiveClass:true,
    nav: true,
    dots: false,
    autoplay : true,
    responsive:{
        0:{
            items:1,
        },
        600:{
            items:1,
        },
        1000:{
            items:1,
        }
    }
});

/* ---------------------------------------------------
 Listing Tabs - Slider
 -------------------------------------------------- */
/********
 *
 * @param targer 要实现木马轮换的目标标签
 * @param total_product 商品总数量
 */
function setOwl(targer,total_product) {
    var nb_column0 = 4,
        nb_column1 = 3,
        nb_column2 = 2,
        nb_column3 = 1,
        nb_column4 = 1;
    targer.owlCarousel2({
        nav: true,
        dots: false,
        margin: 25,
        loop: true,
        autoplay: true,
        autoplayHoverPause: true,
        autoplayTimeout: 5000,
        autoplaySpeed: 500,
        navRewind: true,
        navText: ['', ''],
        responsive: {
            0: {
                items: nb_column4,
                nav: total_product <= nb_column4 ? false : ((true) ? true : false),
            },
            480: {
                items: nb_column3,
                nav: total_product <= nb_column3 ? false : ((true) ? true : false),
            },
            768: {
                items: nb_column2,
                nav: total_product <= nb_column2 ? false : ((true) ? true : false),
            },
            992: {
                items: nb_column1,
                nav: total_product <= nb_column1 ? false : ((true) ? true : false),
            },
            1200: {
                items: nb_column0,
                nav: total_product <= nb_column0 ? false : ((true) ? true : false),
            },
        }
    });
}
$(document).ready(function($) {
    var $tag_id = $('#so_listing_tabs_1'),
        parent_active = $('.items-category-0', $tag_id);
    //total_product = parent_active.data('total'),
    $.ajax({
        type: "POST",
        async: false,
        dataType: "json",
        url: root_url + '/Home/Index/getCat',
        data: {cid: '0', rec: '1'},
        success: function (data) {
            if (data.error === 0) {
                parent_active.html(data.content);
                parent_active.data("tab-load", 1);//1代表已缓存，不需要再AJAX
                var tab_active = $('.ltabs-items-inner', parent_active),
                    total_product = tab_active.data('total');
                setOwl(tab_active, total_product);
            } else {
                alert("error:indexajax");
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert(textStatus +':'+ errorThrown);
        }
    });

    var $tag_id = $('#so_listing_tabs_2'),
        parent_active = $('.items-category-0', $tag_id);
    //total_product = parent_active.data('total'),
    $.ajax({
        type: "POST",
        async: false,
        dataType: "json",
        url: root_url + '/Home/Index/getCat',
        data: {cid: '0', rec: '2'},
        success: function (data) {
            if (data.error === 0) {
                parent_active.html(data.content);
                parent_active.data("tab-load", 1);
                var tab_active = $('.ltabs-items-inner', parent_active),
                    total_product = tab_active.data('total');
                setOwl(tab_active, total_product);
            } else {
                alert("error:indexajax");
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert(textStatus +':'+ errorThrown);
        }
    });

    var $tag_id = $('#so_listing_tabs_3'),
        parent_active = $('.items-category-0', $tag_id);
    //total_product = parent_active.data('total'),
    $.ajax({
        type: "POST",
        async: false,
        dataType: "json",
        url: root_url + '/Home/Index/getCat',
        data: {cid: '0', rec: '3'},
        success: function (data) {
            if (data.error === 0) {
                parent_active.html(data.content);
                parent_active.data("tab-load", 1);
                var tab_active = $('.ltabs-items-inner', parent_active),
                    total_product = tab_active.data('total');
                setOwl(tab_active, total_product);
            } else {
                alert("error:indexajax");
            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert(textStatus +':'+ errorThrown);
        }
    });
});

$(document).ready(function($) {
    //大屏变sm或以下时，展示框变下拉
    if ($(window).innerWidth() <= 767) {
        $('.ltabs-tabs-wrap').addClass('ltabs-selectbox');
    } else {
        $('.ltabs-tabs-wrap').removeClass('ltabs-selectbox');
    }

    //大屏变sm或以下时，展示框变下拉，调整浏览器窗口时触发
    $(window).resize(function () {
        if ($(window).innerWidth() <= 767) {
            $('.ltabs-tabs-wrap').addClass('ltabs-selectbox');
        } else {
            $('.ltabs-tabs-wrap').removeClass('ltabs-selectbox');
        }
    });
});

$(document).ready(function($) {
    (function (element) {
        var $element = $(element),
            $tab = $('.ltabs-tab', $element),
            $tab_label = $('.ltabs-tab-label', $tab),
            $tabs = $('.ltabs-tabs', $element),
            //ajax_url = $tabs.parents('.ltabs-tabs-container').attr('data-ajaxurl'),
            effect = $tabs.parents('.ltabs-tabs-container').attr('data-effect'),
            delay = $tabs.parents('.ltabs-tabs-container').attr('data-delay'),
            duration = $tabs.parents('.ltabs-tabs-container').attr('data-duration'),
            type_source = $tabs.parents('.ltabs-tabs-container').attr('data-type_source'),
            $items_content = $('.ltabs-items', $element),
            $items_inner = $('.ltabs-items-inner', $items_content),
            $items_first_active = $('.ltabs-items-selected', $element),
            $select_box = $('.ltabs-selectbox', $element),
            $tab_label_select = $('.ltabs-tab-selected', $element),
            type_show = 'slider';

        //sm或以下，下拉商品展示框选择器时
        $('span.ltabs-tab-selected, span.ltabs-tab-arrow', $element).click(function () {
            if ($('.ltabs-tabs', $element).hasClass('ltabs-open')) {
                $('.ltabs-tabs', $element).removeClass('ltabs-open');
            } else {
                $('.ltabs-tabs', $element).addClass('ltabs-open');
            }
        });

        //'shown.bs.tab'事件监听方法,还没理解
        /*$tab.on('shown.bs.tab', function (e) {
         console.log(23333)
         $($(e.target).attr('href'))
         .find('.owl2-carousel')
         .owlCarousel2('invalidate', 'width')
         .owlCarousel2('update')
         });*/

        $tab.on('click.ltabs-tab', function () {
            var $this = $(this);
            if ($this.hasClass('tab-sel')) return false;
            if ($this.parents('.ltabs-tabs').hasClass('ltabs-open')) {
                $this.parents('.ltabs-tabs').removeClass('ltabs-open');
            }
            $tab.removeClass('tab-sel');
            $this.addClass('tab-sel');
            $tab_label_select.html($this.children('.ltab-tab-label').html());

            var items_active = $this.data('active-content');
            var _items_active = $(items_active, $element);

            if (!_items_active.data("tab-load")) {
                var cat_id = $this.data('category-id');
                $.ajax({
                    type: "POST",
                    async: false,
                    dataType: "json",
                    url: root_url + '/Home/Index/getCat',
                    data: {cid: cat_id, rec: '1'},
                    success: function (data) {
                        if (data.error === 0) {
                            _items_active.html(data.content);
                            // _items_active.attr("data-tab-load", 1);//改html表面的值，改不改都行
                            _items_active.data("tab-load", 1);
                            var tab_active = $('.ltabs-items-inner', _items_active),
                                total_product = tab_active.data('total');
                            setOwl(tab_active, total_product);
                        } else {
                            alert("error:indexajax");
                        }
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        alert(textStatus +':'+ errorThrown);
                    }
                });
            }
            $items_content.removeClass('ltabs-items-selected');
            _items_active.addClass('ltabs-items-selected');
        });
    })('#so_listing_tabs_1');
});

$(document).ready(function($) {
    (function (element) {
        var $element = $(element),
            $tab = $('.ltabs-tab', $element),
            $tab_label = $('.ltabs-tab-label', $tab),
            $tabs = $('.ltabs-tabs', $element),
            //ajax_url = $tabs.parents('.ltabs-tabs-container').attr('data-ajaxurl'),
            effect = $tabs.parents('.ltabs-tabs-container').attr('data-effect'),
            delay = $tabs.parents('.ltabs-tabs-container').attr('data-delay'),
            duration = $tabs.parents('.ltabs-tabs-container').attr('data-duration'),
            type_source = $tabs.parents('.ltabs-tabs-container').attr('data-type_source'),
            $items_content = $('.ltabs-items', $element),
            $items_inner = $('.ltabs-items-inner', $items_content),
            $items_first_active = $('.ltabs-items-selected', $element),
            $select_box = $('.ltabs-selectbox', $element),
            $tab_label_select = $('.ltabs-tab-selected', $element),
            type_show = 'slider';

        //sm或以下，下拉商品展示框选择器时
        $('span.ltabs-tab-selected, span.ltabs-tab-arrow', $element).click(function () {
            if ($('.ltabs-tabs', $element).hasClass('ltabs-open')) {
                $('.ltabs-tabs', $element).removeClass('ltabs-open');
            } else {
                $('.ltabs-tabs', $element).addClass('ltabs-open');
            }
        });

        $tab.on('click.ltabs-tab', function () {
            var $this = $(this);
            if ($this.hasClass('tab-sel')) return false;
            if ($this.parents('.ltabs-tabs').hasClass('ltabs-open')) {
                $this.parents('.ltabs-tabs').removeClass('ltabs-open');
            }
            $tab.removeClass('tab-sel');
            $this.addClass('tab-sel');
            $tab_label_select.html($this.children('.ltab-tab-label').html());

            var items_active = $this.data('active-content');
            var _items_active = $(items_active, $element);

            if (!_items_active.data("tab-load")) {
                var cat_id = $this.data('category-id');
                $.ajax({
                    type: "POST",
                    async: false,
                    dataType: "json",
                    url: root_url + '/Home/Index/getCat',
                    data: {cid: cat_id, rec: '2'},
                    success: function (data) {
                        if (data.error === 0) {
                            _items_active.html(data.content);
                            _items_active.data("tab-load", 1);
                            var tab_active = $('.ltabs-items-inner', _items_active),
                                total_product = tab_active.data('total');
                            setOwl(tab_active, total_product);
                        } else {
                            alert("error:indexajax");
                        }
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        alert(textStatus +':'+ errorThrown);
                    }
                });
            }
            $items_content.removeClass('ltabs-items-selected');
            _items_active.addClass('ltabs-items-selected');
        });
    })('#so_listing_tabs_2');
});

$(document).ready(function($) {
    (function (element) {
        var $element = $(element),
            $tab = $('.ltabs-tab', $element),
            $tab_label = $('.ltabs-tab-label', $tab),
            $tabs = $('.ltabs-tabs', $element),
            //ajax_url = $tabs.parents('.ltabs-tabs-container').attr('data-ajaxurl'),
            effect = $tabs.parents('.ltabs-tabs-container').attr('data-effect'),
            delay = $tabs.parents('.ltabs-tabs-container').attr('data-delay'),
            duration = $tabs.parents('.ltabs-tabs-container').attr('data-duration'),
            type_source = $tabs.parents('.ltabs-tabs-container').attr('data-type_source'),
            $items_content = $('.ltabs-items', $element),
            $items_inner = $('.ltabs-items-inner', $items_content),
            $items_first_active = $('.ltabs-items-selected', $element),
            $select_box = $('.ltabs-selectbox', $element),
            $tab_label_select = $('.ltabs-tab-selected', $element),
            type_show = 'slider';

        //sm或以下，下拉商品展示框选择器时
        $('span.ltabs-tab-selected, span.ltabs-tab-arrow', $element).click(function () {
            if ($('.ltabs-tabs', $element).hasClass('ltabs-open')) {
                $('.ltabs-tabs', $element).removeClass('ltabs-open');
            } else {
                $('.ltabs-tabs', $element).addClass('ltabs-open');
            }
        });

        $tab.on('click.ltabs-tab', function () {
            var $this = $(this);
            if ($this.hasClass('tab-sel')) return false;
            if ($this.parents('.ltabs-tabs').hasClass('ltabs-open')) {
                $this.parents('.ltabs-tabs').removeClass('ltabs-open');
            }
            $tab.removeClass('tab-sel');
            $this.addClass('tab-sel');
            $tab_label_select.html($this.children('.ltab-tab-label').html());

            var items_active = $this.data('active-content');
            var _items_active = $(items_active, $element);

            if (!_items_active.data("tab-load")) {
                var cat_id = $this.data('category-id');
                $.ajax({
                    type: "POST",
                    async: false,
                    dataType: "json",
                    url: root_url + '/Home/Index/getCat',
                    data: {cid: cat_id, rec: '3'},
                    success: function (data) {
                        if (data.error === 0) {
                            _items_active.html(data.content);
                            _items_active.data("tab-load", 1);
                            var tab_active = $('.ltabs-items-inner', _items_active),
                                total_product = tab_active.data('total');
                            setOwl(tab_active, total_product);
                        } else {
                            alert("error:indexajax");
                        }
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        alert(textStatus +':'+ errorThrown);
                    }
                });
            }
            $items_content.removeClass('ltabs-items-selected');
            _items_active.addClass('ltabs-items-selected');
        });
    })('#so_listing_tabs_3');
});