<?php

/**
 * Class TT_Step_Finish
 *
 * This is not more than an information step.
 */
class TT_Step_Finish extends TT_Step_Abstract
{

    protected $slug = 'finish';

    private $active = true;

    public function getPlugins()
    {
        return $this->plugins;
    }

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
