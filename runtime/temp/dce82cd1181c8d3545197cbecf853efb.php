<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:89:"D:\phpStudy\WWW\yinchuan.junyiqiche.com\public/../application/index\view\index\index.html";i:1547437816;s:85:"D:\phpStudy\WWW\yinchuan.junyiqiche.com\application\index\view\vote\layoutextend.html";i:1546949393;s:79:"D:\phpStudy\WWW\yinchuan.junyiqiche.com\application\index\view\vote\header.html";i:1547434685;s:79:"D:\phpStudy\WWW\yinchuan.junyiqiche.com\application\index\view\vote\footer.html";i:1547436661;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport"/>

    <title>银川君忆</title>

    <!-- Bootstrap Core CSS -->
    <link href="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/index.css" rel="stylesheet">

    <!-- Plugin CSS -->
    <link href="https://cdn.staticfile.org/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://cdn.staticfile.org/simple-line-icons/2.4.1/css/simple-line-icons.min.css" rel="stylesheet">
    <!--Layui CDN-->
    <link rel="stylesheet" href="//layui.hcwl520.com.cn/layui/css/layui.css?v=201809030202">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://cdn.staticfile.org/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://cdn.staticfile.org/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<style>
    #test1 img {
        max-width: 100%;
        height: auto;
    }

    .pd_12 {
        padding: 8px 0px 0px 0px;
    }

    .headBox {
        border: 1px solid #F2F2F2;
        border-radius: 5px;
        background-color: #fff
    }

    .headBoxColor {
        color: #d2d2d2;
    }

    .head_body {
        position: relative;
        margin: -15px auto;
    }

    .border-raduis-5 {
        border-radius: 5px;
        padding: 0 5px;
    }

    .ctivity-time {
        margin-top: 35px;
    }

    .footer_box .row_style {
        background-color: #d2d2d2;
        position: fixed;
        bottom: 0;
        width: 100%
    }

    .footer_box .item {
        padding: 15px 0;
    }



</style>

<body id="page-top" style="background-color: #F2F2F2">


<div class="layui-carousel" id="test1">
    <div carousel-item>
        <?php foreach($data['bannerList'] as $k => $v): ?>
        <div><img src="<?php echo $config['upload']['cdnurl']; ?>/<?php echo $v['rotationimage']; ?>" style=""></img>
        </div>
        <!-- <div><img src="http://assets.youzhan.org/img/8/f4/d8ece1342920b4388c8174d349925.jpg"></img></div> -->
        <?php endforeach; ?>
    </div>
