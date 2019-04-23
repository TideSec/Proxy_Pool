<?php
//自动加载类库处理
function __autoload($classname)
{
	$classname = preg_replace("/[^0-9a-z_]/i", '', $classname);
	if(class_exists ( $classname ) )
	{
		return TRUE;
	}
	$classfile = $classname.'.php';
	$libclassfile = $classname.'.class.php';
	require LDINC.'/'.$libclassfile;
}

foreach(Array('_GET','_POST','_COOKIE') as $_request){
	foreach($$_request as $_k => $_v) ${$_k} = _runmagicquotes($_v);
}
function _runmagicquotes(&$svar){
	if(!get_magic_quotes_gpc()){
		if( is_array($svar) ){
			foreach($svar as $_k => $_v) $svar[$_k] = _runmagicquotes($_v);
		}else{
			$svar = addslashes($svar);
		}
	}
	return $svar;
}
function Ajaxmsg($msg)
{
	echo $msg;
	exit();
}
function AjaxJsonMsg($arr)
{
	foreach ($arr as $k=>$v)
	{
		$arr[$k] = iconv("GB2312","UTF-8",$v);
	}
	echo json_encode($arr);
	exit();
}
/***弹出信息*/
function Message($msg,$gourl=0,$onlymsg=0,$limittime=1000){
	global $sitename,$includeurl;
	$htmlhead  = "<html>\r\n<head>\r\n<title>{$sitename}提示信息</title>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\r\n";
	$htmlhead .= "<base target='_self'/>\r\n<style>div{line-height:160%;}</style></head>\r\n<body leftmargin='0' topmargin='0' bgcolor='#FFFFFF'>\r\n<center>\r\n<script>\r\n";
	$htmlfoot  = "</script>\r\n</center>\r\n</body>\r\n</html>\r\n";
	$litime = ($limittime==0 ? 1000 : $limittime);
	$func = '';
	if($gourl=='-1'){
		if($limittime==0) $litime = 1000;
		$gourl = "javascript:history.go(-1);";
	}
	if($gourl=='0'){
		if($limittime==0) $litime = 1000;
		$gourl = "javascript:history.back();";
	}
	if($gourl=='' || $onlymsg==1){
		$msg = "<script>alert(\"".str_replace("\"","“",$msg)."\");</script>";
	}else{
		if(preg_match('/close::/i',$gourl)){
			$tgobj = trim(eregi_replace('close::', '', $gourl));
			$gourl = 'javascript:;';
			$func .= "window.parent.document.getElementById('{$tgobj}').style.display='none';\r\n";
		}

		$func .= "      var pgo=0;
      function JumpUrl(){
        if(pgo==0){ location='$gourl'; pgo=1; }
      }\r\n";
		$rmsg = $func;
		$rmsg .= "document.write(\"<br /><div style='width:450px;padding:0px;border:1px solid #DADADA;'>";
		$rmsg .= "<div style='padding:6px;font-size:12px;border-bottom:1px solid #DADADA;background:#DBEEBD url({$includeurl}/images/wbg.gif)';'><b>{$sitename} 提示信息！</b></div>\");\r\n";
		$rmsg .= "document.write(\"<div style='padding-bottom:20px;font-size:10pt;background:#ffffff'><br />\");\r\n";
		$rmsg .= "document.write(\"".str_replace("\"","“",$msg)."\");\r\n";
		$rmsg .= "document.write(\"";

		if($onlymsg==0){
			if( $gourl != 'javascript:;' && $gourl != ''){
				$rmsg .= "<br /><a href='{$gourl}'>如果你的浏览器没反应，请点击这里...</a>";
				$rmsg .= "</div>\");\r\n";
				$rmsg .= "setTimeout('JumpUrl()',$litime);";
			}else{
				$rmsg .= "</div>\");\r\n";
			}
		}else{
			$rmsg .= "<br/></div>\");\r\n";
		}
		$msg  = $htmlhead.$rmsg.$htmlfoot;
	}
	echo $msg;
	exit();
}

