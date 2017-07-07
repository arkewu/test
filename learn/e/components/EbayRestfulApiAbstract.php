<?php
/**
 * @desc Ebay Rest API Abstract
 * @author yangsh
 * @since 2017-03-11
 */
abstract class EbayRestfulApiAbstract implements PlatformApiInterface {
    
    /**@var string 最终交互地址*/
    protected $_url = null;

    /**@var string 服务地址*/
    protected $_baseUrl = null;
    
    /**@var string 交互Endpoint*/
    protected $_endpoint = null;

    /**@var int 账号ID*/
    protected $accountID = null;

    /**@var string eBay Redirect URL Name*/
    protected $_ruName = null;

    /**@var string access token*/
    protected $_accessToken = null;

    /**@var string refresh token*/
    protected $_refreshToken = null;

    /**@var string 开发者账号ID(与开发者账号绑定)*/
    protected $_devID = null;
    
    /**@var string Client ID(与开发者账号绑定)*/
    protected $_clientID = null;
    
    /**@var string Client 密钥(与开发者账号绑定)*/
    protected $_clientSecret = null;

    /**@var string Marketplace ID 如：EBAY-US.MOTORS */
    protected $_marketplaceID = null;

    /** @var array ebayRestfulKeys **/
    public $ebayRestfulKeys = null;    

    /**@var string 请求内容*/
    protected $request = null;
    
    /**@var string 返回响应信息*/
    protected $response = null;

    /**@var string 请求头信息*/
    protected $_headers = null;
    
    /**@var string 请求报文信息*/
    protected $_requestbody = null;

    /** @var boolean 是否为Post交互*/
    protected $_isPost = false;

    /** @var boolean 是否为json格式交互*/
    protected $_isJson = true;

    /* @var int timeout */
    protected $_timeout = 1800;  

    /* @var array curl响应结果 */
    protected $_curlResponse = null; 

    protected $_errorCode = null;

    protected $_errorMsg = null;      

    /** all ebay scope list **/
    public static $ALL_EBAY_SCOPE_LIST = array(
        'api_scope'                 =>'https://api.ebay.com/oauth/api_scope',
        'sell_marketing_readonly'   =>'https://api.ebay.com/oauth/api_scope/sell.marketing.readonly',
        'sell_marketing'            =>'https://api.ebay.com/oauth/api_scope/sell.marketing',
        'sell_inventory_readonly'   =>'https://api.ebay.com/oauth/api_scope/sell.inventory.readonly',
        'sell_inventory'            =>'https://api.ebay.com/oauth/api_scope/sell.inventory',
        'sell_account_readonly'     =>'https://api.ebay.com/oauth/api_scope/sell.account.readonly',
        'sell_account'              =>'https://api.ebay.com/oauth/api_scope/sell.account',
        'sell_fulfillment_readonly' =>'https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly',
        'sell_fulfillment'          =>'https://api.ebay.com/oauth/api_scope/sell.fulfillment',
        'sell_analytics_readonly'   =>'https://api.ebay.com/oauth/api_scope/sell.analytics.readonly',
        //'buy_order_readonly'        =>'https://api.ebay.com/oauth/api_scope/buy.order.readonly',
        //'buy_guest_order'           =>'https://api.ebay.com/oauth/api_scope/buy.guest.order',        
    );

    /**
     * @desc 初始化对象
     */    
    public function __construct(){
        $ebayRestfulKeys = ConfigFactory::getConfig('ebayRestfulKeys');
        if(empty($ebayRestfulKeys)) {
            throw new Exception("ebayRestfulKeys Not Exist");
        }
        $this->ebayRestfulKeys = $ebayRestfulKeys;
    }

    /**
     * @desc 设置账号信息
     * @param int $accountID
     */
    public function setAccount($accountID){
        $accountInfo = EbayAccountRestful::model()->getByAccountID($accountID);//获取账号相关信息
        if(empty($accountInfo)) {
            throw new Exception("Account Not Exist");
        }
        $this->accountID     = $accountID;
        $this->_ruName       = $accountInfo['ru_name'];   
        $this->_accessToken  = $accountInfo['access_token'];
        $this->_refreshToken = $accountInfo['refresh_token'];
        $this->_devID        = $accountInfo['dev_id'];
        $this->_clientID     = $accountInfo['client_id'];
        $this->_clientSecret = $accountInfo['client_secret'];

        return $this;
    }

    public function setTimeout($timeout) {
        $this->_timeout = $timeout;
        return $this;
    }

    public function getTimeout() {
        return $this->_timeout;
    }     

    public function getMarketplaceID(){
        return $this->_marketplaceID;
    }

    /**
     * @desc 设置Marketplace ID
     * @param string $marketplaceID
     */
    public function setMarketplaceID($marketplaceID){
        $this->_marketplaceID = $marketplaceID;
        return $this;
    } 

    /**
     * @desc 设置Endpoint
     */
    public abstract function setEndPoint();    

    /**
     * @desc 设置交互链接
     * @see PaytmApiAbstract::setUrl()
     */
    public function setUrl() {
        $this->setEndPoint();
        $this->_url = $this->_baseUrl . $this->_endpoint;
    }             
    
