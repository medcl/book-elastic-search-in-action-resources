...
此为部分关键代码
...

	public function suggest_action(){

		if (get_setting('advanced_search_enabled') != 'Y'){
			return null;
		}

		//获取请求参数，参数名为 q
		$keyword=$_GET['q'];


		//固定的搜索地址，来自索引 forum-mysql
		$url=get_setting('search_suggest_endpoint');

		//封装 QueryDSL 查询，过滤返回字段
		$dsl='{
		  "size":10,
		  "query": {
			"match": {
			  "title": "'.$keyword.'"
			}
		  },"_source": ["title","id","uid","views"]
		}';

		//执行 HTTP 请求，发送给 ES
		$response=self::post($url,$dsl);

		if($response!=null){

			$result=array();
			$o=json_decode($response,true);
			if(isset($o["hits"]["hits"])){
				foreach ($o["hits"]["hits"] as $v){
					//重新封装字段名称，符合搜索提示的结果规范
					$x["keyword"]=$v["_source"]["title"];
					//根据文章的 ID 来拼接文章的 URL
					$x["url"]="/article/".$v["_source"]["id"];
					//放入到结果数组
					array_push($result,$x);
				}
			}

			//生成 JSON 格式，输出
			echo json_encode($result);
		}
	}

	public function post($url,$queryDSL){

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		$data_string=$queryDSL;

		$data_string= str_replace('\\r\\n',"",$data_string);
		$data_string= str_replace('\r\n',"",$data_string);
		$data_string= str_replace('\\r',"",$data_string);
		$data_string= str_replace('\r',"",$data_string);
		$data_string= str_replace('\n',"",$data_string);
		$data_string= str_replace('\\n',"",$data_string);

//			$username = "elastic";
//			$password = "changeme";
//			if(isset($username)&&isset($password)){
//				curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
//			}

		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json; charset=utf-8',
				'Content-Length: ' . strlen($data_string))
		);
		ob_start();
		$json=curl_exec($ch);
		$return_content =$json;
		ob_end_clean();

		//如果请求执行成功会返回200的状态码，则返回响应结果
		$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($return_code==200){
			return$return_content;
		}

		return null;
	}
