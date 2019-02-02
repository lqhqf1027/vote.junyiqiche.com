$(document).ready(function(){
   $(".fixed_button").click(function(){
	    //console.log('发布行程');
	    $(".backDiv").fadeIn();
   });
   $(".closeArea").click(function(){
        $(".backDiv").fadeOut();
   });

   $('.switchItem').click(function(){
   	    //type  passenger-乘客  driver-司机
        var type=$(this).attr('type');
        console.log('type:',type);
        //请求顺风车列表
        //成功后添加activeItem 类
        $(this).addClass('activeItem').siblings().removeClass('activeItem');
        
   });
   $('.form-switchItem').click(function(){
   	    //type  passenger-乘客  driver-司机
        var form_type=$(this).attr('type');
        console.log('form_type:',form_type);
        //请求顺风车列表
        //成功后添加activeItem 类
        $(this).addClass('activeItem').siblings().removeClass('activeItem');
        
   });
});