    /**
     * @desc 设置http头信息
     */
    public function setHeaders(){
        $requestHeaders = array(
            'Accept'         => 'application/json',
            'Accept-Charset' => 'utf-8',
            'Authorization'  => 'Bearer '.$this->_accessToken,
        );
        if($this->_marketplaceID) {
            $requestHeaders['X-EBAY-C-MARKETPLACE-ID'] = $this->_marketplaceID;
        }
        $this->_headers = $requestHeaders;
    }    

    /**
     * @desc 发送请求,获取响应结果
     */
    public function sendRequest() {
        try {
            $curl = new Curl();
            $curl->init();                
            $this->setUrl();
            $this->setHeaders();
            if(!empty($_REQUEST['debug'])) {
                echo '<hr>##### serviceUrl: '.$this->_url."<br>";
            }
            if($this->_isPost && !$this->_isJson){//普通表单格式post
                $response = $curl->setOption(CURLOPT_TIMEOUT, $this->_timeout)
                                 ->setHeaders($this->_headers)
                                 ->addCertificate()
                                 ->post($this->_url, $this->getRequestBody());
                if(!empty($_REQUEST['debug'])){
                    echo '<br>##### requestBody[POST]: '.$this->_requestbody."<br>";
                } 
            }elseif($this->_isPost && $this->_isJson ){//以json格式post
                $response = $curl->setOption(CURLOPT_TIMEOUT, $this->_timeout)
                                 ->addCertificate()
                                 ->postByJson($this->_url, $this->request, $this->_headers);
                if(!empty($_REQUEST['debug'])){
                    echo '<br>##### requestBody[Json POST]: ';print_r($this->request);
                }                
            }else if($this->_isJson){//以json格式get
                $response = $curl->setOption(CURLOPT_TIMEOUT, $this->_timeout)
                                 ->addCertificate()//添加证书
                                 ->getByRestful($this->_url, $this->request, $this->_headers);
                if(!empty($_REQUEST['debug'])) {
                    echo '<br><pre>#####requestBody[Json GET]: ';print_r($this->request);
                }
            } else {//get方式
                $response = $curl->setOption(CURLOPT_TIMEOUT, $this->_timeout)
                                 ->setHeaders($this->_headers)
                                 ->addCertificate()
                                 ->get($this->_url, $this->request);
                if(!empty($_REQUEST['debug'])) {
                    echo '<br><pre>#####requestBody[GET]: ';print_r($this->request);
                }                 
            }
            $this->response = json_decode($response);
            $this->_curlResponse = $curl->getCurlResponse();//curlResponse object, 属性：errno，error，info  
            if(!empty($_REQUEST['debug']))  {
                echo '<br>##### response: '.var_export($response,true).'<hr>';
            }
            if( !$this->getIfSuccess() ){
                $this->writeErrorLog();
            }
        } catch (Exception $e ) {
            $this->writeErrorLog();
        }
        return $this;
    }

    /**
     * @desc 将请求参数变成url参数
     */
    public function getRequestBody(){
        $requestBody = $this->getRequest();
        $this->_requestbody = http_build_query($requestBody);
        return $this->_requestbody;
    }
    
    /**
     * @desc 获取请求参数
     * @see ApiInterface::getRequest()
     */
    public function getRequest() {
        return $this->request;
    }
    
    /**
     * @desc 获取响应结果
     * @see ApiInterface::getResponse()
     */
    public function getResponse() {
        return $this->response;
    }
    
    /**
     * @desc 判断交互是否成功
     * @return boolean
     */
    public function getIfSuccess(){
        return $this->_curlResponse->info['http_code'] != 200
                 || empty($this->response)
                 || isset($this->response->errors) ? false : true;
    }

    /**
     * @desc 获取token
     * @return string
     */
    public function getToken(){
        return $this->_accessToken;
    }   

    /**
     * @desc 获取响应编码
     * @return string
     */
    public function getErrorCode(){
        return isset($this->response->error_code) ? $this->response->error_code : '';
    }    
    
    /**
     * @desc 获取失败信息
     * @return string 
     */
    public function getErrorMsg(){
        //curl error
        $this->_errorMsg = empty($this->_curlResponse->error) ? '' : trim($this->_curlResponse->error);
        //response error message
        if(isset($this->response->errors) || isset($this->response->warnings)) {
            $errData = isset($this->response->errors) ? $this->response->errors : $this->response->warnings;
            foreach ($errData as $value) {
                if(isset($value['longMessage'])) {
                    $this->_errorMsg .= $value['longMessage'].' ';
                } else if($value['message']) {
                    $this->_errorMsg .= $value['message'].' ';
                }
            }
        }        
        return $this->_errorMsg;
    }
    
    /**
     * @desc 记录文件错误日志
     */
    public function writeErrorLog(){
        $logPath = Yii::getPathOfAlias('webroot').'/log/ebay/'.date('Y').'/'.date('m').'/'.date('d').'/'.date('H');
        if( !is_dir($logPath) ){
            mkdir($logPath, 0777, true);
        }
        $log = date('Y-m-d H:i:s')."\n";//时间，接口名
        $log .= $this->_requestbody."\n";//交互报文
        $log .= 'Error Message:'.$this->getErrorMsg()."\n\n";//错误信息
        $fileName = $this->accountID.'-'.date("YmdHis").'.txt';
        file_put_contents($logPath.'/'.$fileName, $log, FILE_APPEND);
    } 

}

?>