/* -------------------------------------------------------------------------------- /
	
	Magentech jQuery
	Created by Magentech
	v1.0 - 20.9.2016
	All rights reserved.
	
/ -------------------------------------------------------------------------------- */


	// Cart add remove functions
	var cart = {
		'add': function(product_id,quantity=1) {
			$.post(
				root_url+"/home/cart/navAddtocart",
				{goods_id:product_id,quantity:quantity},
				function(res){
					if(!res.error){
                        addProductNotice('已加入购物车',
                            '<img src='+root_url+res.thumb_img+' alt="">',
                            '<h3><a href='+root_url+'/home/goods/goods/goods_id/'+product_id+ '>'+res.goods_name+'</a>' +
							' 已加入 <a href='+root_url+'/home/cart/cart>购物车</a>!</h3>',
                            'success');
					}else{
                        alert(res.msg);
					}
				},"json");
		}
	};

	var wishlist = {
		'add': function(product_id) {
            $.post(
                root_url+"/home/wish/navAddtowish",
                {goods_id:product_id},
                function(res){
                    if(!res.error){
                        if(res.login){
                            if(res.exist) {
                                addProductNotice('此商品心愿单中已存在',
                                    '<img src='+root_url+res.thumb_img+'>',
                                    '<h3><a href='+root_url+'/home/goods/goods/goods_id/'+product_id+ '>'+res.goods_name+'</a>'+
                                    ' 点击查看 <a href='+root_url+'/home/wish/index>心愿单</a>!</h3>',
                                    'success');
                            }else{
                            	addProductNotice('已加入心愿单',
                                    '<img src='+root_url+res.thumb_img+'>',
                                    '<h3><a href='+root_url+'/home/goods/goods/goods_id/'+product_id+ '>'+res.goods_name+'</a>' +
                                    ' 已加入 <a href='+root_url+'/home/wish/index>心愿单</a>!</h3>',
                                    'success');
                            }
                        }else {
                            //'<h3>You must <a href="#">login</a>  to save <a href="#">Apple Cinema 30"</a> to your <a href="#">wish list</a>!</h3>', 'success');
                            $.post(
                                root_url + "/home/user/logindialog",
                                {act: "login"},
                                function (res) {
                                    if (res.error === "0") {
                                        $('body').append(res.content);
                                        $('.ui-dialog-title>span').text('你必须要登录才能加入心愿单');
                                    }
                                }, "json");
                        }
                    }else{
                    	alert(res.msg);
                    }
                },"json");
		},
		'del': function(product_id) {
			$.post(
				root_url+"/home/wish/del",
				{goods_id:product_id},
				function(res){
					if(!res.error){
                        if(res.login){
                             $('#'+product_id).remove();
                        }else{
                            $.post(
                                root_url + "/home/user/logindialog",
                                {act: "login"},
                                function (res) {
                                    if (res.error === "0") {
                                        $('body').append(res.content);
                                        $('.ui-dialog-title>span').text('你必须要登录才能继续操作');
                                    }
                                }, "json");
						}

					}else{
						alert(res.msg);
					}
				},"json");
		}
	};

	var compare = {
		'add': function(product_id,product_name) {
			addProductNotice('商品已加入对比列表', '<img src="image/demo/shop/product/e11.jpg" alt="">', '<h3>成功: 您已把商品 <a href='+root_url+'/home/goods/goods/goods_id/'+product_id+'>'+product_name+'</a> 加入您的 <a href="#">对比列表</a>!</h3>', 'success');
		}
	};

	/* ---------------------------------------------------
		jGrowl – jQuery alerts and message box
	-------------------------------------------------- */
	function addProductNotice(title, thumb, text, type) {
		$.jGrowl.defaults.closer = false;
		//Stop jGrowl
		//$.jGrowl.defaults.sticky = true;
		var tpl = thumb + '<h3>'+text+'</h3>';
		$.jGrowl(tpl, {		
			life: 4000,
			header: title,
			speed: 'slow',
			theme: type
		});
	}

//关弹出登录窗
function remove_ui_dialog(){
    $('.ui-dialog').remove();
}
