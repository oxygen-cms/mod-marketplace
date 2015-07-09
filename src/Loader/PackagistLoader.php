<?php

namespace OxygenModule\Marketplace\Loader;

use GuzzleHttp\Client;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Cache\CacheManager;

use OxygenModule\Marketplace\Package\Package;

class PackagistLoader implements LoaderInterface {

    /**
     * GuzzleHttp client.
     *
     * @var Client
     */

    protected $client;

    /**
     * Illuminate cache.
     *
     * @var Cache
     */

    protected $cache;

    /**
     * Constructs the PackagistLoader.
     *
     * @param Client $client
     */
    public function __construct(Client $client, CacheManager $cache) {
        $this->client = $client;
        $this->cache = $cache;
    }

    /**
     * Searches for packages.
     *
     * @param array $filters
     * @return array
     * @throws LoadingException If the request cannot be made
     */
    public function search(array $filters = []) {
        try {
            return $this->cache->remember('marketplace.search.' . http_build_query($filters), 60, function() use($filters) {
                return $this->client->get(
                    'search.json',
                    ['query' => $filters]
                )->json();
            });
        } catch(RequestException $e) {
            throw new LoadingException($e->getMessage());
        }
    }

    /**
     * Loads details about one or more packages.
     *
     * @param string|array $packages
     * @return array
     * @throws NotFoundHttpException if the package doesn't exist
     * @throws LoadingException if the package couldn't be loaded
     */
    public function getPackageDetails($packages) {
        $key = 'marketplace.details.' . implode('|', (array) $packages);
        return $this->cache->remember($key, 10, function() use($packages) {
            if(is_string($packages)) {
                return $this->getSinglePackageDetails($packages);
            } else {
                return $this->getMultiplePackageDetails($packages);
            }
        });
    }

    /**
     * Loads details about the specified package.
     *
     * @param string $package
     * @return array
     * @throws NotFoundHttpException if the package doesn't exist
     * @throws LoadingException if the package couldn't be loaded
     */

    protected function getSinglePackageDetails($package) {
        try {
            return $this->client->get(
               $this->getPackageDetailsUrl($package)
            )->json();
        } catch(RequestException $e) {
            // let's return the usual 404 error page
            if($e->getResponse() !== null && $e->getResponse()->getStatusCode() === 404) {
                throw new NotFoundHttpException();
            } else {
                throw new LoadingException($e->getMessage());
            }
        }
    }

    /**
     * Loads details about multiple packages.
     *
     * @param array $packages
     * @return array
     */

    protected function getMultiplePackageDetails($packages) {
        $requests = [];

        foreach($packages as $package) {
            $requests[] = $this->client->createRequest('GET', $this->getPackageDetailsUrl($package));
        }

        $result = Pool::batch($this->client, $requests);
        $json = [];

        foreach($result->getSuccessful() as $item) {
            $json[] = $item->json()['package'];
        }

        return $json;
    }

    /**
     * Returns the URL to the specified package.
     * @param $package
     * @return string
     */
    public function getPackageDetailsUrl($package) {
        return 'packages/' . $package . '.json';
    }

    /**
     * Returns a publicly accessible URL to the specified file inside the package.
     *
     * @param Package $package
     * @param string $filename
     * @return string
     */
    public function getUrl(Package $package, $filename) {
        $base = str_replace('//github.com', '//raw.github.com', $package->repository);
        $base = str_replace('git://', 'https://', $base);
        $base = preg_replace('/\.git$/', '', $base);
        return $base . '/master/' . $filename;
    }

    /**
     * Returns the contents of the file inside the package.
     *
     * @param Package $package
     * @param string $filename
     * @return string
     * @throws LoadingException If the request cannot be made
     */
    public function getFileContents(Package $package, $filename) {
        try {
            return $this->client->get($this->getUrl($package, $filename))->getBody()->getContents();
        } catch(RequestException $e) {
            throw new LoadingException($e->getMessage());
        }
    }

    /**
     * Returns the contents of the package's readme.
     *
     * @param Package $package
     * @return string
     * @throws LoadingException If the request cannot be made
     */
    public function getReadme(Package $package) {
        if($package->readme !== null) {
            $readme = $package->readme;
        } else if($this->isGithubRepository($package->repository)) {
            return $this->getGithubReadme($this->getGithubPackage($package->repository));
        } else {
            $readme = 'README.md';
        }

        try {
            return $this->client->get($this->getUrl($package, $readme))->getBody()->getContents();
        } catch(RequestException $e) {
            throw new LoadingException($e->getMessage());
        }
    }

    /**
     * Returns the contents of the package's readme.
     *
     * @param Package $package
     * @return string
     * @throws LoadingException If the request cannot be made
     */
    public function getIcon(Package $package) {
        if($this->isGithubRepository($package->repository)) {
            return $this->getGithubIcon(explode('/', $this->getGithubPackage($package->repository))[0]);
        }

        return null;
    }

    /**
     * Determines whether the given repository is a GitHub repository.
     *
     * @param string $repository
     * @return boolean
     */

    protected function isGithubRepository($repository) {
        return strpos($repository, '//github.com') !== false;
    }

    /**
     * Returns the GitHub package from it's repository URL.
     *
     * @param string $repository
     * @return string
     */

    protected function getGithubPackage($repository) {
        $parts = array_reverse(explode('/', trim(explode('.', parse_url($repository)['path'])[0], '/')));
        return $parts[1] . '/' . $parts[0];
    }

    /**
     * Returns the GitHub readme for the specified package.
     *
     * @param $githubPackage
     * @throws LoadingException if the readme can't be loaded
     * @return string
     */

    protected function getGithubReadme($githubPackage) {
        $url = 'https://api.github.com/repos/' . $githubPackage . '/readme';
        try {
            $result = $this->client->get($url)->json();
            if($result['encoding'] === 'base64') {
                return base64_decode($result['content']);
            } else {
                throw new LoadingException('Unknown encoding ' . $result['encoding']);
            }
        } catch(RequestException $e) {
            throw new LoadingException($e->getMessage());
        }
    }

    /**
     * Retrieves the icon for the given GitHub user.
     *
     * @param $user
     * @return string
     * @throws LoadingException if the request failed
     */

    protected function getGithubIcon($user) {
        $url = 'https://api.github.com/users/' . $user;
        try {
            $result = $this->client->get($url)->json();
            return $result['avatar_url'];
        } catch(RequestException $e) {
            throw new LoadingException($e->getMessage());
        }
    }

}