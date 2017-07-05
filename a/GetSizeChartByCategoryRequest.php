<?php
/**
 * @desc   aliexpress 拉取该用户在这个分类下的尺码模板
 * @author AjunLongLive!
 * @time   2017-04-25
 */
class GetSizeChartByCategoryRequest extends AliexpressApiAbstract {   
	
	/** @var int 分类ID **/
	protected $_categoryId = null;	
	
	/**
	 * (non-PHPdoc)
	 * @see AliexpressApiAbstract::setApiMethod()
	 */
	public function setApiMethod() {
		$this->_apiMethod = 'api.getSizeChartInfoByCategoryId';
	}
	
	/**
	 * @desc 设置请求
	 */
	public function setRequest() {
		$request = array();
		if (!is_null($this->_categoryId))
		    $request['categoryId'] = $this->_categoryId;
		$this->request = $request;
		if(isset($_REQUEST['debug'])){
			print_r($request);
		}
 		
		return $this;
	}
	
	
	/**
	 * @desc 设置分类ID
	 * @param unknown $categoryID
	 */
	public function setCategoryID($categoryID) {
		$this->_categoryId = $categoryID;
	}
	
	/**
	 * @desc    获取错误的中文解释信息
	 * @param   $erroCode
	 * @return  详细的错误解释
	 */
	public function getErrorDetail($erroCode) {
		$errorArray = array(
		    
		);
		if (isset($errorArray[$erroCode])){
		    return $errorArray[$erroCode];
		} else {
		    return '未知错误，请联系技术！';
		}
	}
}