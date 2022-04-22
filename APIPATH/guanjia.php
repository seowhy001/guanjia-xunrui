<?php

/**
 * 搜外内容管家api
 * https://guanjia.seowhy.com
 */
//error_log('api_start:'. 'api_start1'.PHP_EOL, 3, '/www/wwwroot/xunruicms.simpledatas.com/test.log');
define('IS_API', basename(__FILE__, '.php')); // 项目标识
//error_log('FILE:'. basename(__FILE__, '.php').PHP_EOL, 3, '/www/wwwroot/xunruicms.simpledatas.com/test.log');
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME)); // 该文件的名称
//error_log('SELF:'. pathinfo(__FILE__, PATHINFO_BASENAME).PHP_EOL, 3, '/www/wwwroot/xunruicms.simpledatas.com/test.log');
define('rootUrl', dirname(__FILE__)); // url目录
//error_log('rootUrl:'. dirname(dirname(__FILE__)).PHP_EOL, 3, '/www/wwwroot/xunruicms.simpledatas.com/test.log');
require("../index.php"); // 引入主文件
