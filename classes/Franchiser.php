<?php namespace Dstokesy\Franchises\Classes;

use Event;
use Config;
use Request;
use Redirect;
use Dstokesy\Franchises\Models\Franchise;

class Franchiser
{
    use \October\Rain\Support\Traits\Singleton;

	/**
	 * @var \Dstokesy\Franchises\Models\Franchise active franchise Model
	 */
    protected $franchise;

    /**
     * Initialize the singleton
     * @return void
     */
    protected function init()
    {
    	return;
    }

    public function setFranchise($id = false)
    {
    	if ($id) {
    		$this->setFranchiseById($id);
    	} else {
    		$this->setFranchiseByUrl();
    	}

    	$this->setFranchiseMediaDirectory();

    	return;
    }

    public function getFranchise()
    {
    	return $this->franchise;
    }

    public function getRedirect()
    {
    	$redirect = $this->getRedirectIfNoFranchise();

    	if ($redirect) {
    		return $redirect;
    	}

    	$redirect = $this->getRedirectIfFranchiseHasDomain();

    	if ($redirect) {
    		return $redirect;
    	}

    	return;
    }

    protected function setFranchiseById($id)
    {
    	$franchise = Franchise::where('id', $id)->first();

    	if ($franchise) {
			$this->franchise = $franchise;
		}

    	return $this;
    }

    protected function setFranchiseByUrl()
    {
    	if (env('APP_URL') == Request::root()) {
    		return;
    	}

    	$parts = explode('.', Request::getHost());

    	if (isset($parts[0])) {
    		if ($parts[0] == 'www') {
    			$this->setFranchiseFromDomain();
    		} else {
    			$this->setFranchiseFromSubDomain();
    		}
    	}

    	return $this;
    }

    protected function setFranchiseFromDomain()
    {
    	$host = Request::getHost();

    	$franchise = Franchise::where('domain', $host)->first();

    	if ($franchise) {
			$this->franchise = $franchise;
		}

    	return $this;
    }

    protected function setFranchiseFromSubDomain()
    {
    	$host = Request::getHost();
    	$parts = explode('.', Request::getHost());

    	if (isset($parts[0])) {
    		$subDomain = $parts[0];

    		$franchise = Franchise::where('slug', $subDomain)->first();

    		if ($franchise) {
    			$this->franchise = $franchise;
    		}
    	}

    	return $this;
    }

    protected function getRedirectIfNoFranchise()
    {
    	$redirect = false;

    	if (!$this->franchise) {
    		$redirect = true;
		}

		Event::fire('dstokesy.franchises.franchiser.beforeRedirect', [$this->franchise, &$redirect]);

		if ($redirect) {
			$requestedHost = Request::getHost();
			$host = parse_url(Config::get('app.url'), PHP_URL_HOST);

			if ($requestedHost != $host) {
				$url = Request::fullUrl();
				$url = str_replace($requestedHost, $host, $url);

				return Redirect::to($url);
			}
		}
    }

    protected function getRedirectIfFranchiseHasDomain()
    {
    	if ($this->franchise && $this->franchise->domain) {
    		$requestedHost = Request::getHost();
    		$domain = $this->franchise->domain;

    		if ($requestedHost != $domain) {
				$url = Request::fullUrl();
				$url = str_replace($requestedHost, $domain, $url);

				return Redirect::to($url);
    		}
    	}
    }

    protected function setFranchiseMediaDirectory()
    {
    	if ($this->franchise) {
	    	Config::set('cms.storage.media.path', '/storage/app/media-' . $this->franchise->id);
			Config::set('cms.storage.media.folder', 'media-' . $this->franchise->id);
    	}
    }
}