/**
 * 弹出JS对话框 $msg指弹出内容,$url是跳转页面，如果为0的情况则返回上一级目录
*/
function Alert($msg,$url="0")
{
	if ($url =="0") {
		$url = "history.go(-1)";
	}
	else{
		$url = "window.location.href = '$url'";
	}
	echo "<script language='javascript'>alert('$msg');$url;</script>";
	exit();
}

/**获取IP地址*/
function ld_ipaddress()
{
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
	$ip = getenv("HTTP_CLIENT_IP");
	else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
	$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
	$ip = getenv("REMOTE_ADDR");
	else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
	$ip = $_SERVER['REMOTE_ADDR'];
	else
	$ip = "unknown";
	return($ip);
}

/**
 * 数字分页
 * */
function Page($num = '',$url = '',$pagesize = 20,$ishtml=0)
{
	global $page,$pagesql,$pagenav,$includeurl,$cfg;        //定义三个全局变量，$page是页码，$pagesql是SQL语句里面的imit,$pagenav是分页的连接
	$lastpage = ceil(($num/$pagesize)); //末页
	if($page >= $lastpage) $page = $lastpage;//如果页码大于等于总共页数，那么页码就等于总共页数
	if($page =="" or $page<=0) $page =1;   //如果page为空又或者page小于等于0时则page等于1
	$prepg=$page-1; 				   //上一页
	$nextpg=$page+1;                   //下一页
	$pagesql = ($page-1)*$pagesize;        //计算SQL语句
	$GLOBALS["pagesize"]=$pagesize;        //为使函数外部可以访问这里的“$displaypg”，将它也设为全局变量。注意一个变量重新定义为全局变量后，原值被覆盖，所以这里给它重新赋值。
	$pagenum = 10;		//每个显示多少条
	if($ishtml==0)
	{
		$pagenavurl = "{$url}page=1";
		$pageurl1 = "{$url}page=$prepg";
		$nextpageurl = "{$url}page=$nextpg";
		$lastpageurl = "{$url}page=$lastpage";
	}else
	{
		$pagenavurl = "{$url}-1.html";
		$pageurl1 = "{$url}-$prepg.html";
		$nextpageurl = "{$url}-$nextpg.html";
		$lastpageurl = "{$url}-$lastpage.html";
	}
	$pagenav = "<div class='page'><ul>";
	if ($page > 1)
	{
		$pagenav .= "<li><a href='{$pagenavurl}'>首页</a></li>";
		$pagenav .= "<li><a href='{$pageurl1}'>上一页</a></li>";
	}
	$dqpage = floor($page / $pagenum);		//当前多少页,除以10
	$beginpage = $dqpage * $pagenum;
	$endpage = ($dqpage + 1) * $pagenum;			//结束的页号
	for ($i=$beginpage;$i<=$endpage;$i++)
	{
		if($i==0) continue;
		$ss = $i == $page ? " class='selected'" : "";
		$iurl = $ishtml==0 ? "{$url}page=$i" : "{$url}-$i.html";
		$pagenav .= "<li $ss><a href='{$iurl}'>{$i}</a></li>";
		if($i >= $lastpage) break;
	}
	if($page < $lastpage)
	{
		//$pagenav .= "<li style='border:none;margin-left:0px'>...</li>";
		//$pagenav .= "<li style='margin-left:0px'><a href='{$lastpageurl}'>{$lastpage}</a></li>";
		$pagenav .= "<li><a href='{$nextpageurl}'>下一页</a></li>";
	}
	$pagenav .= "</ul></div>";
}
/**
 * 获取某个表中的某个最大的值
 */
function ld_gettablemax($table,$field="ord")
{
	global $db;
	$i = $db->listtablezd($table,"max({$field})");
	return $i+1;
}

/**
 * 选择时间格式
 *
 * 1返回Y-m-d H:i:s
 * 2返回Y-m-d
 */