</div>
<div class="layui-container">
    <div class="head_body" style="">
        <div class="layui-row text-center ">
            <div class="layui-col-xs12 headBox" style="">
                <div class="layui-row pd_12" style="font-weight: bold;font-size: 16px;">
                    <div class="layui-col-xs4"><?php echo $data['statistics']['applicationCount']; ?></div>
                    <div class="layui-col-xs4"><?php echo $data['statistics']['voteCount']; ?></div>
                    <div class="layui-col-xs4  "><?php echo $data['statistics']['visitCount']; ?></div>
                </div>
                <div class="layui-row " style="padding-bottom: 8px;">
                    <div class="layui-col-xs4 headBoxColor"><i class="layui-icon layui-icon-user"></i> 参赛者</div>
                    <div class="layui-col-xs4 headBoxColor"><i class="fa fa-line-chart" aria-hidden="true"></i> 投票数
                    </div>
                    <div class="layui-col-xs4  headBoxColor"><i class="fa fa-eye"></i> 访问量</div>
                </div>
            </div>
        </div>
    </div>
    <div class=" ctivity-time" style="">
        <div class="layui-row text-center ">
            <div class="layui-col-xs12 headBox " style="padding: 8px 0;">
                <i class="layui-icon layui-icon-log" aria-hidden="true" style=""></i>
                <span>投票于</span>
                <!-- <?php echo $data['voteEndTime']; ?> -->
                <span class="bg-danger text-danger border-raduis-5">1</span>月
                <span class="bg-danger text-danger border-raduis-5">25</span>日
                <span class="bg-danger text-danger border-raduis-5">23</span>时
                <span class="bg-danger text-danger border-raduis-5">59</span>分
                <span class="bg-danger text-danger border-raduis-5">59</span>秒
                <span class="text-danger">结束</span>

            </div>

        </div>

        <div class="layui-row text-center " style="padding-top: 20px">
            <?php if($data['is_application'] == 1): ?>
            <button class="btn btn-info btn-lg "  style="width: 80%; background-color: gray">
                已报名
            </button>
            <?php else: ?>
            <button class="btn btn-info btn-lg " data-toggle="modal" data-target="#exampleModal" style="width: 80%">
                我要报名
            </button>
            <?php endif; ?>
        </div>
        <div class="layui-row text-center " style="padding-top: 20px">
        </div>


    </div>
    <div class="modal " id="exampleModal" role="dialog" aria-labelledby="exampleModalLabel" style="">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="exampleModalLabel">提交报名信息</h4>
                </div>
                <div class="modal-body" style="padding:15px 0 ;">
                    <form class="layui-form container" action="">
                        <div class="row">
                            <div class="form-group col-xs-6">
                                <label for="name" class="control-label">姓名:</label>
                                <input type="text" class="form-control" class="layui-input" name="name" id="name"
                                       placeholder="请输入你的姓名" required lay-verify="required">
                            </div>
                            <div class="form-group col-xs-6">
                                <label for="model" class="control-label">车型:</label>
                                <input type="text" class="form-control" class="layui-input" name="model" id="model"
                                       placeholder="请输入你的车型"  required lay-verify="required">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-xs-6">
                                <label for="daily_running_water" class="control-label">日均流水:</label>
                                <input type="text" class="form-control" class="layui-input" name="daily_running_water"
                                       id="daily_running_water" placeholder="请输入你的日均流水"  required lay-verify="required">
                            </div>
                            <div class="form-group col-xs-6">
                                <label for="service_points" class="control-label">服务分:</label>
                                <input type="text" class="form-control" class="layui-input" name="service_points"
                                       id="service_points" placeholder="请输入你的服务分"  required lay-verify="required">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="describe_yourself" class="control-label">自我描述:</label>
                            <input type="text" class="form-control" class="layui-input" name="describe_yourself"
                                   id="describe_yourself" placeholder="请输入你的自我描述"  required lay-verify="required">
                        </div>
                        <div class="row">
                            <div class="layui-upload col-xs-8">
                                <label for="" class="control-label">封面图片:</label>
                                <button type="button" class="layui-btn" id="fileUp" style="background-color: #393D49">
                                    上传图片
                                </button>
                                <input type="hidden" name="applicationimages">
                            </div>
                            <div class=" col-xs-4" style="margin:0">
                                <img class="img-responsive" id="demo1" width="">
                                <p id="demoText"></p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="mobile" class="control-label">手机验证:</label>
                            <input type="text" class="form-control" class="layui-input" name="mobile" id="mobile"
                                   placeholder="请输入11位手机号"  required lay-verify="required|phone|number">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default"  data-dismiss="modal">关闭
                            </button>
                            <button type="button" class="btn btn-primary " lay-submit lay-filter="go" id="send-vote">提交报名</button>
                        </div>
                    </form>
                </div>


            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src=https://cdn.staticfile.org/jquery/2.1.4/jquery.min.js></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="https://cdn.staticfile.org/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <!--Layui CDN-->
    <script src="//layui.hcwl520.com.cn/layui/layui.js?v=201809030202"></script>
    <script>

    </script>


<style>
.pd_tb10-lr0{
    padding: 10px 0;
}
    .flex{
        column-count: 2; column-gap: 0;
    }
    .flex_content{
        break-inside: avoid; box-sizing: border-box; padding: 5px;border-bottom-left-radius: 5px;border-bottom-right-radius: 5px;
    }
    .btn.send_vote{
        width: 100%;

        border-bottom-left-radius: 10px;
        border-bottom-right-radius:10px;
        border-top-left-radius: 0px;
        border-top-right-radius:0px;
    }
    .thumbnail.item_box{
        margin-bottom: 0;padding:0;

        border-bottom-left-radius: 10px;
        border-bottom-right-radius:10px;
        border: none;

    }
    #img img {
        max-width: 100%;
        height: auto;
    }
    .more {
        width: 200px;
        height: 35px;
        font-size: 16px;
        font-weight: normal;
        background-color: rgb(255, 127, 36);
        color: white;
        margin: 0 auto;
        text-align: center;
        line-height: 35px;
        border-radius: 5px;
    }
    .name {
        font-weight: normal;
    }
    .fa-male {
        color:  #1296db;
    }
    .fa-female {
        color:  pink;
    }

