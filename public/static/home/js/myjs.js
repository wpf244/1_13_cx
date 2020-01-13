function commonTab(s,e,t){var i=s.index();s.addClass(t).siblings().removeClass(t),e.eq(i).addClass(t).siblings().removeClass(t)}function switchClas(s,e){s.addClass(e).siblings().removeClass(e)}function validatemobile(s){if(0!=s.length)if(11==s.length){/^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1}))+\d{8})$/.test(s)||alert("请输入有效的手机号码！")}else alert("请输入有效的手机号码，需是11位！");else alert("手机号码不能为空！")}$(function(){$(".detail_box p img").each(function(){$(this).parents("p").css("textIndent",0)}),$(".goods_detail p img").each(function(){$(this).parents("p").css("textIndent",0)}),$("#sider_close").click(function(){$("#sider_box").slideUp()}),$(".tab_item").click(function(){commonTab($(this),$(".tab_cont"),"active")})});
$(function() {
  $('.mess-bar-like,.mess-bar-collect').click(function() {
    $(this).toggleClass('on');
  })
})