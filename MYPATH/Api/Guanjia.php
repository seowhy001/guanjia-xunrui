<?php

//搜外内容管家免登陆接口：version 1.0.0
//支持 XunRuiCMS-V4.5.1


$this->_module_init('news');
if (isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on") {
  $http = "https://";
} else {
  $http = "http://";
}
$domain = $http . str_replace('\\', '/', $_SERVER['HTTP_HOST']);


$rt = \Phpcmf\Service::M()->db->table("guanjiaset")->where('id', 1)->get();
if ($rt) {
  $rows = $rt->getResultArray();
  $setdata = json_decode($rows[0]["setdata"], true);
} else {
  guanjia_failRsp(1002, "guanjiaset table does not exist", "搜外内容管家发布插件配置表guanjiaset不存在，请检查并创建配置表");
}
$guanjia_time = intval($_REQUEST['guanjia_time']);
if(!$guanjia_time){
  guanjia_failRsp(1008, "password error", "time不存在");
}
if (time()-$guanjia_time > 600) {
  guanjia_failRsp(1009, "password error", "该token已超时！");
}
//密码校验
if (empty($_REQUEST['guanjia_token']) || $_REQUEST['guanjia_token'] != md5($guanjia_time . $setdata['guanjia_token'])) {
  guanjia_failRsp(1003, "password error", "token验证失败");
}

