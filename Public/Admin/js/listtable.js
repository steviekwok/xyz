/* $Id: listtable.js 14980 2008-10-22 05:01:19Z testyang $ */

//window.console = window.console || {};
//console.log || (console.log = opera.postError);
if (typeof Ajax != 'object')
{
    alert('Ajax object doesn\'t exists.');
}

if (typeof Utils != 'object')
{
    alert('Utils object doesn\'t exists.');
}

$(document).ready(function(){
    $(document).on('click','.first',function() {
        var args = "act="+listTable.query+listTable.compileFilter();
        var url2 = root_url + "/index.php/Admin/goods/goodslist";
        Ajax.call(url2, args, listTable.listCallback, "POST", "JSON");

        return false;
    });
    $(document).on('click','.end',function() {
        var args = "act="+listTable.query+listTable.compileFilter();
        var url2 = root_url + "/index.php/Admin/goods/goodslist/"+$(this).attr('href').substring(($(this).attr('href').lastIndexOf("p")) , $(this).attr('href').lastIndexOf("."));
        Ajax.call(url2, args, listTable.listCallback, "POST", "JSON");

        return false;
    });
    $(document).on('click','.prev',function() {
        var args = "act="+listTable.query+listTable.compileFilter();
        var url2 = root_url + "/index.php/Admin/goods/goodslist/"+$(this).attr('href').substring(($(this).attr('href').lastIndexOf("p")) , $(this).attr('href').lastIndexOf("."));
        Ajax.call(url2, args, listTable.listCallback, "POST", "JSON");
        return false;
    });
    $(document).on('click','.next',function() {
        var args = "act="+listTable.query+listTable.compileFilter();
        var url2 = root_url + "/index.php/Admin/goods/goodslist/"+$(this).attr('href').substring(($(this).attr('href').lastIndexOf("p")) , $(this).attr('href').lastIndexOf("."));
        //console.log(url2);
        Ajax.call(url2, args, listTable.listCallback, "POST", "JSON");
        return false;
    });
    $('body').on('click','.num',function() {
        var args = "act="+listTable.query+listTable.compileFilter();
        var url2 = root_url + "/index.php/Admin/goods/goodslist/"+$(this).attr('href').substring(($(this).attr('href').lastIndexOf("p")) , $(this).attr('href').lastIndexOf("."));

        Ajax.call(url2, args, listTable.listCallback, "POST", "JSON");
        return false;
    });
});
var listTable = new Object;

listTable.query = "query";
listTable.filter = new Object;
listTable.url = location.href.indexOf('goods')=='-1'?root_url + "/index.php/Admin/cat/aajax":root_url + "/index.php/Admin/goods/aajax";

/**
 * 创建一个可编辑区
 */
listTable.edit = function(obj, act, id)
{
    var tag = obj.firstChild.tagName;

    if (typeof(tag) != "undefined" && tag.toLowerCase() == "input")
    {
        return;
    }

    /* 保存原始的内容 */
    var org = obj.innerHTML;
    var val = Browser.isIE ? obj.innerText : obj.textContent;

    /* 创建一个输入框 */
    var txt = document.createElement("INPUT");
    txt.value = (val == 'N/A') ? '' : val;
    txt.style.width = (obj.offsetWidth + 12) + "px" ;

    /* 隐藏对象中的内容，并将输入框加入到对象中 */
    obj.innerHTML = "";
    obj.appendChild(txt);
    txt.focus();

    /* 编辑区输入事件处理函数 */
    txt.onkeypress = function(e)
    {
        var evt = Utils.fixEvent(e);
        var obj = Utils.srcElement(e);

        if (evt.keyCode == 13)
        {
            obj.blur();

            return false;
        }

        if (evt.keyCode == 27)
        {
            obj.parentNode.innerHTML = org;
        }
    }

    /* 编辑区失去焦点的处理函数 */
    txt.onblur = function(e)
    {
        if (Utils.trim(txt.value).length > 0 && Utils.trim(txt.value)!=org)
        {
            res = Ajax.call(listTable.url, "act="+act+"&val=" + encodeURIComponent(Utils.trim(txt.value)) + "&id=" +id, null, "POST", "JSON", false);
            if (res.message)
            {
                alert(res.message);
            }

            if(res.id && (res.act == 'goods_auto' || res.act == 'article_auto'))
            {
                document.getElementById('del'+res.id).innerHTML = "<a href=\""+ thisfile +"?goods_id="+ res.id +"&act=del\" onclick=\"return confirm('"+deleteck+"');\">"+deleteid+"</a>";
            }

            obj.innerHTML = (res.error == 0) ? res.content : org;
        }
        else
        {
            obj.innerHTML = org;
        }
    }
};

/**
 * 切换状态
 */
listTable.toggle = function(obj, act, id)
{
    var val = (obj.src.match(/yes.gif/i)) ? 0 : 1;
    var res = Ajax.call(this.url, "act="+act+"&val=" + val + "&id=" +id, null, "POST", "JSON", false);

    if (res.message)
    {
        alert(res.message);
    }
    if (res.error == 0)
    {
        obj.src = (res.content > 0) ? root_url+'/Public/Admin/images/yes.gif' : root_url+'/Public/Admin/images/no.gif';
    }
};

/**
 * 切换排序方式
 */
listTable.sort = function(sort_by)
{
    var args = "act="+this.query + "&sort_by="+sort_by+"&sort_order=";
    if(this.page)
    {
        var url2 = root_url + "/index.php/Admin/goods/goodslist/p/" + this.page;
    }
    else
    {
        var url2 = root_url + "/index.php/Admin/goods/goodslist";
    }

    //console.log(url2);
    if (this.filter.sort_by == sort_by)
    {
        args += this.filter.sort_order == "DESC" ? "ASC" : "DESC";
    }
    else
    {
        args += "DESC";
    }
    for (var i in this.filter)
    {
        if (typeof(this.filter[i]) != "function" &&
            i != "sort_order" && i != "sort_by" && !Utils.isEmpty(this.filter[i]))
        {
            args += "&" + i + "=" + this.filter[i];
        }
    }
    Ajax.call(url2, args, this.listCallback, "POST", "JSON");
};

/**
 * 载入列表
 */
listTable.loadList = function()
{
    var args = "act="+this.query + this.compileFilter();
    var url2 = root_url + "/index.php/Admin/goods/goodslist";
    Ajax.call(url2, args, this.listCallback, "POST", "JSON");
};

listTable.listCallback = function(result, txt)
{
    if (result.error > 0)
    {
        alert(result.message);
    }
    else
    {
        try
        {
            document.getElementById('listDiv').innerHTML = result.content;
            if (typeof result.filter == "object")
            {
                listTable.filter = result.filter;
            }
            listTable.page = result.page;
        }
        catch (e)
        {
            alert(e.message);
        }
    }
};

listTable.selectAll = function(obj, chk)
{
    if (chk == null)
    {
        chk = 'checkboxes';
    }

    var elems = obj.form.getElementsByTagName("INPUT");

    for (var i=0; i < elems.length; i++)
    {
        if (elems[i].name == chk || elems[i].name == chk + "[]")
        {
            elems[i].checked = obj.checked;
        }
    }
};

listTable.compileFilter = function()
{
    var args = '';
    for (var i in this.filter)
    {
        if (typeof(this.filter[i]) != "function" && typeof(this.filter[i]) != "undefined")
        {
            args += "&" + i + "=" + encodeURIComponent(this.filter[i]);
        }
    }
    return args;
};