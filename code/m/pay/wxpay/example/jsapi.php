﻿<?php 
ini_set('date.timezone','Asia/Shanghai');
session_start();
//error_reporting(E_ERROR);
require_once "../lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";
require_once 'log.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Mysql.php';

//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

//打印输出数组信息
function printf_info($data)
{
    foreach($data as $key=>$value){
        echo "<font color='#00ff55;'>$key</font> : $value <br/>";
    }
}

//①、获取用户openid
$tools = new JsApiPay();
$openId = $tools->GetOpenid();
printf_info(["openId"=>$openId]);



//微信订单号
$order_sn_shop = WxPayConfig::MCHID.date("YmdHis");

//更新数据表 微信订单号
$db = new Mysql('106.15.183.147','root','Ryb123456','utf8','tickets');

$result = $db->update(array('order_sn_shop'=>$order_sn_shop),'order_info','order_id = '.$_SESSION["data"]['order_id']);
//②、统一下单
$input = new WxPayUnifiedOrder();
$input->SetBody("test");
$input->SetAttach("test");
$input->SetOut_trade_no($order_sn_shop);
$input->SetTotal_fee($_SESSION["data"]["Total_fee"]);
$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetGoods_tag("test");
$input->SetNotify_url("https://m.kxschool.com/pay/wxpay/example/notify.php");
$input->SetTrade_type("JSAPI");
$input->SetOpenid($openId);
$order = WxPayApi::unifiedOrder($input);
echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
printf_info($order);
$jsApiParameters = $tools->GetJsApiParameters($order);

//获取共享收货地址js函数参数
$editAddress = $tools->GetEditAddressParameters();

//③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
/**
 * 注意：
 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
 */
?>

<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/> 
    <title>微信支付样例-支付</title>
    <script type="text/javascript">
	//调用微信JS api 支付
	function jsApiCall()
	{
		WeixinJSBridge.invoke(
			'getBrandWCPayRequest',
			<?php echo $jsApiParameters; ?>,
			function(res){
				WeixinJSBridge.log(res.err_msg);
				//alert(res.err_code+res.err_desc+res.err_msg);


                WeixinJSBridge.log(res.err_msg);
                if(res.err_msg == "get_brand_wcpay_request:ok"){
                    //alert(res.err_code+res.err_desc+res.err_msg);
                    window.location.href="https://m.kxschool.com/order/index";
                }else{
                    //返回跳转到订单详情页面
                    alert('支付失败');
                    window.location.href="https://m.kxschool.com/order/index";

                }


			}
		);
	}

	function callpay()
	{

		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
		        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
		    }
		}else{
		    jsApiCall();
		}
	}
	</script>
	<script type="text/javascript">
	//获取共享地址
	function editAddress()
	{
		WeixinJSBridge.invoke(
			'editAddress',
			<?php echo $editAddress; ?>,
			function(res){
				var value1 = res.proviceFirstStageName;
				var value2 = res.addressCitySecondStageName;
				var value3 = res.addressCountiesThirdStageName;
				var value4 = res.addressDetailInfo;
				var tel = res.telNumber;
				
				alert(value1 + value2 + value3 + value4 + ":" + tel);
			}
		);
	}
	
	//window.onload = function(){
	//	if (typeof WeixinJSBridge == "undefined"){
	//	    if( document.addEventListener ){
	//	        document.addEventListener('WeixinJSBridgeReady', editAddress, false);
	//	    }else if (document.attachEvent){
	//	        document.attachEvent('WeixinJSBridgeReady', editAddress); 
	//	        document.attachEvent('onWeixinJSBridgeReady', editAddress);
	//	    }
	//	}else{
	//		editAddress();
	//	}
	//};
	callpay();
	</script>
</head>
<body style="display: none;">
    <br/>
    <font color="#9ACD32"><b>该笔订单支付金额为<span style="color:#f00;font-size:50px"><?php echo $_SESSION["data"]["Total_fee"]/100; ?>元</span>钱</b></font><br/><br/>
	<div align="center">
		<button style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" >立即支付</button>
	</div>
</body>
</html>