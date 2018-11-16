<?php

class TT_Step_Site_Key extends TT_Step_Abstract
{
    protected $slug = "site-key";

	public function isActive()
	{
		if( $this->settings->getProtocol()->isStepActive( $this->slug )
		    || ! $this->settings->getProtocol()->isSiteKeyValid( $this->settings->getRepository() ) ) {
			$this->settings->getProtocol()->setStepActive( $this->slug );
			// active without a valid site key or if we still in the same installation session
			return true;
		}

		return false;
	}

	public function requirementsDone()
	{
		if (! $this->settings->getProtocol()->isSiteKeyValid( $this->settings->getRepository() ) ) {
			return false;
		}

		$this->setFinished();
		return true;
	}
}
