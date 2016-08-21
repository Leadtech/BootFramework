<?php

namespace Boot\IO;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class FileUtils.
 *
 * @codeCoverageIgnore
 */
final class FileUtils
{
    /**
     * @param string $directory
     * @param bool   $recursive
     * @param bool   $followSymlinks
     *
     * @return Finder|SplFileInfo[]
     */
    public static function listFiles($directory, $recursive = true, $followSymlinks = false)
    {
        // Possibly invokes SplFileInfo::toString() which returns the full path to the resource.
        $directory = (string) $directory;

        // Check target dir
        if (!self::isDir($directory)) {
            throw new IOException("The directory '{$directory}' is not valid or does not exist!");
        }

        $finder = new Finder();
        $finder
            ->files()
            ->ignoreDotFiles(true)
            ->ignoreUnreadableDirs(true)
            ->ignoreVCS(true)
        ;

        if ($recursive) {
            $finder->in($directory.'/**');
        } else {
            $finder->in($directory);
        }

        if ($followSymlinks) {
            $finder->followLinks();
        }

        return $finder;
    }

    /**
     * @param string|\SplFileInfo $dir         the directory, can be either a string or an instance of SplFileInfo
     * @param string              $extension   the file extension
     * @param bool                $recursive   whether to search recursively
     * @param bool                $followLinks whether links should be followed
     * @param string              $filePattern name pattern without the extension, defaults to *
     *
     * @return Finder|SplFileInfo[]
     *
     * @throws IOException
     */
    public static function findByExtension($dir, $extension, $recursive = true, $followLinks = false, $filePattern = '*')
    {
        $dir = (string) $dir;

        // Check target dir
        if (!self::isDir($dir)) {
            throw new IOException("The directory '{$dir}' is not valid or does not exist!");
        }

        // Sanitize prepended dot, for example both  .php and php should be ok
        $extension = ltrim($extension, '.');

        $finder = self::listFiles($dir, $recursive, $followLinks);

        $finder->name("{$filePattern}.{$extension}");

        return $finder;
    }

    /**
     * @param string[]|Finder|SplFileInfo[] $files
     *
     * @throws IOException
     */
    public static function deleteAll($files)
    {
        (new Filesystem())->remove($files);
    }

    /**
     * @param string|SplFileInfo            $targetDir
     * @param string[]|Finder|SplFileInfo[] $files
     * @param bool                          $overwrite
     *
     * @throws IOException
     */
    public static function moveAll($targetDir, $files, $overwrite = false)
    {
        if (self::isDir($targetDir)) {
            $fs = new Filesystem();
            foreach ($files as $file) {

                // Converts strings to instances of SplFileInfo
                $ffinfo = $file;
                if (is_string($file)) {
                    $ffinfo = new \SplFileInfo($file);
                }

                // Check if file exists, co
                if (!$ffinfo instanceof \SplFileInfo) {
                    throw new IOException("File '{$file}' not found. Unable to move files.");
                }

                $newPath = $targetDir.'/'.$ffinfo->getFilename();
                $fs->rename($ffinfo, $newPath, $overwrite);
            }

            return;
        }

        throw new IOException("Not a valid directory: {$targetDir}");
    }

    /**
     * Restrictive method to return or create a new directory.
     * If anything goes wrong an exception is thrown.
     *
     * @param string $path
     * @param int    $mode
     * @param bool   $recursive
     *
     * @throws IOException
     *
     * @return string
     */
    public static function createDirIfNotExists($path, $mode = 0777, $recursive = true)
    {
        // Check if the given path is a directory.
        if (!is_dir($path)) {
            if (file_exists($path)) {
                throw new IOException('The given resource is a file!');
            }

            if (!mkdir($path, $mode, $recursive)) {
                throw new IOException("Could not create directory: {$path}");
            }
        }

        $dir = realpath($path);
        if (empty($dir)) {
            throw new IOException(
                'The system has encountered an unexpected problem. '.
                "Could not create the '{$path}' directory or could not resolve the location of this resource."
            );
        }

        return $dir;
    }

    /**
     * @param string $directory
     * @param bool   $deleteDir
     */
    public static function truncateFolder($directory, $deleteDir = true)
    {
        // Recursively delete folder contents
        $it = new \RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        if ($deleteDir) {
            rmdir($directory);
        }
    }

    /**
     * When using this method please ensure that the path to the given directory is valid.
     * The intended use for this method is to for example properly clean up resources after a process or test.
     *
     * @param string $directory The path to the directory
     * @param bool   $deleteDir Whether to delete the directory itself
     */
    public static function forceDeleteContentsOnShutDown($directory, $deleteDir = true)
    {
        register_shutdown_function(
            function () use ($directory, $deleteDir) {
                try {
                    static::truncateFolder($directory, $deleteDir);
                } catch (\Exception $e) {
                    // a shutdown function should never throw an exception.
                }
            }
        );
    }

    /**
     * Checks if the directory is valid.
     *
     * @param string|SplFileInfo $directory
     *
     * @return bool
     */
    private static function isDir($directory)
    {
        return !empty($directory) && is_dir((string) $directory);
    }
}
