<?php
class CommonAction extends Action{
    function common(){
        echo 'common';
    }

	/**
     * ģ��post����url����
     * @param string $url
     * @param array $post_data
     */
    function request_post($url = '', $post_data = array()) {
        if (empty($url) || empty($post_data)) {
            return false;
        }
        $o = "";
        foreach ( $post_data as $k => $v )
        {
            $o.= "$k=" . urlencode( $v ). "&" ;
        }
        $post_data = substr($o,0,-1);

        $postUrl = $url;
        $curlPost = $post_data;
        $ch = curl_init();//��ʼ��curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//ץȡָ����ҳ
        curl_setopt($ch, CURLOPT_HEADER, 0);//����header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//Ҫ����Ϊ�ַ������������Ļ��
        curl_setopt($ch, CURLOPT_POST, 1);//post�ύ��ʽ
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//����curl
        curl_close($ch);
        return $data;
    }

	/**
     * ģ��post����url����
     * @param string $url
     * @param array $post_data
     */
    function curl_post($url = '', $post_data = array()) {
        if (empty($url) || empty($post_data)) {
            return false;
        }
        $o = "";
        foreach ( $post_data as $k => $v )
        {
            $o.= "$k=" . urlencode( $v ). "&" ;
        }
        $post_data = substr($o,0,-1);
		$header = array(
			"content-type: application/x-www-form-urlencoded; 
			charset=UTF-8"
		);
        $postUrl = $url;
        $curlPost = $post_data;
        $ch = curl_init();//��ʼ��curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//ץȡָ����ҳ
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//����header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//Ҫ����Ϊ�ַ������������Ļ��
        curl_setopt($ch, CURLOPT_POST, 1);//post�ύ��ʽ
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//����curl
        curl_close($ch);
        return $data;
    }

	/*������غ�̨���ݽӿ� 
	*��ȡtoken
	*/
    function curl_JFpost($url = '', $post_data = array()) {
        $postUrl = $url;
        $curlPost = json_encode($post_data);
		$header = array(
			'Content-Type: application/json;charset=UTF-8',
			'Content-Length: ' . strlen($curlPost)
		);
        $ch = curl_init();//��ʼ��curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//ץȡָ����ҳ
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//����header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//Ҫ����Ϊ�ַ������������Ļ��
        curl_setopt($ch, CURLOPT_POST, 1);//post�ύ��ʽ
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//����curl
        curl_close($ch);
        return $data;
    }
}
?>
