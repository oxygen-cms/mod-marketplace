<?php

namespace Oxygen\Marketplace\Loader;

use Oxygen\Marketplace\Package\Package;

interface LoaderInterface {

    /**
     * Searches for packages.
     *
     * @param array $filters
     * @return array
     * @throws LoadingException If the search results can't be loaded.
     */
    public function search(array $filters = []);

    /**
     * Loads details about the specified package.
     *
     * @param string|array $packages
     * @return array
     */
    public function getPackageDetails($packages);

    /**
     * Returns a publicly accessible URL to the specified file inside the package.
     *
     * @param Package $package
     * @param string $filename
     * @return string
     */
    public function getUrl(Package $package, $filename);

    /**
     * Returns the contents of the file inside the package.
     *
     * @param Package $package
     * @param string $filename
     * @return string
     */
    public function getFileContents(Package $package, $filename);

    /**
     * Returns the contents of the package's readme.
     *
     * @param Package $package
     * @return string
     * @throws LoadingException If the request cannot be made
     */
    public function getReadme(Package $package);

}