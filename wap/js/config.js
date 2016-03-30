var SiteUrl = "http://www.nzc.com";
var ApiUrl = "http://www.nzc.com/mobile";
var pagesize = 10;
var WapSiteUrl = "http://www.nzc.com/wap";
var IOSSiteUrl = "https://itunes.apple.com/us/app/shopnc-b2b2c/id879996267?l=zh&ls=1&mt=8";
var AndroidSiteUrl = "http://openbox.mobilem.360.cn/index/d/sid/3166156";

// auto url detection
(function() {
    var m = /^(https?:\/\/.+)\/wap/i.exec(location.href);
    if (m && m.length > 1) {
        SiteUrl = m[1] + '/shop/index.php';
        ApiUrl = m[1] + '/mobile';
        WapSiteUrl = m[1] + '/wap';
    }
})();
