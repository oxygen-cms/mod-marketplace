<?php

namespace OxygenModule\Marketplace\Package;

use Michelf\MarkdownExtra;
use Carbon\Carbon;

use OxygenModule\Marketplace\Loader\LoaderInterface;
use OxygenModule\Marketplace\Loader\LoadingException;

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
     * Description of the package.
     *
     * @var string
     */

    protected $description;

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

    public $time;

    public $maintainers;

    public $type;

    public $repository;

    public $homepage;

    public $downloads;

    public $favers;

    /**
     * Constructs the Package.
     *
     * @param LoaderInterface $loader
     * @param string          $name
     */
    public function __construct(LoaderInterface $loader, $name) {
        $this->loader = $loader;
        $this->name = $name;
        $this->images = [];
        $this->readme = 'README.md';
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
     * Returns the package's description.
     *
     * @return string
     */
    public function getDescription() {
        return $this->description !== null ? $this->description : 'No Description';
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
        return $this->keywords ?: [];
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
        if(isset($data['description'])) { $this->description = $data['description']; }
        if(isset($data['time'])) { $this->time = new Carbon($data['time']); }
        if(isset($data['maintainers'])) { $this->maintainers = $data['maintainers']; }
        if(isset($data['versions'])) { $this->versions = $data['versions']; }
        if(isset($data['type'])) { $this->type = $data['type']; }
        if(isset($data['repository'])) { $this->repository = $data['repository']; }
        if(isset($data['downloads'])) {
            $this->downloads = is_array($data['downloads']) ? $data['downloads'] : ['total' => $data['downloads']];
            $this->favers = $data['favers'];
        }

        if(isset($data['versions']) && !empty($data['versions'])) {
            $latest = head($data['versions']);
        } else {
            $latest = $data;
        }
        if(isset($latest['homepage'])) { $this->homepage = $latest['homepage']; }
        if(isset($latest['extra']['readme'])) { $this->readme = $latest['extra']['readme']; }
        if(isset($latest['extra']['images'])) { $this->images = $latest['extra']['images']; }
        if(isset($latest['extra']['icon'])) { $this->icon = $latest['extra']['icon']; }
        if(isset($latest['extra']['title'])) { $this->prettyName = $latest['extra']['title']; }
        if(isset($latest['extra']['providers'])) { $this->providers = $latest['extra']['providers']; }
        else { $this->providers = []; }
        if(isset($latest['keywords'])) { $this->keywords = $latest['keywords']; }
    }

}