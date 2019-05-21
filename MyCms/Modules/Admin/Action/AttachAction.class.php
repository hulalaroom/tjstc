<?php

class AttachAction extends AdminAction {

    public function upload() {

        if (!empty($_FILES['imgFile']['error'])) {
            switch ($_FILES['imgFile']['error']) {
                case '1':
                    $error = '超过php.ini允许的大小。';
                    break;
                case '2':
                    $error = '超过表单允许的大小。';
                    break;
                case '3':
                    $error = '图片只有部分被上传。';
                    break;
                case '4':
                    $error = '请选择图片。';
                    break;
                case '6':
                    $error = '找不到临时目录。';
                    break;
                case '7':
                    $error = '写文件到硬盘出错。';
                    break;
                case '8':
                    $error = 'File upload stopped by extension。';
                    break;
                case '999':
                default:
                    $error = '未知错误。';
            }
            $this->alert($error);
        }
        $config = F('attach','',CONF_PATH);
        $image = explode(',', $config['attach_ext_image']);
        $flash = explode(',', $config['attach_ext_flash']);
        $media = explode(',', $config['attach_ext_media']);
        $file = explode(',', $config['attach_ext_file']);
//检查目录名
        $dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
        $ext_arr = array(
            'image' => $image,
            'flash' => $flash,
            'media' => $media,
            'file' => $file,
        );
        $type = implode(",", $ext_arr[$dir_name]);
        import("ORG.Net.UploadFile");
        $upload = new UploadFile();
//$upload->supportMulti = false;//是否支持多文件上传
        $upload->maxSize = $config['attach_maxsize'] * 1024 * 1024; //$this->config['attach_maxsize']; //上传文件大小
        $upload->autoSub = true; //子目录上传
        $upload->subType = 'date'; //子目录创建方式
        $upload->dateFormat = 'Ymd'; //子目录格式
        $upload->allowExts = explode(',', $type); //设置上传文件类型 
        $upload->savePath = UPLOAD_PATH; //设置附件上传目录     
//创建文件夹
        if ($dir_name !== '') {
            $upload->savePath .= $dir_name . "/";

            if (!file_exists($upload->savePath)) {
                mkdirs($upload->savePath);
            }
        }
//有上传文件时
        if (empty($_FILES) === false) {
//原文件名
            $file_name = $_FILES['imgFile']['name'];
//服务器上临时文件名
            $tmp_name = $_FILES['imgFile']['tmp_name'];
//文件大小
            $file_size = $_FILES['imgFile']['size'];
//检查文件名
            if (!$file_name) {
                $this->alert("请选择文件。");
            }
////检查目录写权限
            if (@is_writable($upload->savePath) === false) {
                $this->alert("上传目录没有写权限。");
            }
//检查是否已上传
            if (@is_uploaded_file($tmp_name) === false) {
                $this->alert("上传失败。");
            }
//检查文件大小
            if ($file_size > $upload->maxSize) {
                $this->alert("上传文件大小超过限制。");
            }
//获得文件扩展名
            $temp_arr = explode(".", $file_name);
            $file_ext = array_pop($temp_arr);
            $file_ext = trim($file_ext);
            $file_ext = strtolower($file_ext);
//检查扩展名
            if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
                $this->alert("上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。");
            }
        }
        $upload->thumb = $config['attach_isthumb']; //设置需要生成缩略图，仅对图像文件有效
        $upload->imageClassPath = 'ORG.Util.Image'; // 设置引用图片类库包路径
        $upload->thumbPrefix = $config['attach_thumpre'];  //生产2张缩略图//设置需要生成缩略图的文件后缀
        $upload->thumbMaxWidth = $config['attach_thumbmaxwidth']; //设置缩略图最大宽度
        $upload->thumbMaxHeight = $config['attach_thumbmaxheight']; //设置缩略图最大高度
        $upload->thumbRemoveOrigin = $config['attach_thumbremove']; //删除原图
        $upload->saveRule = 'uniqid'; //设置上传文件规则
        if (!$upload->upload()) {
//捕获上传异常
            $this->error($upload->getErrorMsg());
        } else {
            $uploadList = $upload->getUploadFileInfo();
//            dump($uploadList);
//            array(1) {
//                [0] => array(8) {
//                    ["name"] => string(5) "0.jpg"
//                    ["type"] => string(10) "image/jpeg"
//                    ["size"] => int(3764)
//                    ["key"] => string(7) "imgFile"
//                    ["extension"] => string(3) "jpg"
//                    ["savepath"] => string(16) "./Uploads/image/"
//                    ["savename"] => string(26) "20130802/51fb655a6bf69.jpg"
//                    ["hash"] => bool(false)
//                }
//            }

            $sname = explode('/', $uploadList[0]['savename']); //文件名保存子目录，分割

            if ($config['attach_isthumb'] == 1 && $dir_name == "image") {//如果缩略
                $pre = explode(',', $config['attach_thumpre']); //缩略前缀

                $img = $uploadList[0]['savepath'] . $sname[0] . "/" . $pre[0] . $sname[1];
                $savename = $pre[0] . $sname[1];
            } else {
                $img = $uploadList[0]['savepath'] . $uploadList[0]['savename'];
                $savename = $sname[1];
            }


            if ($config['attach_iswater'] == 1) {
                $iswater = 1;
            } else {
                $iswater = 0;
            }

            $w = $this->_param('iswater');
            if (!empty($w)) {
                if ($w == "no") {
                    $iswater = 0;
                } else {
                    $iswater = 1;
                }
            }

            if ($iswater == 1) {
                import('ORG.Util.Image');
                //给m_缩略图添加水印, Image::water('原文件名','水印图片地址')

                Image::water($img, $config['attach_markpic'], '', $config['attach_alpha'], $config['attach_position']);
            }
//                        echo $img;
//            $this->addlog(1);
            header('Content-Type:text/html; charset=utf-8');
            echo json_encode(array('error' => 0, 'url' => $img, 'savename' => $savename, 'name' => $uploadList[0]['name']));
        }
    }