function ld_select_date($date,$type=1)
{
	if(!empty($date))
	{
		if($type==1)
		return date('Y-m-d H:i:s',$date);
		else if($type==2)
		return date('Y-m-d',$date);
		else if($type==3)
		return date('Y-m',$date);
		else if($type==4)
		return date('Y年m月d日',$date);
		else if($type==5)
		return date('m-d',$date);
	}
	else {
		return "";
	}

}

/**
  * 清除所有HTML
  */
function ld_clearhtml($str,$len) {
	$str=eregi_replace("<\/*[^<>]*>", '', $str);
	$str=str_replace(" ", '', $str);
	$str=str_replace("::", ':', $str);
	$str=str_replace(" ", '', $str);
	$str=str_replace("#p#", '', $str);
	$str=str_replace("　", '', $str);
	$str=str_replace("　", '', $str);
	$str=str_replace("&nbsp;", '', $str);
	$str=str_replace("&ldquo;", '"', $str);
	$str=str_replace("&rdquo;", '"', $str);
	$str=str_replace("&mdash;", '-', $str);
	$str = ereg_replace("\t","",$str);
	$str = ereg_replace("\r\n","",$str);
	$str = ereg_replace("\r","",$str);
	$str = ereg_replace("\n","",$str);
	$str = ereg_replace(" "," ",$str);
	$str = ereg_replace("&hellip;","",$str);
	$str = GBsubstr($str,0,$len);
	return $str;
}

/*
*P中文字串截取无乱码
*/

function GBsubstr($str, $start, $len) { // $str指字符串,$start指字符串的起始位置，$len指字符串长度
	$strlen = $start + $len; // 用$strlen存储字符串的总长度，即从字符串的起始位置到字符串的总长度
	for($i = $start; $i < $strlen;) {
		if (ord ( substr ( $str, $i, 1 ) ) > 0xa0) { // 如果字符串中首个字节的ASCII序数值大于0xa0,则表示汉字
			$tmpstr .= substr ( $str, $i, 3 ); // 每次取出三位字符赋给变量$tmpstr，即等于一个汉字
			$i=$i+3; // 变量自加3
		} else{
			$tmpstr .= substr ( $str, $i, 1 ); // 如果不是汉字，则每次取出一位字符赋给变量$tmpstr
			$i++;
		}
	}
	return $tmpstr; // 返回字符串
}



/**
 *获取表单复选框
 */
