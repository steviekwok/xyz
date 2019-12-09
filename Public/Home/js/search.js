/* ---------------------------------------------------
 Back to Top
 -------------------------------------------------- */
$(".back-to-top").addClass("hidden-top");
$(window).scroll(function () {
    if ($(this).scrollTop() === 0) {
        $(".back-to-top").addClass("hidden-top")
    } else {
        $(".back-to-top").removeClass("hidden-top")
    }
});

$('.back-to-top').click(function () {
    $('body,html').animate({scrollTop:0}, 1200);
    return false;
});

m.factory('goods',['$http', function($http) {
    return {
        getGoods: function (keyword) {
            return $http({
                method: 'post',
                url: root_url + '/Home/search/search',
                data:$.param({keyword:keyword}),
                headers:{'Content-type':'application/x-www-form-urlencoded'},
            })
        },
    }
}]);

m.directive('search', ['goods', function (goods) {
    return {
        scope: {
            root_url: '@rootUrl'
        },
        restrict: 'E',
        templateUrl: root_url + '/Home/View/directive/search.html',
        link: function ($scope) {
            //$scope.root_url = root_url;
            var selectListIndex;// 数据列表索引
            $scope.method = {};// 模板方法对象
            $('#forhide').width($('.autosearch-input').width()+24);

            $scope.submitSearch = function () {
                window.location.href = root_url+"/Home/search/index?keyword="+$scope.inputModel;
            };

            $scope.method.searchData = function () { // 根据关键字搜索联想词
                if (!$scope.inputModel) {// 为空时不做搜索
                    $scope.dataList = [];
                    return;
                }
                if ($scope.inputModel.length > 0) {// 非空时异步请求联想词
                    goods.getGoods($scope.inputModel).then(function (data) {
                        $scope.dataList = data.data;// 请求回来的数据赋值给视图对象中的dataList数组
                        selectListIndex = -1;// 索引置为-1，不选中任何项
                        $('#forhide').show();// 显示列表
                    });
                }
            };

            $scope.method.keyDownEvent = function ($event) {
                $event.stopPropagation();// 阻止其他按键事件冒泡
                var listArry = $('#forhide>li');
                switch ($event.keyCode) {
                    case 38:// 'UP'
                        selectListIndex--;
                        if (selectListIndex >= 0 && selectListIndex < listArry.length) {
                            for (var i = 0; i < listArry.length; i++) {
                                listArry.eq(i).removeClass('selected');// 清除选中样式
                                if (i === selectListIndex) {
                                    listArry.eq(i).addClass('selected');// 添加选中样式
                                    $scope.inputModel = listArry.eq(i).text();// 将选中项的文本赋值给输入框
                                }
                            }
                        } else if (selectListIndex < 0) {// 第0项跳转到最后一项
                            selectListIndex = listArry.length - 1;
                            listArry.eq(0).removeClass('selected');
                            listArry.eq(selectListIndex).addClass('selected');
                            $scope.inputModel = listArry.eq(selectListIndex).text();
                        }
                        break;
                    case 40:// 'Down'
                        selectListIndex++;
                        if (selectListIndex >= 0 && selectListIndex < listArry.length) {
                            for (var i = 0; i < listArry.length; i++) {
                                listArry.eq(i).removeClass('selected');
                                if (i === selectListIndex) {
                                    listArry.eq(i).addClass('selected');
                                    $scope.inputModel = listArry.eq(i).text();
                                }
                            }
                        } else if (selectListIndex >= listArry.length) {// 最后一项跳转到第0项
                            selectListIndex = 0;
                            listArry.eq(listArry.length - 1).removeClass('selected');
                            listArry.eq(selectListIndex).addClass('selected');
                            $scope.inputModel = listArry.eq(selectListIndex).text();
                        }
                        break;
                }
            };

            // 鼠标悬停事件：获取悬停数据项的索引，并添加选中样式
            $scope.method.mouseOverEvent = function (index) {
                selectListIndex = index;
                var listArry = $('#forhide>li');
                for (var i = 0; i < listArry.length; i++) {
                    listArry.eq(i).removeClass('selected');
                    if (i === selectListIndex) {
                        listArry.eq(i).addClass('selected');
                    }
                }
            };

            $scope.method.mouseDownEvent = function ($event){
                if($event.button == 0) {//鼠标左键
                    var listArry = $('#forhide>li');
                    $scope.inputModel = listArry.eq(selectListIndex).text();
                    $scope.submitSearch();
                }
            };

            $scope.method.closeSearch = function () {// 关闭联想词列表
                //$scope.dataList = [];
                $('#forhide').hide();
            };

            $(window).resize(function () {
                $('#forhide').width($('.autosearch-input').width()+24);
            });
        }
    };
}]);