</style>

        <form class="layui-form layui-form-pane " action="/index/index/searchPlayer" method="post">
            <div class="layui-form-item "  >

                <div class=" layui-col-xs8" style="margin-left: 0;">
                    <input type="text" name="search" required lay-verify="required" placeholder="输入编号或姓名" autocomplete="off"
                        class="layui-input" style="    border-right-style: none;">

                </div>
                <div class="layui-col-xs4">
                    <button class="layui-form-label ">搜索</button>
                </div>
            </div>

        </form>
<?php echo $_SERVER['SERVER_NAME']; ?>
        <div class="layui-row  flex" id="index-flex">
            <?php foreach($data['contestantList']['data'] as $k => $v): ?>
            <div class=" flex_content">
                <div class="thumbnail item_box" style="">
                    <a href="/index/index/playerDetails?application_id=<?php echo $v['id']; ?>" ><img src="<?php echo $config['upload']['cdnurl']; ?><?php echo $v['applicationimages']; ?>" alt="..."></a>
                    <div class="row text-center  " style="margin: 0">
                        <div class="layui-col-xs6 pd_tb10-lr0 pull-left" >
                            <h4 style="font-weight: bold" class=""><i class="fa <?php if($v['wechat_user']['sex'] == '1'): ?> fa-male <?php else: ?> fa-female <?php endif; ?>" aria-hidden="true"
                            ></i> <?php echo $v['name']; ?></h4>

                            <h4 style="padding: 5px 0 0 10px;color: #D2D2D2;" class="pull-left">
                                <i class="fa fa-line-chart" style="font-size: 14px">
                            </i><?php echo $v['votes']; ?>票</h4>
                        </div>

                        <div class="layui-col-xs12 voting">

                            <a href="javascript:void(0)" class="btn btn-info send_vote" style="">投票给<strong style="font-size: 15px" class="text-danger application_id"><?php echo $v['id']; ?></strong>号</a>

                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

        </div>
<?php echo $page; ?>


<div class="text-center" style="padding: 10px 0 80px 0;">
    © 成都君忆汽车服务有限公司. All Rights Reserved
</div>
</div>
<style>
   .footer_box .item.active_s {
        background-color: #5bc0de;
    }
</style>
<div class="text-center footer_box">
    <div class="layui-row row_style" id="title">
        <div class="layui-col-xs4 item <?php if(\think\Request::instance()->action() == 'index'): ?> active_s <?php endif; ?>" id="vote">全部参赛</div>
        <div class="layui-col-xs4 item <?php if(\think\Request::instance()->action() == 'ranking'): ?> active_s <?php endif; ?>" id="ranking">当前排名</div>
        <div class="layui-col-xs4 item <?php if(\think\Request::instance()->action() == 'rules'): ?> active_s <?php endif; ?>" id="rules">活动规则</div>

    </div>
</div>


<!-- jQuery -->
<script src=https://cdn.staticfile.org/jquery/2.1.4/jquery.min.js></script>

<!-- Bootstrap Core JavaScript -->
<script src="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>

<!-- Plugin JavaScript -->
<script src="https://cdn.staticfile.org/jquery-easing/1.4.1/jquery.easing.min.js"></script>
<!--Layui CDN-->
<script src="//layui.hcwl520.com.cn/layui/layui.js?v=201809030202"></script>
<!--瀑布流插件-->
<!--<script src="https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.js"></script>-->
<!--<script src="https://static.yc.junyiqiche.com/layer/layer.js"></script>-->

