<?php namespace Phpcmf\Controllers\Admin;

class Home extends \Phpcmf\Table
{

/*
	public function index() {

	    $name = 'hello word';

        // 将变量传入模板
        \Phpcmf\Service::V()->assign([
            'testname' => $name,
        ]);
        // 选择输出模板 后台位于 ./Views/test.html 此文件已经创建好了
        \Phpcmf\Service::V()->display('test.html');
    }
*/

    public function __construct(...$params) {
        parent::__construct(...$params);

        // 初始化数据表
        $this->_init([
            'table' => 'guanjiaset',
            'order_by' => 'id ASC',
        ]);


//'搜外内容管家发布插件' => [APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/index', 'bi bi-globe2'],
//setdata

        \Phpcmf\Service::V()->assign([
            'menu' => \Phpcmf\Service::M('auth')->_admin_menu(
                [
                    '搜外内容管家发布插件' => [APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/index', 'bi bi-globe2'],

                ]
            )
        ]);



    }



    public function index() {

		if (isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on") {
			$http="https://";
		} else {
			$http="http://";
		}
		$domain = $http.str_replace('\\', '/', $_SERVER['HTTP_HOST']).'/api/guanjia.php';
		//$domain = 'hello word';
/*
        // 将变量传入模板
        \Phpcmf\Service::V()->assign([
            'testname' => $name,
        ]);
        // 选择输出模板 后台位于 ./Views/test.html 此文件已经创建好了
        \Phpcmf\Service::V()->display('index.html');
		*/
		$id = 1;

        if(IS_POST){
            $data = \Phpcmf\Service::L('Input')->post('data');
            $data = array(
                'setdata'=> json_encode($data)
            );
            //var_dump($setting);
            $rt = \Phpcmf\Service::M()->table('guanjiaset')->update($id, $data);
            exit($this->_json(1, dr_lang('操作成功')));
        }

        list($tpl, $data) = $this->_Post($id, null, 1);
        //!$data['data'] && $this->_admin_msg(0, dr_lang('数据#%s不存在', $id));

        $content = \Phpcmf\Service::L('cache')->get('module-'.SITE_ID.'-content');

        if ($content) {
            foreach ($content as $i => $t) {
                    $module[$i] = $t;
            }
        }

        \Phpcmf\Service::V()->assign([
            'data' => json_decode($data['setdata'], true),
			'domain' => $domain,
            'module' => $module,
        ]);
        \Phpcmf\Service::V()->display('index.html');
    }
}
