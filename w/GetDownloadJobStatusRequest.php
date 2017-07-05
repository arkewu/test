<?php

/**
 * User: ketu.lai <ketu.lai@gmail.com>
 * Date: 2017/5/24 10:00
 */
class GetDownloadJobStatusRequest extends WishApiAbstract
{




    public function setJobId($jobId)
    {
        $this->request['job_id'] = $jobId;
        return $this;
    }

    public function setEndpoint($endpoint = 'product/get-download-job-status', $isPost = true)
    {
        parent::setEndpoint($endpoint, $isPost);
    }

    public function setRequest()
    {
        // TODO: Implement setRequest() method.
        return $this;
    }
}