<script src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js" type="text/javascript" charset="utf-8"></script>
<script>
    window.onload = function () {
        var ajaxurl = '<?php echo url("index/sharedata"); ?>';
        var urll = location.href.split('#')[0];
        $.ajax({
            url: ajaxurl,
            type: "post",
            data: {urll: urll},
            dataType: "json",
            success: function (s) {
                f = $.parseJSON(s);
                wx.config({
                    debug: false, //分享成功后可以关闭 false
                    appId: f.appid,
                    timestamp: f.timestamp,
                    nonceStr: f.nonceStr,
                    signature: f.signature,
                    jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage']
                });
                wx.ready(function () {
                    wx.onMenuShareTimeline({
                        title: '<?php echo $share_title; ?>', // 分享标题
                        link: s.url, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                        imgUrl: "<?php echo $share_img; ?>", // 分享图标 使用绝对路径
                        success: function () {

                        }
                    });
                    wx.onMenuShareAppMessage({
                        title: '<?php echo $share_title; ?>',
                        desc: "<?php echo $share_body; ?>", // 分享描述
                        link: s.url, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                        imgUrl: "<?php echo $share_img; ?>", // 分享图标 使用绝对路径
                        type: '', // 分享类型,music、video或link，不填默认为link
                        dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                        success: function () {

                        }
                    });
                });

            },
            error: function () {
                console.log("通信失败");
            }
        });
    }




    layui.use(['carousel', 'form', 'flow', 'upload', 'element'], function () {
        var $ = layui.jquery;

        var flow = layui.flow;
        //当你执行这样一个方法时，即对页面中的全部带有lay-src的img元素开启了懒加载（当然你也可以指定相关img）

        var carousel = layui.carousel;
        var form = layui.form;
        var upload = layui.upload;

        flow.lazyimg();
        //普通图片上传
        var uploadInst = upload.render({
            elem: '#fileUp'
            , url: "<?php echo url('index/uploadsHeaderImg'); ?>"
          /*  , auto: false //选择文件后不自动上传
            , bindAction: '#send-vote' //指向一个按钮触发上传*/
            , choose: function (obj) {
                //预读本地文件示例，不支持ie8
                obj.preview(function (index, file, result) {
                    $('#demo1').attr('src', result); //图片链接（base64）
                });
            }
            , done: function (res) {
                console.log(res);
                //如果上传失败
                if (res.code==1) {
                    $('input[type="hidden"]').val(res.data.url);
                }
                else{
                    return layer.msg('上传失败');
                }

            }
            , error: function () {
                //演示失败状态，并实现重传
                var demoText = $('#demoText');
                demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-xs demo-reload">重试</a>');
                demoText.find('.demo-reload').on('click', function () {
                    uploadInst.upload();
                });
            }
        });
        //提交报名
        form.on('submit(go)', function (data) {

            if ($('input[type="hidden"]').val() == '') {
                return layer.msg('请上传封面');
            }
            data.field.wechat_user_id = "<?php echo $user_id; ?>";
            // console.log(data.field);return;
            var index = layer.load(1, {
                shade: [0.1,'#fff'] //0.1透明度的白色背景
            });
            $.post("<?php echo url('Index/sendVote'); ?>",{datas:data.field},function (res) {
                console.log(res.code);
                if(res.code==1){
                    layer.close(index);
                    layer.msg(res.msg);
                    window.location.reload();
                }else{
                    layer.msg(res.msg);
                    layer.close(index);

                }
            })
            return false;
        });
        //建造实例
        carousel.render({
            elem: '#test1'
            , width: '100%' //设置容器宽度
            , height: '200px'
            , arrow: 'none' //始终显示箭头
            , anim: 'fade' //切换动画方式
            , indicator: 'none'
        });
        //排名ID
        var count = 1;

        // flow.load({
        //     elem: '#index-flex' //流加载容器
        //     // , scrollElem: '#LAY_demo1' //滚动条所在元素，一般不用填，此处只是演示需要。
        //     , done: function (page, next) { //加载下一页
        //         $.get('<?php echo url("index/lazyPlayerInfo"); ?>', {page: page}, function (data) {
        //             data = JSON.parse(data);
        //             // console.log(data);
        //
        //             if(data.length==0){
        //                 layer.msg('没有更多了');
        //             }
        //
        //
        //             //模拟插入
        //             setTimeout(function () {
        //                 var lis = [];
        //
        //                 for (var i in data){
        //                     var sex = data[i].wechat_user.sex==1?'fa-male':'fa-female';
        //                     lis.push('<div class=" flex_content">\n' +
        //                         '                <div class="thumbnail item_box" style="">\n' +
        //                         '                    <a href="/index/index/playerDetails?application_id='+data[i].id+'" ><img src="<?php echo $config['upload']['cdnurl']; ?>/'+data[i].applicationimages+'" alt="..."></a>\n' +
        //                         '                    <div class="row text-center  " style="margin: 0">\n' +
        //                         '                        <div class="layui-col-xs6 pd_tb10-lr0 pull-left" >\n' +
        //                         '                            <h4 style="font-weight: bold" class=""><i class="fa '+sex+'" aria-hidden="true"\n' +
        //                         '                                                                    ></i> '+data[i].name+'</h4>\n' +
        //                         '\n' +
        //                         '                            <h4 style="font-weight: bold;padding-top: 5px" class=""><i class="fa fa-line-chart"\n' +
        //                         '                                                                                    aria-hidden="true"></i>'+data[i].votes+' 票</h4>\n' +
        //                         '                        </div>\n' +
        //                         '\n' +
        //                         '                        <div class="layui-col-xs12 voting">\n' +
        //                         '                            <a href="javascript:void(0)" class="btn btn-info send_vote" style="">投票给<strong style="font-size: 15px" class="text-danger application_id">'+data[i].id+'</strong>号</a>\n' +
        //                         '                        </div>\n' +
        //                         '                    </div>\n' +
        //                         '                </div>\n' +
        //                         '            </div>');
        //                     // console.log($('.fa-fa').length);
        //                 }
        //
        //
        //                 next(lis.join(''), page < 6); //假设总页数为 6
        //             }, 500);
        //
        //         })
        //     }
        // });

        flow.load({
            elem: '.user-list' //流加载容器
            // , scrollElem: '#LAY_demo1' //滚动条所在元素，一般不用填，此处只是演示需要。
            , done: function (page, next) { //加载下一页


                $.ajax({
                    dataType: 'json',
                    type: 'POST',
                    url: '<?php echo url("index/rankingMore"); ?>',
                    data: {
                        page: page
                    },
                    success: function (msg) {
                        msg = JSON.parse(msg);
                        // console.log(msg);

                        if (msg.length == 0) {
                            layer.msg('没有更多了');
                        }
                        
                        //模拟插入
                        setTimeout(function () {
                            var lis = [];
                            
                            for (var i = 0; i < msg.length; i++) {
                                var sex = "four";
                                if (count == 1) {
                                    sex = "first";
                                }
                                if (count == 2) {
                                    sex = "second";
                                }
                                if (count == 3) {
                                    sex = "third";
                                }
                                lis.push('<div class="user '+sex+'">\n' +
                                    '            <span class="rank ">' + count + '</span><p class="username">' + msg[i].name + '</p> \n' +
                                    '            <span class="coupons">' + msg[i].votes + '票</span>\n' +
                                    '        </div>');
                                count++;
                            }
                            next(lis.join(''), page < 6); //假设总页数为 6
                        }, 500);
                    }
                });

            }
        });

    });

    document.getElementById('vote').onclick = function () {
        window.location = '/';
    };
    document.getElementById('ranking').onclick = function () {
        window.location = '/index/index/ranking';
    };
    document.getElementById('rules').onclick = function () {
        window.location = '/index/index/rules';
    };


    var isTodayVote = "<?php echo $data['isTodayVote']; ?>";
    if (isTodayVote == 0) {
        $(".voting").each(function(i){
            if($(this).hasClass('asd')){
                $(this).removeClass('asd');
            }
        })
    }

    //投票
    $(".voting").each(function(i){
        // alert(3);
        $(this).bind("click", {index: i}, function(){

            if($(this).hasClass('asd')){
                layer.msg("今天已经投过票啦，请明天再来");
                return;
            }

            // console.log(isTodayVote);
            if (isTodayVote == 1) {
                layer.msg("今天已经投过票啦，请明天再来");
                return;
            }
            // console.log(123);
            var ajaxurl ='<?php echo url("index/vote"); ?>';
            var application_id = $('.application_id').eq(i).text();
            var user_id = "<?php echo $user_id; ?>";
            console.log(application_id);
            console.log(user_id);
            $.ajax({
                url: ajaxurl,
                type: "post",
                //注意序列化的值一定要放在最前面,并且不需要头部变量,不然获取的值得格式会有问题
                data:{application_id: application_id, user_id: user_id},
                dataType:"json",
                success:function (data) {

                    layer.msg(data);
                    
                    // window.location.reload(); //刷新当前页面
                    $('.votes').html("票数是：" + "<?php echo $data['playerDetail']['votes'] + 1; ?>" + "票");

                    $(".voting").each(function(i){
                        $(this).addClass('asd');
                    });

                },
                error:function (data) {
                    // alert(data);
                    layer.msg(data);
                }
            })
        })
    });

</script>
</body>

</html>