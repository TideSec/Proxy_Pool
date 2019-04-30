# Proxy_Pool

Proxy_Pool，一个小巧的代理ip抓取+评估+存储+展示的一体化的工具，可自动化的搜集检测可用代理并进行评分，并添加了web展示和接口。

# 安装

1、从GitHub上脱下来，把代码放在web目录下。

```
git clone https://github.com/TideSec/Proxy_Pool

```
web服务器在unix/linux下可以用https://github.com/teddysun/lamp进行快速安装。

在windows下可以用[phpstudy](http://phpstudy.php.cn/)进行快速部署。

2、在mysql中新建数据库proxy，将proxy.sql文件导入，在include/config.inc.php中修改数据库密码。

3、此时本机访问http://ip:port，应该可以看到代理web展示界面

4、安装python2依赖库

```
pip install lxml
pip install requests
pip install pymysql
```
5、在py_proxy_task/config.py文件中配置数据库连接信息及其他参数。

# 使用

在py_proxy_task目录下有`proxy_get.py`和`proxy_check.py`两个程序，前者负责每天抓ip存进数据库，后者负责数据库中ip的清理和评估。

```bash
python proxy_get.py
# 等待上述程序抓取完结果后再运行评测程序
python proxy_check.py
```
之后按默认配置，这两个程序每天分别执行抓取和评估工作，放服务器上长期运行即可。


# 简介

原作者代码在这里：`https://github.com/chungminglu/Proxy`

我对部分代码进行了修改，完善了部分提取代理的解析代码，并加入了web展示和web接口，方便其他程序调用。

web页面我是从我的另外一个扫描器上改过来的`https://github.com/TideSec/WDScanner/`，里面可能有部分无用代码没有删除。

程序的几个功能：

1、每天从多个代理ip网站上抓下最新高匿ip数据。

2、经过筛选后的ip将存入数据库。

3、存入数据库的ip每天也要经过测试，存在剔除、评分机制，多次不合格的ip将被删除，每个ip都被评分，我们最终可以按得分排名获得稳定、低响应时间的优质ip。	
   
web展示如下图所示：
<div align=center><img src=images/001.png ></div>

web接口如下图所示：
<div align=center><img src=images/002.png ></div>

# 参数设置

在py_proxy_task/config.py文件可进行代理评估参数的设置。

```python
USELESS_TIME = 4   # 最大失效次数
SUCCESS_RATE = 0.8
TIME_OUT_PENALTY = 10  # 超时惩罚时间
CHECK_TIME_INTERVAL = 24*3600  # 每天更新一次
```
除数据库配置参数外，主要用到的几个参数说明如下：

* ```USELESS_TIME```和```SUCCESS_RATE```是配合使用的，当某个```ip```的```USELESS_TIME < 4 && SUCCESS_RATE < 0.8```时（同时兼顾到ip短期和长期的检测表现），则剔除该ip。
* ```TIME_OUT_PENALTY```， 当某个ip在某次检测时失效，而又没有达到上一条的条件时（比如检测了100次后第一次出现超时），设置一个```response_time```的惩罚项，此处为10秒。
* ```CHECK_TIME_INTERVAL```， 检测周期。此处设置为每隔12小时检测一次数据库里每一个ip的可用性。

# 策略

* 每天如下5个代理ip网站上抓下最新高匿ip数据：
  * ```mimi```
  * ```66ip```
  * ```xici```
  * ```cn-proxy```
  * ```kuaidaili```
* N轮筛选
  * 收集到的ip集合将经过N轮，间隔为t的连接测试，对于每一个ip，必须全部通过这N轮测试才能最终进入数据库。如果当天进入数据库的ip较少，则暂停一段时间（一天）再抓。

* 数据库中ip评价准则
  * 检测过程中累计超时次数>```USELESS_TIME```&&成功率<```SUCCESS_RATE```就被剔除。  
  ```score = (success_rate + test_times / 500) / avg_response_time```  
  原来的考虑是```score = success_rate / avg_response_time```, 即：评分=成功率/平均响应时间， 考虑到检测合格过100次的老ip比新ip更有价值，检测次数也被引入评分。



# 关注我们

**TideSec安全团队：**

![pic](images/ewm.png)

