/**
 * Created by gerrard on 2017/11/2.
 */
m.controller('ctrl2', function ($scope,cartNav) {
    $scope.root_url = root_url;
    cartNav.getCart().then(function(res){
        $scope.item = res.data[0];
        $scope.amount = res.data.amount;
        $scope.total = res.data.total;
        $scope.login = res.data.login;
    });
});

m.factory('cartNav', function($http){
    return {
        getCart: function(){
            return $http({
                method:'post',
                url:root_url+'/Home/cart/cart_nav',
                cache:true
            })
        },
        removeItem: function(id){
            return $http({
                method:'post',
                url:root_url+'/Home/cart/navDel',
                data:$.param({goods_id:id}),
                headers:{'Content-type':'application/x-www-form-urlencoded'},
            })
        },
        checkout: function() {
            return $http({
                method:'post',
                url:root_url+'/Home/order/navCheckout',
                headers:{'Content-type':'application/x-www-form-urlencoded'},
            })
        }
    };
});

m.directive('navCart', function(cartNav){
    return {
        restrict: 'E',
        /*replace: true,*/
        scope: {
            amount: '=',
            total: '@',
            item: '=',
            root_url: '@rootUrl',
            login: '@'
        },
        /*transclude: true,*/
        templateUrl: root_url + '/Home/View/directive/cart_nav.html',
        link: function($scope, elem, attr){
            var being = true;

            $(elem).delegate('#cart', 'mouseenter', function () {
                //防mouseenter不停调方法
                if (!being) return;
                being = false;
                setData();
                $('.shoppingcart-box',elem).show();
                if($('.table-striped').height() > 350){
                    $('.shoppingcart-box>li:first').height('350px');
                    $('.shoppingcart-box li').eq(0).css({'overflow':'auto','margin-right':'0'});
                }
            });

            $(elem).delegate('#cart', 'mouseleave', function () {
                being = true;
                $('.shoppingcart-box',elem).hide();
            });

            $(elem).delegate('a.fa-delete', 'click', function() {
                cartNav.removeItem($(this).data('id')).then(function(res){
                    if(res.data == 'done'){
                        setData();
                    }
                });
            });

            function setData(){
                cartNav.getCart().then(function(res){
                    $scope.item = res.data[0];
                    $scope.amount = res.data.amount;
                    $scope.total = res.data.total;
                });
            }

            $scope.check = function(){
                if($scope.login == '1'){
                    var num="";
                    for(var i=0;i<4;i++){
                        num+=Math.floor(Math.random()*10)
                    }
                    var rid = Math.round(new Date().getTime()/1000) + num;
                    location.href= $scope.root_url +"/Home/Order/checkout?rid="+rid;
                }else{
                    $.post(
                        root_url + "/home/user/logindialog",
                        function (res) {
                            $('body').append(res);
                            $('.ui-dialog-title>span').text('你必须要登录才能结算');
                        }, "json");
                }
            }

        }
    }
});