function ld_Getbox($str,$split=",")
{
	for ($i=0;$_POST[$str][$i]!="";$i++)//通过for循环取值
	{
		$checkbox .= $_POST[$str][$i].$split;
	}
	return $checkbox;
}
function ld_listip($ip)
{
	//IP数据文件路径
	$ipaddress = $ip;
	$dat_path = dirname(__FILE__).'/QQWry.Dat';    //检查IP地址
	if(!preg_match("/^\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3}$/", $ip)){
		return 'IP 地址错误！';
	}
	//打开IP数据文件
	if(!$fd = @fopen($dat_path, 'rb')){
		return 'IP数据文件无法读取，请确保是正确的纯真IP库！';
	}    //分解IP进行运算，得出整形数
	$ip = explode('.', $ip);
	$ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];    //获取IP数据索引开始和结束位置
	$DataBegin = fread($fd, 4);
	$DataEnd = fread($fd, 4);
	$ipbegin = implode('', unpack('L', $DataBegin)); //unpack() 函数从二进制字符串对数据进行解包。unpack(format,data) L - unsigned long (always 32 bit, machine byte order)
	#$ipbegin 值如：5386001
	if($ipbegin < 0) $ipbegin += pow(2, 32);
	$ipend = implode('', unpack('L', $DataEnd));
	if($ipend < 0) $ipend += pow(2, 32);
	$ipAllNum = ($ipend - $ipbegin) / 7 + 1;

	$BeginNum = 0;
	$EndNum = $ipAllNum;    //使用二分查找法从索引记录中搜索匹配的IP记录
	$ip1num=''; $ip2num='';   $ipAddr1='';    $ipAddr2='';
	while($ip1num>$ipNum || $ip2num<$ipNum) {
		$Middle= intval(($EndNum + $BeginNum) / 2);        //偏移指针到索引位置读取4个字节
		fseek($fd, $ipbegin + 7 * $Middle);
		$ipData1 = fread($fd, 4);
		if(strlen($ipData1) < 4) {
			fclose($fd);
			return 'System Error';
		}
		//提取出来的数据转换成长整形，如果数据是负数则加上2的32次幂
		$ip1num = implode('', unpack('L', $ipData1));
		if($ip1num < 0) $ip1num += pow(2, 32);

		//提取的长整型数大于我们IP地址则修改结束位置进行下一次循环
		if($ip1num > $ipNum) {
			$EndNum = $Middle;
			continue;
		}

		//取完上一个索引后取下一个索引
		$DataSeek = fread($fd, 3);
		if(strlen($DataSeek) < 3) {
			fclose($fd);
			return 'System Error';
		}
		$DataSeek = implode('', unpack('L', $DataSeek.chr(0)));
		fseek($fd, $DataSeek);
		$ipData2 = fread($fd, 4);
		if(strlen($ipData2) < 4) {
			fclose($fd);
			return 'System Error';
		}
		$ip2num = implode('', unpack('L', $ipData2));
		if($ip2num < 0) $ip2num += pow(2, 32);        //没找到提示未知
		if($ip2num < $ipNum) {
			if($Middle == $BeginNum) {
				fclose($fd);
				return 'Unknown';
			}
			$BeginNum = $Middle;
		}
	}    //下面的代码读晕了，没读明白，有兴趣的慢慢读
	$ipFlag = fread($fd, 1);
	if($ipFlag == chr(1)) {
		$ipSeek = fread($fd, 3);
		if(strlen($ipSeek) < 3) {
			fclose($fd);
			return 'System Error';
		}
		$ipSeek = implode('', unpack('L', $ipSeek.chr(0)));
		fseek($fd, $ipSeek);
		$ipFlag = fread($fd, 1);
	}    if($ipFlag == chr(2)) {
		$AddrSeek = fread($fd, 3);
		if(strlen($AddrSeek) < 3) {
			fclose($fd);
			return 'System Error';
		}
		$ipFlag = fread($fd, 1);
		if($ipFlag == chr(2)) {
			$AddrSeek2 = fread($fd, 3);
			if(strlen($AddrSeek2) < 3) {
				fclose($fd);
				return 'System Error';
			}
			$AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
			fseek($fd, $AddrSeek2);
		} else {
			fseek($fd, -1, SEEK_CUR);
		}        while(($char = fread($fd, 1)) != chr(0))
		$ipAddr2 .= $char;        $AddrSeek = implode('', unpack('L', $AddrSeek.chr(0)));
		fseek($fd, $AddrSeek);        while(($char = fread($fd, 1)) != chr(0))
		$ipAddr1 .= $char;
	} else {
		fseek($fd, -1, SEEK_CUR);
		while(($char = fread($fd, 1)) != chr(0))
		$ipAddr1 .= $char;        $ipFlag = fread($fd, 1);
		if($ipFlag == chr(2)) {
			$AddrSeek2 = fread($fd, 3);
			if(strlen($AddrSeek2) < 3) {
				fclose($fd);
				return 'System Error';
			}
			$AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
			fseek($fd, $AddrSeek2);
		} else {
			fseek($fd, -1, SEEK_CUR);
		}
		while(($char = fread($fd, 1)) != chr(0)){
			$ipAddr2 .= $char;
		}
	}
	fclose($fd);    //最后做相应的替换操作后返回结果
	if(preg_match('/http/i', $ipAddr2)) {
		$ipAddr2 = '';
	}
	$ipaddr = "$ipAddr1 $ipAddr2";
	$ipaddr = preg_replace('/CZ88.Net/is', '', $ipaddr);
	$ipaddr = preg_replace('/^s*/is', '', $ipaddr);
	$ipaddr = preg_replace('/s*$/is', '', $ipaddr);
	//var_dump($ipaddr);
	if(preg_match('/http/i', $ipaddr) || $ipaddr == '') {
		$ipaddr = 'Unknown';
	}
	return $ipaddress." ".iconv("GB2312","UTF-8",$ipaddr);
}