if ($_REQUEST['action'] == 'setting') {
  return guanjia_successRsp('','通讯成功');
}else if ($_REQUEST['action'] == 'articleAdd') {
//检查标题
  $title = isset($_REQUEST['title']) ? addslashes($_REQUEST['title']) : '';//标题
  if (empty($title)) {
    guanjia_failRsp(1004, "title is empty", "标题不能为空");
  }
//检查内容
  $content = isset($_REQUEST['content']) ? $_REQUEST['content'] : '';
  if (empty($content)) {
    guanjia_failRsp(1005, "content is empty", "内容不能为空");
  }
//检查栏目
  $catid = isset($_REQUEST['category_id']) ? addslashes($_REQUEST['category_id']) : '';
  if (empty($catid)) {
    guanjia_failRsp(1006, "catid is empty", "栏目ID不能为空");
  }

  $category = \Phpcmf\Service::M()->db->table("1_share_category")->where('id', $catid)->get();
  if ($category) {
    $categoryrows = $category->getResultArray();
    $setting = json_decode($categoryrows[0]["setting"], true);
    $urlrule = $setting['urlrule'];
    //如结果是"urlrule":0是动态，伪静态是"urlrule":1
    //{SITE_NAME}","list_keywords":"","list_description":""},"urlrule":1,"html":0}
    //{SITE_NAME}","list_keywords":"","list_description":""},"urlrule":0,"html":0}
    if ($urlrule) {
      $ifwzt = true;
    } else {
      $ifwzt = false;
    }
  } else {
    guanjia_failRsp(1002, "catid is error", "栏目ID错误，无法在迅睿CMS此找到此ID");
  }

//标题重复检查
//  if ($setdata['titleUnique']) {
//    $rowid = $this->content_model->find_id('title', $_REQUEST['title']);
//    if ($rowid > 0) {
//      if ($ifwzt) {
//        //show-139.html
//        $docFinalUrl = $domain . "/show-" . $rowid . '.html';
//      } else {
//        $docFinalUrl = $domain . "/index.php?c=show&id=" . $rowid;
//      }
//      return guanjia_successRsp(array("url" => $docFinalUrl), '标题已存在');
//    }
//  }

  //新建文章
  $uid = isset($_REQUEST['uid']) ? $_REQUEST['uid'] : 0;
//keywords,description
  $keywords = isset($_REQUEST['keywords']) ? $_REQUEST['keywords'] : '';
  $description = isset($_REQUEST['description']) ? $_REQUEST['description'] : '';

  $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 9;   //9发布，1审核
  $hits = isset($_REQUEST['hits']) ? $_REQUEST['hits'] : 0;
  $displayorder = isset($_REQUEST['displayorder']) ? $_REQUEST['displayorder'] : 0;
  $author = isset($_REQUEST['author']) ? $_REQUEST['author'] : '';
  $inputip = isset($_REQUEST['inputip']) ? $_REQUEST['inputip'] : '127.0.0.1';
  $inputtime = isset($_REQUEST['inputtime']) ? $_REQUEST['inputtime'] : SYS_TIME;
  $updatetime = isset($_REQUEST['updatetime']) ? $_REQUEST['updatetime'] : SYS_TIME;


// 主表字段
  $fields[1] = $this->get_cache('table-' . SITE_ID, $this->content_model->dbprefix(SITE_ID . '_' . MOD_DIR));
  $cache = $this->get_cache('table-' . SITE_ID, $this->content_model->dbprefix(SITE_ID . '_' . MOD_DIR . '_category_data'));
  $cache && $fields[1] = array_merge($fields[1], $cache);

// 附表字段
  $fields[0] = $this->get_cache('table-' . SITE_ID, $this->content_model->dbprefix(SITE_ID . '_' . MOD_DIR . '_data_0'));
  $cache = $this->get_cache('table-' . SITE_ID, $this->content_model->dbprefix(SITE_ID . '_' . MOD_DIR . '_category_data_0'));
  $cache && $fields[0] = array_merge($fields[0], $cache);

// 去重
  $fields[0] = array_unique($fields[0]);
  $fields[1] = array_unique($fields[1]);

  $save = [];

// 主表附表归类
  foreach ($fields as $ismain => $field) {
    foreach ($field as $name) {
      isset($_REQUEST[$name]) && $save[$ismain][$name] = $_REQUEST[$name];
    }
  }


  $save[1]['catid'] = $catid;
  $save[1]['title'] = $title;
//$save[1]['content'] = $content;
  $save[1]['keywords'] = $keywords;
  $save[1]['description'] = $description;

  $save[1]['uid'] = $uid;
  $save[1]['author'] = $author;
  $save[1]['status'] = $status;
  $save[1]['hits'] = $hits;
  $save[1]['displayorder'] = $displayorder;
  $save[1]['inputtime'] = $inputtime;
  $save[1]['updatetime'] = $updatetime;
  $save[1]['inputip'] = $inputip;

//直接到http开头的链接进行图片下载，对于图片暂停搜外内容管家的情况，系统会自动取得第一张图片的相对地址写入到表字段中。
  if (strpos($_REQUEST['thumb'], 'http') === 0) {
    $thumburl = downloadThumb($_REQUEST['thumb'], $domain);
    $save[1]['thumb'] = $thumburl;
  }

  $rt = $this->content_model->save_content(0, $save);
  if ($rt['code'] > 0) {
    if ($ifwzt) {
      //show-139.html
      $docFinalUrl = $domain . '/show-' . $rt['code'] . '.html';
    } else {
      $docFinalUrl = $domain . '/index.php?c=show&id=' . $rt['code'];
    }
    //$docFinalUrl=$domain.'/index.php?c=show&id='.$rt['code'];
    //图片http下载，不能用_POST
    downloadImages($_REQUEST);
    return guanjia_successRsp(array("url" => $docFinalUrl),'发布成功');

  } else {
    return guanjia_failRsp(1007, "insert dr_1_news error", "文章发布错误");
  }
} else if ($_REQUEST['action'] == 'categoryLists') {
  $rows = \Phpcmf\Service::M()->table("1_share_category")->select('id,name as title,pid as parent_id')->where('mid', 'news')->getAll();
  foreach ($rows as $k=>&$v){
    $v['id'] = intval($v['id']);
  }
  guanjia_successRsp($rows);
}


/**
 * 获取文件完整路径
 * @return string
 */
