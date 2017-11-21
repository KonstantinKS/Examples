$(document).ready(function(e){
	
$('.c-opener').on('click', function() {	
	if ($('.header-contacts').css('display')=='none') {
		$('.header-contacts').css('display', 'block');
	} else {
		$('.header-contacts').css('display', 'none');
	}
});

$('.bxslider').bxSlider({
	minSlides: 1,
	maxSlides: 2,
	slideWidth: 495,
	slideMargin: 10,
	moveSlides: 1,
});

/* Форма обратной связи */
$(".header_contact button").click(function(){	
	$("#leave_application").slideDown("normal");
	$(".background").fadeIn(500);
});
$(".footer_telephone_application").click(function(){	
	$("#leave_application").slideDown("normal");
	$(".background").fadeIn(500);
});
$(".header_email p a").click(function(){	
	$("#request_call").slideDown("normal");
	$(".background").fadeIn(500);
});
$(".footer_telephone_request").click(function(){	
	$("#request_call").slideDown("normal");
	$(".background").fadeIn(500);
});
$(".exit_form").click(function(){
	$(".leave_application").slideUp("normal");
	$(".background").fadeOut(500);
	$("#leave_application").slideUp("normal");
	$("#request_call").slideUp("normal");
});
$("#leave_application").css({"margin-left": "-"+$("#leave_application").outerWidth() / 2+"px","margin-top":"-"+$("#leave_application").outerHeight() / 2+"px"});
$("#request_call").css({"margin-left": "-"+$("#request_call").outerWidth() / 2+"px","margin-top":"-"+$("#request_call").outerHeight() / 2+"px"});


/* Анимация для слайдера на главной странице */
$("#slides .slide_text .title").addClass("fadeInRight animated_one");
$("#slides .slide_text .description").addClass("fadeInRight animated_second");
$("#slides .slide_text .more").addClass("fadeInRight animated_three");


/* Подстановка высоты footer и hFooter */
$(".hFooter").height($("footer").innerHeight());
$("footer").css("margin-top","-"+$("footer").innerHeight()+"px");


/* Слайдер */
$("#slides .slide_text").css({"margin-top":"-"+$("#slides .slide_text").height() / 2 +"px"});
$("#slides .slides_images").css({"width":$(window).width()+"px"});
$(window).resize(function(){
	$("#slides .slides_images").css({"width":$(window).width()+"px"});
	$("#slides .slide_text").css({"margin-top":"-"+$("#slides .slide_text").height() / 2 +"px"});
	$(".hFooter").height($("footer").innerHeight());
	$("footer").css("margin-top","-"+$("footer").innerHeight()+"px");
});
$(".prev, .next").css({"margin-top":"-"+$(".prev").height() / 2+"px"});


/* В проектах показать еще элементы */
$(".projects").on('click', 'button', function(){
	startLoadingAnimation();
	$.ajax({
		async: false,
		type: "POST",
		url: $(this).attr("data-site-id")+"ajax_script/projects.php",
		data: "ajax_array="+$(this).attr("data-all-count")+"&section_id="+$(this).attr("data-section-id"),
		dataType: "html",
		success:function(data){
			setTimeout(function(){
				$(".projects").html("");
				$(".loadImg").hide();
				$(".projects").html(data);
			}, 1000);
		} 
	});
});

function startLoadingAnimation()
{
	var imgObj = $(".loadImg");
	var bgObj = $(".load_bg");
	imgObj.fadeIn();
	bgObj.fadeIn();
	imgObj.css({"margin-left": "-"+imgObj.width() / 2+"px", "margin-top": "-"+imgObj.height() / 2+"px"});
}


/* Блок конечной суммы в калькуляторе */
$(".calculator tbody .column_5 input").keyup(function(){
	var total_price = 0;
	$(".calculator tbody .column_5 input").each(function(){
		var count = $(this).val();
		var price = parseInt($(this).attr("data-price"));
		total_price = total_price + (price * count);
		$(".total_price span").text(total_price);
		$(".total_price_bottom span").text(total_price);
	});
});
$(".calculator tbody .column_5 input").keyup(function(){
	var count = $(this).val();
	var price = parseInt($(this).attr("data-price"));
	var parent = $(this).parentsUntil("tbody");
	$("> .column_6 span", parent).text(count * price +" руб.");
});


/* Проверяем корректно ли отправилось сообщение */
var url = window.location.search;
var arrayVar = (url.substr(1)).split('&');
var valueAndKey = [];
var resultArray = [];
for (i = 0; i < arrayVar.length; i++) {
	valueAndKey = arrayVar[i].split('=');
	resultArray[valueAndKey[0]] = valueAndKey[1];
	if(valueAndKey[0] == "success"){
		$('.dialog').css("display","block").removeClass("dialog-close").addClass("dialog-open");
		$(".background").css("display","block");
		window.history.pushState(null, null, "index-2.html");
	}
}

/* Блок об успешной отправки сообщения */
$(".dialog").css({"margin-left": "-"+$(".dialog").outerWidth() / 2+"px","margin-top":"-"+$(".dialog").outerHeight() / 2+"px"});
$(".dialog button").click(function(){
	$(".dialog").removeClass("dialog-open").addClass("dialog-close");
	$(".background").fadeOut(500);
	$(".dialog").fadeOut(500);
});
$(".background").click(function(){
	$(".dialog").removeClass("dialog-open").addClass("dialog-close");
	$(this).fadeOut(500);
	$(".dialog").fadeOut(500);
	$("#leave_application").slideUp("normal");
	$("#request_call").slideUp("normal");
});

/* Фиксированное меню */
var $menu = $("#top_menu");
$(window).scroll(function(){
	if((device.windows() || device.fxos()) && $(window).width() > '1023') {
		if ( $(this).scrollTop() > 145 && $menu.hasClass("default") ){
			$menu.removeClass("default").addClass("fixed_menu");
			$menu.prev('div.header_wrapper').addClass('fixed_div');
		} else if($(this).scrollTop() <= 145 && $menu.hasClass("fixed_menu")) {
			$menu.removeClass("fixed_menu").addClass("default");
			$menu.prev('div.header_wrapper').removeClass('fixed_div');
		}
	}
});

/* Вернуться наверх */
var offset = 300,
offset_opacity = 1200,
scroll_top_duration = 700,
$back_to_top = $('.top_map');
$(window).scroll(function(){
	( $(this).scrollTop() > offset ) ? $back_to_top.addClass('cd_is_visible') : $back_to_top.removeClass('cd_is_visible cd_fade_out');
	if( $(this).scrollTop() > offset_opacity ) { 
		$back_to_top.addClass('cd_fade_out');
	}
});
$back_to_top.on('click', function(event){
	event.preventDefault();
	$('body,html').animate({
		scrollTop: 0 ,
		}, scroll_top_duration
	);
});


$(".projects_photo a").fancybox();


$('#slides').slides({
	preload: true,
	preloadImage: '/bitrix/templates/site/css/themes/images/slider/loading.gif',
	play: 5000,
	hoverPause: false,
	animationComplete: function(current){if (window.console && console.log) {};}
});


var pull = $('#mobile_menu');
var menu = $('header nav ul');
$(pull).on('click', function(e) {
	e.preventDefault();
	menu.slideToggle();
});	

/* фиксированное поле с калькулятором */		
var a = document.querySelector('.calculator .total_price'), b = null;  // селектор блока, который нужно закрепить
if(a){
	window.addEventListener('scroll', Ascroll, false);
	document.body.addEventListener('scroll', Ascroll, false);  // если у html и body высота равна 100%
	function Ascroll() {
	  if (b == null) {  // добавить потомка-обёртку, чтобы убрать зависимость с соседями
		var Sa = getComputedStyle(a, ''), s = '';
		for (var i = 0; i < Sa.length; i++) {  // перечислить стили CSS, которые нужно скопировать с родителя
		  if (Sa[i].indexOf('padding') == 0) {
			s += Sa[i] + ': ' +Sa.getPropertyValue(Sa[i]) + '; '
		  }
		}
		b = document.createElement('div');  // создать потомка
		//b.style.cssText = s + ' box-sizing: border-box; width: ' + a.offsetWidth + 'px;';
		a.insertBefore(b, a.firstChild);  // поместить потомка в цепляющийся блок первым
		var l = a.childNodes.length;
		for (var i = 1; i < l; i++) {  // переместить во вновь созданного потомка всех остальных потомков (итого: создан потомок-обёртка, внутри которого по прежнему работают скрипты)
		  b.appendChild(a.childNodes[1]);
		}
		//a.style.height = b.getBoundingClientRect().height + 'px';  // если под скользящим элементом есть другие блоки, можно своё значение
		//a.style.padding = '0';
		//a.style.border = '0';
	  }
	  if (a.getBoundingClientRect().top <= 0) { // elem.getBoundingClientRect() возвращает в px координаты элемента относительно верхнего левого угла области просмотра окна браузера
		b.className = 'total_fixed_price';
	  } else {
		b.className = '';
	  }
	  window.addEventListener('resize', function() {
		a.children[0].style.width = getComputedStyle(a, '').width
	  }, false);  // если изменить размер окна браузера, измениться ширина элемента
	}
}


/* Блок переключения цвета */
$('.style-switcher .switch').click(function(e){
	e.preventDefault();
	var styleswitcher = $(this).closest('.style-switcher');
	if(styleswitcher.hasClass('active')){
		styleswitcher.animate({left: '-' + styleswitcher.outerWidth() + 'px'}, 300).removeClass('active');
	}
	else{
		styleswitcher.animate({left: '0'}, 300).addClass('active');
		var pos = styleswitcher.offset().top;
		if($(window).scrollTop() > pos){
			$('html, body').animate({scrollTop: pos}, 500);
		}
	}
});
$('.style-switcher .options li').click(function(e){
	$(this).addClass('active').siblings().removeClass('active');
	var name_color = $("> a",this).attr("data-option-value");
	var link_color = "/bitrix/templates/sait/css/themes/styles_"+name_color+".css";
	$("[data-color = 'true']").attr("href",link_color);
	$.cookie('color_theme', name_color, { expires: 7, path: '/' });
});



});

