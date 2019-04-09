<?php


/**
 * Class TT_Step_Demo_Import
 *
 * This handles the initial import of settings and content.
 */
class TT_Step_Demo_Import extends TT_Step_Abstract
{

    protected $slug = 'demo-import';

    /**
     * @return TT_Settings_Files_Interface
     */
    public function getImporter()
    {
        return $this->settings->getFiles();
    }

    public function getPlugins()
    {
        return $this->plugins;
    }

    public function isActive()
    {
        return true;

	    if( ( $this->settings->getFiles()->hasWordpressImport() && ! $this->settings->getProtocol()->isThemeUpdate() )
            || $this->settings->getFiles()->hasPluginImports()
        ) {
            return true;
        }

        return false;
    }

    public function allowNext()
    {
        return true;
    }

    protected function setFinished()
    {
        $this->memory->setStepFinished($this);
        $this->memory->setLastInstalledThemeVersion();
    }
}
