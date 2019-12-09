/**
 * Created by stevie on 2018/9/27.
 */
m.factory('arServer', function($http){
    return {
        //获取最新的收货地址条目
        ar:function (user_id) {
            return  $http({
                method:'get',
                url:root_url+'/Home/user/getAddress',
                params:{user_id:user_id},
                headers:{'Content-type':'application/x-www-form-urlencoded'}
            })
        },

        //取一条地址
        arOne:function (address_id) {
            return  $http({
                method:'get',
                url:root_url+'/Home/user/getAddressOne',
                params:{address_id:address_id},
                headers:{'Content-type':'application/x-www-form-urlencoded'}
            })
        },

        //设置某用户的默认地址
        setDt:function(address_id) {
            return $http({
                method:'get',
                url:root_url+'/Home/user/setDt',
                params:{address_id:address_id},
                headers:{'Content-type':'application/x-www-form-urlencoded'},
            })
        },

        //修改某收货地址的地址别名
        editArName:function(address_id, address_name){
            return $http({
                method:'post',
                url:root_url+'/Home/user/editArName',
                data:$.param({address_id:address_id,address_name:address_name}),
                headers:{'Content-type':'application/x-www-form-urlencoded'},
                cache:true
            })
        },

        //添加收货地址
        addAddress:function(user_id,name,sjld,address,mobile,address_name,tel="",email=""){
            return $http({
                method:'post',
                url:root_url+'/Home/user/addAddress',
                data:$.param({user_id:user_id,consignee:name,sjld:sjld,address:address,mobile:mobile,address_name:address_name,tel:tel,email:email}),
                headers:{'Content-type':'application/x-www-form-urlencoded'}
            })
        },

        //修改收货地址
        editAddress:function(address_id,user_id,name,sjld,address,mobile,address_name,tel="",email=""){
            return $http({
                method:'post',
                url:root_url+'/Home/user/editAddress',
                data:$.param({address_id:address_id,user_id:user_id,consignee:name,sjld:sjld,address:address,mobile:mobile,address_name:address_name,tel:tel,email:email}),
                headers:{'Content-type':'application/x-www-form-urlencoded'}
            })
        },

        //删除收货地址
        delAddress:function(address_id){
            return $http({
                method:'get',
                url:root_url+'/Home/user/delAddress',
                params:{address_id:address_id},
                headers:{'Content-type':'application/x-www-form-urlencoded'},
            })
        },

        //获取三级联动 下一级的名称和ID
        getRegion:function(region_type,parent_id){
            return $http({
                method:'get',
                url:root_url+'/Home/user/getRegion',
                params:{region_type:region_type,parent_id:parent_id},
                headers:{'Content-type':'application/x-www-form-urlencoded'}
            })
        }
    };
});