/**
 * 获取Select表单
 */
function ld_GetSelect($sql,$select="",$split)
{
	global $db;
	$query = $db->query($sql);
	while ($rs = $db->fetch_array($query))
	{
		$ss = $select==$rs[0] ? "selected" : "";
		$str .= "<option value='{$rs[0]}' $ss title='{$rs[1]}'>{$rs[1]}{$split}</option>";
	}
	return $str;
}
/**
 * 获取Select表单，数组
 */
function ld_GetSelectArr($arr,$select="",$stype=0)
{
	global $db;
	foreach ($arr as $k=>$v)
	{
		$temp = $stype==0 ? $v : $k;
		$ss = "{$select}"=="{$temp}" ? "selected" : "";
		$str .= "<option value='{$temp}' $ss title='{$v}'>{$v}</option>";
	}
	return $str;
}
function ld_GetLang()
{
	$Lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 4);
	if (preg_match('/zh-c/i',$Lang))
	{
		$Lang = '简体中文';
	}
	elseif (preg_match('/zh/i',$Lang))
	{
		$Lang = '繁體中文';
	}
	else{
		$Lang = 'English';
	}
	return $Lang;
}
function ld_GetBrowser()
{
	$Browser = $_SERVER['HTTP_USER_AGENT'];
	if (preg_match('/MSIE/i',$Browser))
	{
		$Browser = 'MSIE';
	}
	elseif (preg_match('/Firefox/i',$Browser))
	{
		$Browser = 'Firefox';
	}
	elseif (preg_match('/Chrome/i',$Browser))
	{
		$Browser = 'Chrome';
	}
	elseif (preg_match('/Safari/i',$Browser))
	{
		$Browser = 'Safari';
	}
	elseif (preg_match('/Opera/i',$Browser))
	{
		$Browser = 'Opera';
	}
	else
	{
		$Browser = 'Other';
	}
	return $Browser;
}
/**
 * 判断ID
 */
function ld_CheckID($id,$tablename,$msg="非法提交")
{
	global $db,$path;
	if(empty($id))
	{
		Alert($msg);
	}
	if(!is_numeric($id))
	{
		Alert($msg);
	}
	ld_CheckInput($id);
	if(!$db->checknumsql("select * from $tablename"))
	{
		Alert($msg);
	}
	return $id;
	unset($db);
}
function ld_CheckInput($str)
{
	// 如果不是数字则加引号
	$arr = array("\\","&gt","&lt","script","select","join","or","=","union","where","insert","delete","update","like","drop","create","modify","alert","cast","show tables");
	foreach ($arr as $k)
	{
		if(stristr("$str","$k")) Alert("非法提交");
	}
	return $str;
}

/**正则判断目录**/
function ld_Checkpath($str)
{
	$arr = array("\\","/","..",":");
	foreach ($arr as $k)
	{
		if(stristr("$str","$k")) Alert("非法提交");
	}
	return $str;
}

/**正则判断手机**/
function ld_is_mobile($str){
	return preg_match("/(^[1][3][0-9]{9}$)|(^[1][5][0-9]{9}$)|(^[1][8][0-9]{9}$)|(^[0][1-9]{1}[0-9]{9}$)/", $str);
}
/**正则判断邮箱地址**/
function ld_is_email($str){
	return preg_match("/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/", $str);
}
/**正则判断正整数**/
function ld_is_zzs($str){
	return preg_match("/^[0-9]*[1-9][0-9]*$/", $str);
}
/**正则判断整数**/
function ld_is_zs($str){
	return preg_match("/-?\\d+$/", $str);
}
/**正则判断网址**/
function ld_is_url($str){
	return preg_match("/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/", $str);
}

