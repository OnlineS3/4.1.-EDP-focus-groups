<?php

class TT_Step_Welcome extends TT_Step_Abstract
{

    protected $slug = "welcome";

    private $active = true;

    public function isActive()
    {
        return $this->active;
    }

    /**
     * This step is not required as it is only for information.
     * @return false
     */
    public function isRequired()
    {
        return false;
    }
}
