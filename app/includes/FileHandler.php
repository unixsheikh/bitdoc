<?php
/**
 * File handler
 *
 * @package Bitdoc
 */
namespace Bitdoc;

class FileHandler
{
    /**
     * This function stores the content to file.
     *
     * @param  string $directory The storage directory.
     * @param  string $content The content that needs to be stored to file.
     *
     * @return void
     */
    public function storeFile($directory, $content)
    {
        // First we need to strip the path.
        $fileBuffer = explode('/', $directory);

        // Then we need to know how many subdirectories might exists.
        $numFileBuffer = count($fileBuffer);

        // If $numFileBuffer is equal to 2 we know we're at the root because
        // public_html + filename.md counts 2.
        // $fileBuffer[0] contains our public_html directory and $fileBuffer[1]
        // our filename.
        if ($numFileBuffer == 2) {
            $this->writeFile($fileBuffer[0].'/'.$fileBuffer[1], $content);
        } else {

            // File is not located in root, we traverse the path, write every
            // directory and in the end the file itself.
            // Traversing the path means that we have to remember to:
            // 1. Make the first directory.
            // 2. Then make the next directory inside the first.
            // 3. etc.
            // PHP cannot simply create a file like foo/bar/baz.md in one go.

            // So we need to keep track.
            $traverseCounter = 0;

            // We need to concatenate the path/filename.
            $concatFilename = '';

            foreach ($fileBuffer as $key => $value) {

                // Remember we're traversing an array one slice at a time, so
                // we have to concatenate in order to get the full path.
                if ($traverseCounter == 0) {
                    // First run, we'll just use $value.
                    $concatFilename = $value;
                } else {
                    // Second run or more, so lets start concatenating.
                    $concatFilename = $concatFilename.'/'.$value;
                }

                // As long as we haven't reached the end of the array, keep
                // making directories.
                if ($traverseCounter < ($numFileBuffer - 1)) {
                    if (!is_dir($concatFilename)) {
                        mkdir($concatFilename, 0755);
                    }
                // We have reached the end, we only need the file itself now.
                } else {
                    $this->writeFile($concatFilename, $content);
                }
                $traverseCounter++;
            }
        }
    }

    public function writeFile($file, $content)
    {
        if ($fp = fopen($file, 'w+')) {
            fwrite($fp, $content);
            fclose($fp);
        } else {
            throw new Exception("Unable to write to $file\n", 1);
        }
    }

    public function recurseCopy($src, $dst, $eCounter = 'init')
    {
        // We need to count for errors as this method runs recursively.
        if ($eCounter == 'init') {
            $eCounter = 0;
        }

        if (!$dir = opendir($src)) {
            $eCounter++;
        }

        // Don't verify mkdir, we don't want to overwrite existing directories.
        @mkdir($dst);

        while(false !== ($file = readdir($dir))) {
            if (($file != '.') and ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurseCopy($src . '/' . $file, $dst . '/' . $file, $eCounter);
                } else {
                    if (!copy($src . '/' . $file, $dst . '/' . $file)) {
                        $eCounter++;
                    }
                }
            }
        }
        closedir($dir);

        if ($eCounter == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Generate an MD5 hash string from the contents of a directory.
     *
     * @param  string $directory
     * @return boolean|string
     */
    function hashDirectory($directory)
    {
        if (!is_dir($directory)) {
            return false;
        }

        $files = array();
        $dir = dir($directory);

        while (false !== ($file = $dir->read())) {
            if ($file != '.' and $file != '..') {
                if (is_dir($directory . '/' . $file)) {
                    $files[] = $this->hashDirectory($directory . '/' . $file);
                } else {
                    $files[] = md5_file($directory . '/' . $file);
                }
            }
        }

        $dir->close();

        return md5(implode('', $files));
    }

    /**
     * Store the index and md5 of index to file.
     *
     * @param  string $indexFilename
     * @return boolean
     */
    function storeDatabaseIndex($indexFilename, $bitdocDB)
    {
        // We need a simple MD5 for database file corruption detection.
        $md5 = hash('md5', json_encode($bitdocDB));

        $storeIndex     = file_put_contents($indexFilename, json_encode($bitdocDB));
        $storeIndexMD5 = file_put_contents($indexFilename.'.md5', $md5);

        if ($storeIndex === false OR $storeIndexMD5 === false) {
            return false;
        }

        return true;
    }
}
