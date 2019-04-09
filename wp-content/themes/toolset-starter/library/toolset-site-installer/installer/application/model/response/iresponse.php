<?php

interface TT_Response_Interface {
    public function response();
    public function success();
    public function failed();

    /**
     * @param $msg
     *
     * @return $this
     */
    public function setMessage($msg);
}