<?php
/**
 * @desc 根据在线SKU，返回商品所在的商品分类
 * @author liz
 *
 */
class GetProductCategoriesForSKURequest extends AmazonApiAbstract{
	protected $_urlPath = '/Products/2011-10-01';
	protected $_sellerSKU;
	const MAX_REQUEST_TIMES = 20;	//接口最大请求次数20
	const RESUME_RATE_INTERVAL = 100;	//请求恢复间隔：5秒恢复1个请求，需要100秒恢复20个请求
	// const RESUME_RATE_NUM = 1;		//请求恢复每次1个
	/**
	 * @desc 设置服务对象实例
	 */
	protected function setServiceEntities(){
		$config = array (
				'ServiceURL' => $this->_serviceUrl,
				'ProxyHost' => $this->_proxyHost,
				'ProxyPort' => $this->_proxyPort,
				'MaxErrorRetry' => 3,
		);
		
		$service = new MarketplaceWebServiceProducts_Client(
				$this->_accessKeyID,
				$this->_secretAccessKey,
				$this->_appName,
				$this->_appVersion,
				$config
		);
		$this->_serviceEntities = $service;
	}

	public function __construct() {
		$this->_remainTimes = self::MAX_REQUEST_TIMES;
	}	
	
	/**
	 * @desc 设置请求对象实例
	 */
	protected function setRequestEntities() {
		$request = new MarketplaceWebServiceProducts_Model_GetProductCategoriesForSKURequest();
		$request->setSellerId($this->_merchantID);
		$request->setMarketplaceId($this->_marketPlaceID);	
		$request->setSellerSKU($this->_sellerSKU);
		$this->_requestEntities = $request;			
	}
	
	/**
	 * 调用接口方法
	 * @see AmazonApiAbstract::call()
	 */
	public function call() {
		try {
			if(!$this->requestAble()) sleep(self::RESUME_RATE_INTERVAL);

			$response = $this->_serviceEntities->getProductCategoriesForSKU($this->_requestEntities);	
			$this->_remainTimes--;	//统计接口调用次数
			$responseXml = $response->toXML();
			$responseArray = XML2Array::createArray($responseXml);		
			$Data = isset($responseArray['GetProductCategoriesForSKUResponse']['GetProductCategoriesForSKUResult']['Self']) ? $responseArray['GetProductCategoriesForSKUResponse']['GetProductCategoriesForSKUResult']['Self'] : array();
			$this->response = $Data;

		} catch (MarketplaceWebServiceProducts_Exception $e) {
			$msg = $e->getErrorMessage();
			// echo '错误提示'.$msg;exit;
			if(trim($msg) == "Request is throttled"){
				sleep(self::RESUME_RATE_INTERVAL);
				return $this->call();
			}else{
				$this->_errorMessage = 'Call api failure, ' . $msg;
				return false;
			}
		}
		return true;
	}
	/**
	 * @设置商品在线SKU
	 * @param unknown $sellerSKU
	 */
	public function setSellerSKU($sellerSKU){
		$this->_sellerSKU = $sellerSKU;
	}
}