function getFilePath()
{
  //$rootUrl=$this->options->siteUrl();
  //使用php的方法试试
  ///uploads/ueditor/20200620/1-20062010343IR.jpeg
  $rootUrl = dirname(dirname(dirname(dirname(__FILE__))));
  return $rootUrl . '/uploadfile/ueditor/image';
}

/**
 * 查找文件夹，如不存在就创建并授权
 * @return string
 */
function createFolders($dir)
{
  return is_dir($dir) or (createFolders(dirname($dir)) and mkdir($dir, 0777));
}

////图片http下载，下载缩略图
function downloadThumb($thumb, $domain)
{
  try {

    //$downloadFlag = isset($post['__kds_download_imgs_flag']) ? $post['__kds_download_imgs_flag'] : '';
    //if (!empty($downloadFlag) && $downloadFlag== "true") {
    $docImgsStr = isset($thumb) ? $thumb : '';
    $file = '';

    if (!empty($docImgsStr)) {
      $docImgs = explode(',', $docImgsStr);
      if (is_array($docImgs)) {
        $uploadDir = getFilePath();
        foreach ($docImgs as $imgUrl) {
          $urlItemArr = explode('/', $imgUrl);
          $itemLen = count($urlItemArr);
          if ($itemLen >= 3) {
            //最后的相对路径,如  2018/06
            $fileRelaPath = $urlItemArr[$itemLen - 3] . '/' . $urlItemArr[$itemLen - 2];
            $imgName = $urlItemArr[$itemLen - 1];
            $finalPath = $uploadDir . '/' . $fileRelaPath;
            $thumburl = $domain . '/uploadfile/ueditor/image/' . $fileRelaPath . '/' . $imgName;

            if (createFolders($finalPath)) {
              $file = $finalPath . '/' . $imgName;

              if (!file_exists($file)) {
                $doc_image_data = file_get_contents($imgUrl);
                file_put_contents($file, $doc_image_data);

              }
              return $thumburl;
            }
          }
        }
      }
    }
    //}
  }
  catch (Exception $ex) {
    //error_log('error:'.$e->
  }
}

////图片http下载
//private
function downloadImages($post)
{
  try {

    $downloadFlag = isset($post['__kds_download_imgs_flag']) ? $post['__kds_download_imgs_flag'] : '';
    if (!empty($downloadFlag) && $downloadFlag == "true") {
      $docImgsStr = isset($post['__kds_docImgs']) ? $post['__kds_docImgs'] : '';

      if (!empty($docImgsStr)) {
        $docImgs = explode(',', $docImgsStr);
        if (is_array($docImgs)) {
          $uploadDir = getFilePath();
          foreach ($docImgs as $imgUrl) {
            $urlItemArr = explode('/', $imgUrl);
            $itemLen = count($urlItemArr);
            if ($itemLen >= 3) {
              //最后的相对路径,如  2018/06
              $fileRelaPath = $urlItemArr[$itemLen - 3] . '/' . $urlItemArr[$itemLen - 2];
              $imgName = $urlItemArr[$itemLen - 1];
              $finalPath = $uploadDir . '/' . $fileRelaPath;
              if (createFolders($finalPath)) {
                $file = $finalPath . '/' . $imgName;

                if (!file_exists($file)) {
                  $doc_image_data = file_get_contents($imgUrl);
                  file_put_contents($file, $doc_image_data);
                }
              }
            }
          }
        }
      }
    }
  }
  catch (Exception $ex) {
    //error_log('error:'.$e->
  }
}


function guanjia_successRsp($data = "", $msg = "")
{
  guanjia_rsp(1, $data, $msg);
}

function guanjia_failRsp($code = -1, $data = "", $msg = "")
{
  guanjia_rsp($code, $data, $msg);
}

function guanjia_rsp($code = 0, $data = "", $msg = "")
{
  die(json_encode(array("code" => intval($code), "data" => $data, "msg" => urlencode($msg))));
}
