<?php

/**
 * User: ketu.lai <ketu.lai@gmail.com>
 * Date: 2017/5/24 10:00
 */
class CreateDownloadJobRequest extends WishApiAbstract
{

    protected $_baseUrl = 'https://china-merchant.wish.com/api/v2/';
    protected $request = array();

    public function setSince($dateTime)
    {
        $this->request['since'] = $dateTime;
        return $this;
    }

    public function setLimit($limit)
    {
        $this->request['limit'] = $limit;
        return $this;
    }

    public function setSort($sort)
    {
        $this->request['sort'] = $sort;
        return $this;
    }

    public function setEndpoint($endpoint = 'product/create-download-job', $isPost = true)
    {
        parent::setEndpoint($endpoint, $isPost);
    }

    public function setRequest()
    {
        // TODO: Implement setRequest() method.
        return $this;
    }
}