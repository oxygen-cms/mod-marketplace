<?php

namespace Oxygen\Marketplace\Package;

use Michelf\MarkdownExtra;
use Carbon\Carbon;

use Oxygen\Marketplace\Loader\LoaderInterface;
use Oxygen\Marketplace\Loader\LoadingException;

class Package {

    /**
     * Parent marketplace of the package.
     *
     * @var Marketplace
     */

    protected $loader;

    /**
     * Name of the package.
     *
     * @var string
     */

    protected $name;

    /**
     * Images for the package.
     *
     * @var array
     */

    protected $images;

    /**
     * Icon of the package.
     *
     * @var string
     */

    protected $icon;

    /**
     * Readme for the package.
     *
     * @var array
     */

    public $readme;

    /**
     * Prettier name for the package.
     *
     * @var string
     */

    protected $prettyName;

    /**
     * Versions of the package.
     *
     * @var array
     */

    protected $versions;

    /**
     * Service Providers that the Package Exposes
     *
     * @var array
     */

    protected $providers;

    /**
     * Keywords for the package
     *
     * @var array
     */

    protected $keywords;

    /**
     * Constructs the Package.
     *
     * @param string $name
     */

    public function __construct(LoaderInterface $loader, $name) {
        $this->loader = $loader;
        $this->name = $name;
        $this->images = [];
        $this->icon   = null;
        $this->readme = 'README.md';
        $this->readme = null;
        $this->loaded = false;
    }

    /**
     * Returns the name of the package.
     *
     * @return string
     */

    public function getName() {
        return $this->name;
    }

    /**
     * Returns the name of the package.
     *
     * @return string
     */

    public function getPrettyName() {
        if($this->prettyName !== null) {
            return $this->prettyName;
        } else {
            return $this->name;
        }
    }

    /**
     * Get the name split into the vendor and package.
     *
     * @return array
     */

    public function getSplitName() {
        return explode('/', $this->name);
    }

    /**
     * Determines if the package has included some lovely images.
     *
     * @return boolean
     */

    public function hasImages() {
        $this->load();
        return !empty($this->images);
    }

    /**
     * Returns the packages images.
     *
     * @return array
     */

    public function getImages() {
        if(!$this->hasImages()) { return []; }

        $images = $this->images;
        foreach($images as &$image) {
            if(!isset(parse_url($image)['scheme'])) {
                $image = $this->loader->getUrl($this, $image);
            }
        }
        return $images;
    }

    /**
     * Returns the package's icon.
     *
     * @return string
     */

    public function getIcon() {
        if(!isset(parse_url($this->icon)['scheme'])) {
            return $this->loader->getUrl($this, $this->icon);
        } else {
            return $this->icon;
        }
    }

    /**
     * Returns if the package has an icon.
     *
     * @return boolean
     */

    public function hasIcon() {
        return $this->icon !== null;
    }

    /**
     * Returns the readme for the package.
     *
     * @param mixed $default
     * @return string
     */

    public function getReadme($default = null) {
        try {
            $readme = $this->loader->getReadme($this);
            $parser = new MarkdownExtra;
            $parser->no_entities = true;
            $parser->no_markup = true;
            return $parser->transform($readme);
        } catch(LoadingException $e) {
            return $default;
        }
    }

    /**
     * Returns the latest version of the package.
     *
     * @return array
     */

    public function getLatestVersion() {
        return !is_null($this->versions) ? head($this->versions) : [];
    }

    /**
     * Returns all versions of the package.
     *
     * @return array
     */

    public function getVersions() {
        return $this->versions;
    }

    /**
     * Returns the package's keywords
     *
     * @return array
     */

    public function getKeywords() {
        return $this->keywords;
    }

    /**
     * Returns providers that the package exposes.
     *
     * @return array
     */

    public function getProviders() {
        return $this->providers !== null ? $this->providers : [];
    }

    /**
     * Returns the type of the repository
     *
     * @return array
     */

    public function getRepositoryType() {
        if(strpos($this->repository, 'github.com/') !== false) {
            return 'GitHub';
        } else {
            return 'Unknown';
        }
    }

    /**
     * Returns the authors of the package in human-readable form.
     *
     * @param null $version
     * @return string
     */

    public function getAuthorsAsSentence($version = null) {
        if($version === null) {
            $version = $this->getLatestVersion();
        }

        $return = '';
        $count = count($version['authors']);
        for($i = 0; $i < $count; $i++) {
            $return .= $version['authors'][$i]['name'];
            if($i < $count - 2) {
                $return .= ', ';
            } else if($i < $count - 1) {
                $return .= ' and ';
            }
        }

        return $return;
    }

    /**
     * Loads all details about a package.
     *
     * @return void
     */

    public function load() {
        if($this->loaded) { return; }
        $data = $this->loader->getPackageDetails($this->name);
        $this->fillFromArray($data['package']);
        $this->loaded = true;
    }

    /**
     * Fills the package with information from an array of data.
     *
     * @param array $data
     * @return void
     */

    public function fillFromArray(array $data) {
        $this->description  = isset($data['description']) ? $data['description'] : 'No Description';
        $this->time         = isset($data['time']) ? new Carbon($data['time']) : null;
        $this->maintainers  = isset($data['maintainers']) ? $data['maintainers'] : null;
        $this->versions     = isset($data['versions']) ? $data['versions'] : null;
        $this->type         = isset($data['type']) ? $data['type'] : null;
        $this->repository   = isset($data['repository']) ? $data['repository'] : null;
        $this->downloads    = isset($data['downloads'])
            ? (is_array($data['downloads']) ? $data['downloads'] : ['total' => $data['downloads']])
            : null;
        $this->favers       = isset($data['downloads']) ? $data['favers'] : null;

        $latest = $this->getLatestVersion()  === [] ? $data : $this->getLatestVersion();
        if(isset($latest['homepage'])) {
            $this->homepage = $latest['homepage'];
        }
        if(isset($latest['extra']['readme'])) {
            $this->readme = $latest['extra']['readme'];
        }
        if(isset($latest['extra']['images'])) {
            $this->images = $latest['extra']['images'];
        }
        if(isset($latest['extra']['icon'])) {
            $this->icon = $latest['extra']['icon'];
        }
        if(isset($latest['extra']['title'])) {
            $this->prettyName = $latest['extra']['title'];
        }
        if(isset($latest['extra']['providers'])) {
            $this->providers = $latest['extra']['providers'];
        } else {
            $this->providers = [];
        }
        if(isset($latest['keywords'])) {
            $this->keywords = $latest['keywords'];
        }
    }

}