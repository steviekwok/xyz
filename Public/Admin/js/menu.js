function t_eval(CookieName)
{
    //alert(this.CookieName);
    return eval("("+ document.getCookie(CookieName) +")");
}