function get_severity($hash,$severity) {
	global $db;
	
	$sql = "SELECT * FROM target_vul where hash='{$hash}' and Severity='{$severity}' order by Severity";
	$results = $db->query($sql);
	return mysql_num_rows($results);
}
function get_vul_cn_name($id) {
    global $db;

    $sql = "SELECT name_cn FROM vul_cn where id='{$id}'";
    $results = $db->query($sql);
    $fs = $db->fetch_array($results);
    return $fs['name_cn'];
}


function get_vul_cn_id($name) {
    global $db;

    $sql = "SELECT id  FROM vul_cn where name_en='{$name}'";
    //echo $sql;

    $results = $db->query($sql);
    if (mysql_num_rows($results) > 0){
        $fs = $db->fetch_array($results);}
    else{
        $sql = "SELECT id  FROM vul_cn where name_en like '%{$name}%'";
        $fs = $db->fetch_assoc($sql);
    }

    if ($fs){
        $vul_cn_id = $fs['id'];
    }
    else{
        if (strstr($name,"WordPress")){
            $vul_cn_id = '670';
        }elseif (strstr($name,"Cross site scripting")){
            $vul_cn_id = '668';
        }else{
            $vul_cn_id = '0';
        }
    }
    //$results = $db->query($sql);
    return $vul_cn_id;
}

function specify_server() {
	global $db;
	
	$sql = "SELECT * FROM point_server order by level";
	$results = $db->fetch_assoc($sql);
	$hash = $results['hash'];
	$str = $results['pointip'].' '.$results['pointport'].' '.$results['level'];
	
	$up_arr['level'] = $results['level'] + 1;
	
	$update = $db->update("point_server",$up_arr,"hash='{$hash}'");
	
	return $results['pointip'];
}


function Checklogin($mode)
{
	if ($mode != 'login'){
		if(empty($_SESSION['username'])){
			header("Location: ?m=login");
			exit();
		}elseif( $_SESSION['r_ip'] != $_SERVER['REMOTE_ADDR'] ) {
			header("Location: ?m=login");
			exit();
		}
	}
}

function cpasswd()
{
	global $db;
	
	$username = $_SESSION['username'];
	$oldpasswd = $_POST['oldpasswd'];
	
	$up_arr['passwd'] = $_POST['newpasswd'];
	
	$update = $db->update("user",$up_arr,"username='{$username}' and passwd='{$oldpasswd}'");
	
	Ajaxmsg("密码修改成功");
}

function del()
{
	global $db;
	
	$hash = $_POST['hash'];
	
	$delete = $db->delete("scan_list where hash='{$hash}'");
	$delete = $db->delete("target_info where hash='{$hash}'");
	$delete = $db->delete("target_vul where hash='{$hash}'");
	
	Ajaxmsg("删除成功");
}

function delcustomer()
{
    global $db;

    $id = $_POST['id'];

    $delete = $db->delete("customer where id='{$id}'");

    Ajaxmsg("删除成功");
}

function delinfo()
{
    global $db;

    $id = $_POST['id'];

    $delete = $db->delete("info where hash='{$id}'");

    Ajaxmsg("删除成功");
}

function delspider()
{
    global $db;

    $id = $_POST['id'];

    $delete = $db->delete("spider where hash='{$id}'");

    Ajaxmsg("删除成功");
}

function delall()
{
    global $db;

    $id = $_POST['id'];
    $delete = $db->delete("spider where hash='{$id}'");
    $delete = $db->delete("info where hash='{$id}'");
    $delete = $db->delete("scan_list where hash='{$id}'");
    $delete = $db->delete("target_info where hash='{$id}'");
    $delete = $db->delete("target_vul where hash='{$id}'");

    Ajaxmsg("删除成功");
}

?>