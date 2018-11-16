<?php

/**
 * Class TT_Step_Abstract
 *
 * Each step of the installation uses this as base.
 *
 */
abstract class TT_Step_Abstract
{
    protected $slug = 'anonymous';

    /**
     * @var string
     */
    protected $title;

    /**
     * @var TT_Settings_Interface
     */
    protected $settings;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var string
     */
    protected $menu_title;

    /**
     * @var TT_Settings_Protocol_Interface
     */
    protected $memory;

    /**
     * TT_Step_Abstract constructor.
     *
     * @param TT_Settings_Interface $settings
     */
    public function __construct(TT_Settings_Interface $settings)
    {
        $this->settings = $settings;
        $this->memory   = $settings->getProtocol();
    }

    /**
     * Title
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Slug
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Assign template
     *
     * @param $template Path to template
     */
    public function setTemplate($template)
    {
        if (file_exists($template)) {
            $this->template = $template;
        }
    }

    /**
     * Get HTML output of the step
     * requires having a valid template
     */
    public function render()
    {
        if ($this->template !== null && file_exists($this->template)) {
            include($this->template);
        }
    }

    /**
     * Is step active
     * @return bool
     */
    public function isActive()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function requirementsDone()
    {
        $this->setFinished();

        return true;
    }

    /**
     * Step is done and won't be active on revisiting
     * TT_Settings_Protocol handles the storage
     */
    protected function setFinished()
    {
        $this->memory->setStepFinished($this);
    }

    /**
     * Check if the step is already finished.
     * See TT_Setting_Memory for details
     *
     * @return bool
     */
    public function isFinished()
    {
        return $this->memory->isStepFinished($this);
    }

    /**
     * Step required or not
     * @return bool
     */
    public function isRequired()
    {
        return true;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns true on theme update and false on initial theme installation
     *
     * @return bool
     */
    public function isUpdate() {
    	return $this->settings->getContext()->isUpdate();
    }
}
