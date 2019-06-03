<?php

class UnionAction extends HomeAction {

    public function index(){
		$yy=date("Y");
		$y = substr($yy,-2);
		$m=date("m");
		$d=date("d",strtotime("-1 day"));
		$operatedate=date("Y-m-d",strtotime("-1 day"));
		$date=$y.$m.$d;
		$file = "INN".$date."88ZM_104120049000035";
		###判断该文件是否存在
		if(file_exists($file)){
			$file_arr = file($file);
			$arr_new = array();

		####对数组的处理
			foreach($file_arr as $v){
				$a = trim($v);
				$a = str_replace("\r\n","",$a);
				$a = str_replace("\r","",$a);
				$a = str_replace("\n","",$a);
				$arr_new[] = $a;
			}


		}else{
			echo "file not exists!";
		}
				for($i=0;$i<count($arr_new);$i++){
					$arrstr=$arr_new[$i];
					$arrRow = explode(" ", $arrstr);
					$billcode=$arrRow[17];
					$realcash=$arrRow[12]/100;
					$housearr=$arrRow[180];
					$housearrnew = explode("@", $housearr);
					$housecode=$housearrnew[0];
					$itemcode=$housearrnew[1];
					$chargemonth=$housearrnew[2];
					$clocknumber=$housearrnew[3];

					$url = C('DB_ORACLE');
					$lszd = M('',null,$url);
					$sql = "insert into VALUES ('".$billcode."','00','".$realcash."','".$operaterdate."','".$housecode."','".$itemcode."','".$chargemonth."','".$clocknumber."','5890')";
					$lszd->query($sql);
				}
    }
}