//
//            if ($config['attach_iswater'] == 1) {
//                $iswater = 1;
//            } else {
//                $iswater = 0;
//            }
//
//            $w = $this->_param('iswater');
//            if (!empty($w)) {
//                if ($w == "no") {
//                    $iswater = 0;
//                } else {
//                    $iswater = 1;
//                }
//            }
//
//
//
//            //取得成功上传的文件信息
//
//
//
//
//            if ($iswater == 1) {
//                import('ORG.Util.Image');
//                //给m_缩略图添加水印, Image::water('原文件名','水印图片地址')
//
//                Image::water($uploadList[0]['savepath'] . $sname[0] . "/" . $pre[0] . $sname[1], $config['attach_markpic'], '', $config['attach_alpha'], $config['attach_position']);
//                $url = $uploadList[0]['savepath'] . $sname[0] . "/" . $pre[0] . $sname[1];
//            }
//
////            echo $url;
//            $this->addlog(1);
//            header('Content-Type:text/html; charset=utf-8');
//            echo json_encode(array('error' => 0, 'url' => $url, 'savename' => $sname[1], 'name' => $uploadList[0]['name']));
//        }


    public function filelist() {


//根目录路径，可以指定绝对路径，比如 /var/www/attached/
        $root_path = UPLOAD_PATH;
//根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
        $root_url = UPLOAD_PATH;
//图片扩展名
        $ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');

//目录名
        $dir_name = empty($_GET['dir']) ? '' : trim($_GET['dir']);
        if (!in_array($dir_name, array('', 'image', 'flash', 'media', 'file'))) {
            echo "Invalid Directory name.";
            exit;
        }
        if ($dir_name !== '') {
            $root_path .= $dir_name . "/";
            $root_url .= $dir_name . "/";
            if (!file_exists($root_path)) {
                @mkdir($root_path);
            }
        }

//根据path参数，设置各路径和URL
        if (empty($_GET['path'])) {
            $current_path = realpath($root_path) . '/';
            $current_url = $root_url;
            $current_dir_path = '';
            $moveup_dir_path = '';
        } else {
            $current_path = realpath($root_path) . '/' . $_GET['path'];
            $current_url = $root_url . $_GET['path'];
            $current_dir_path = $_GET['path'];
            $moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
        }
        echo realpath($root_path);
//排序形式，name or size or type
        $order = empty($_GET['order']) ? 'name' : strtolower($_GET['order']);

//不允许使用..移动到上一级目录
        if (preg_match('/\.\./', $current_path)) {
            echo 'Access is not allowed.';
            exit;
        }
//最后一个字符不是/
        if (!preg_match('/\/$/', $current_path)) {
            echo 'Parameter is not valid.';
            exit;
        }
//目录不存在或不是目录
        if (!file_exists($current_path) || !is_dir($current_path)) {
            echo 'Directory does not exist.';
            exit;
        }

//遍历目录取得文件信息
        $file_list = array();
        if ($handle = opendir($current_path)) {
            $i = 0;
            while (false !== ($filename = readdir($handle))) {
                if ($filename{0} == '.')
                    continue;
                $file = $current_path . $filename;
                if (is_dir($file)) {
                    $file_list[$i]['is_dir'] = true; //是否文件夹
                    $file_list[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
                    $file_list[$i]['filesize'] = 0; //文件大小
                    $file_list[$i]['is_photo'] = false; //是否图片
                    $file_list[$i]['filetype'] = ''; //文件类别，用扩展名判断
                } else {
                    $file_list[$i]['is_dir'] = false;
                    $file_list[$i]['has_file'] = false;
                    $file_list[$i]['filesize'] = filesize($file);
                    $file_list[$i]['dir_path'] = '';
                    $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
                    $file_list[$i]['filetype'] = $file_ext;
                }
                $file_list[$i]['filename'] = $filename; //文件名，包含扩展名
                $file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
                $i++;
            }
            closedir($handle);
        }

//排序
        function cmp_func($a, $b) {
            global $order;
            if ($a['is_dir'] && !$b['is_dir']) {
                return -1;
            } else if (!$a['is_dir'] && $b['is_dir']) {
                return 1;
            } else {
                if ($order == 'size') {
                    if ($a['filesize'] > $b['filesize']) {
                        return 1;
                    } else if ($a['filesize'] < $b['filesize']) {
                        return -1;
                    } else {
                        return 0;
                    }
                } else if ($order == 'type') {
                    return strcmp($a['filetype'], $b['filetype']);
                } else {
                    return strcmp($a['filename'], $b['filename']);
                }
            }
        }

        usort($file_list, 'cmp_func');

        $result = array();
//相对于根目录的上一级目录
        $result['moveup_dir_path'] = $moveup_dir_path;
//相对于根目录的当前目录
        $result['current_dir_path'] = $current_dir_path;
//当前目录的URL
        $result['current_url'] = $current_url;
//文件数
        $result['total_count'] = count($file_list);
//文件列表数组
        $result['file_list'] = $file_list;

//输出JSON字符串
        header('Content-type: application/json; charset=UTF-8');

        echo json_encode($result);
    }

    function alert($msg) {
        header('Content-type: text/html; charset=UTF-8');

        echo json_encode(array('error' => 1, 'message' => $msg));
        exit;
    }
	//加入发布状态
	public function putout()
	{
		
			$data = array ();
			$id=array();
			$wid=array();
			$id['operateTime']=$_POST['operateTime'];
			
            $m = M('Energy_meter');
			$wid= $m->where($id)->getField('id',true);
			$num = count($wid); 		
			for($i=0;$i<$num;++$i){
				$data['id']=$wid[$i];
                $data['pd']=1;
				D('Energy_meter')->save($data);
			}
					          
	}
	//修改计量数据
	public function xq()
	{
		
	$data['id']=$_POST['id'];
	$data['startNum']=$_POST['startNum'];	
	$data['nowNum']=$_POST['nowNum'];	
	$data['allTotal']=$_POST['allTotal'];	
	$data['percent']=$_POST['percent'];	
		$data['meterAccount']=$_POST['meterAccount'];	
	
	D('Energy_meter')->save($data);

					          
	}
    public function del() {

        if (!empty($_POST['file'])) {
            delDirAndFile($_POST['file']);
            echo 1;
        }
    }

}

?>
