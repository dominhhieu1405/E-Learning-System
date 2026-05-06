


$(document).ready(function () {
    $("img").on("error", function () {
        $(this).attr("src", "/assets/img/no-image.png");
    });
});


$(".show-menu-space, .myNav, .hide-Siloiki-Menu").on("click", function () {
    $("body").toggleClass("spring-open")
})

$("#Siloiki-FlexMenuList .sub-tab").on("click", function () {
    $(this).parent().removeClass("show").find("> .m-sub").slideToggle(170)
});
$(".Siloiki-Menu ul li a").on("click", function () {
    $(this).parent().removeClass("show").find("> .m-sub").slideToggle(170)
})

$(".dark-toggle, .myDark").on("click", function () {
    localStorage.setItem("mode", "darkmode" === localStorage.getItem("mode") ? "mydark" : "darkmode");
    (localStorage.getItem('mode')) === 'darkmode' ? document.querySelector('#mainContent').classList.add('dark') : document.querySelector('#mainContent').classList.remove('dark')
    $("#darkroom, #darkroom1").each(function () {
        var e = $(this);
        "darkmode" === localStorage.getItem("mode") ? e.attr("src", e.attr("data-dark")) : ($("#mainContent").removeClass("dark"), e.attr("src", e.attr("data-normal")))
    })
});

$(".search-button-flex, .mySearch").on("click",function(){
    $("#search-flex").fadeIn(200).find("input").focus();
    $("body").addClass("active-search")
})

$(".overlay, .search-flex-close").on("click",function(){
    $("#search-flex").fadeOut(200).find("input").blur();
    $("body").removeClass("active-search spring-open")
})

$(".backTop, .myTop").each(function () {
    var e = $(this);
    $(window).on("scroll", function () {
        100 <= $(this).scrollTop() ? e.fadeIn(250) : e.fadeOut(250)
    });
    e.click(function () {
        $("html, body").animate({scrollTop: 0}, 500)
    })
});

const updateOnline = function () {
    try {
        $.get("/api/online", function (e) {

            var $count = parseInt(e); // Giá trị đích
            var $counter = $("#pop-stats #counter"); // Lấy phần tử hiển thị số

            $({ Counter: parseInt($counter.text()) }).animate(
                { Counter: $count },
                {
                    duration: 1000, // Thời gian chạy (ms)
                    step: function (now) {
                        $counter.text(Math.floor(now));
                    }
                }
            );
        })
    } catch (e) {
        console.log(e)
    }
};
// every 5s
setInterval(updateOnline, 5e3);
updateOnline();