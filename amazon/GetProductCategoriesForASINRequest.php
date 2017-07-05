<?php
/**
 * @desc 根据 ASIN，返回商品所在的商品分类
 * @author liz
 *
 */
class GetProductCategoriesForASINRequest extends AmazonApiAbstract{
	protected $_urlPath = '/Products/2011-10-01';
	protected $_asinID;
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
		$request = new MarketplaceWebServiceProducts_Model_GetProductCategoriesForASINRequest();
		$request->setSellerId($this->_merchantID);
		$request->setMarketplaceId($this->_marketPlaceID);	
		$request->setASIN($this->_asinID);
		$this->_requestEntities = $request;			
	}
	
	/**
	 * 调用接口方法
	 * @see AmazonApiAbstract::call()
	 */
	public function call() {
		try {
			if(!$this->requestAble()) sleep(self::RESUME_RATE_INTERVAL);

			$response = $this->_serviceEntities->getProductCategoriesForASIN($this->_requestEntities);	
			$this->_remainTimes--;	//统计接口调用次数
			$responseXml = $response->toXML();
			$responseArray = XML2Array::createArray($responseXml);		
			$Data = isset($responseArray['GetProductCategoriesForASINResponse']['GetProductCategoriesForASINResult']['Self']) ? $responseArray['GetProductCategoriesForASINResponse']['GetProductCategoriesForASINResult']['Self'] : array();	
			$this->response = $Data;

		} catch (MarketplaceWebServiceProducts_Exception $ex) {
			$this->_errorMessage = 'Call api failure, ' . $ex->getMessage();
			return false;
		}
		return true;
	}
	/**
	 * @设置商品asin码
	 * @param unknown $asinids
	 */
	public function setAsinID($asinids){
		$this->_asinID = $asinids;
	}
}