<?php

// 安装前的检测

// 这里写针对控制器中的php语法
//入库开始
if (strpos(CMF_VERSION, '.') !== false && version_compare(CMF_VERSION, '4.5.0') < 0) {
	$this->_json(0, '当前环境无法安装本插件，需要V4.5.0版本及上才支持');
// 不能安装的写法：$this->_json(0, '当前环境无法安装本插件');
}