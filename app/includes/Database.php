<?php
/**
 * Bitdoc custom database handler.
 *
 * @package Bitdoc
 */
namespace Bitdoc;

class Database
{
    /**
     * Creates the index database.
     *
     * With this method we're creating a custom JSON flat file database with
     * all the relevant document information.
     *
     * @param  object $preParser
     * @param  string $documentDir The Markdown directory.
     * @param  string $filelist List of files to be added to the DB index.
     *
     * @return array  $bitdocDB The new DB index.
     */
    public function createIndexDatabase(
        $preParser,
        $documentDir,
        $filelist
    ) {
        $bitdocDB = array();

        $count = 0;

        // We should avoid unused variables, such as the $value variable, but
        // we cannot avoid it without losing some performance. It's the fastest
        // way.
        foreach ($filelist as $key => $value) {

            $extractedContent = $preParser->preParse($documentDir.'/'.$filelist[$key]);

            // Lets build our DB.
            // We begin with the filename as a unique id.
            $bitdocDB[$count]['filename'] = $filelist[$key];

            // We need a hash of the content of the file in order to check for changes.
            $bitdocDB[$count]['content_hash'] = hash_file('sha1', $documentDir.'/'.$filelist[$key]);

            // We also want a last modified timestamp.
            $bitdocDB[$count]['last_modified'] = filemtime($documentDir.'/'.$filelist[$key]);

            // And the subdirectory to which the file belongs.
            $subDir = explode('/', $filelist[$key]);

            $numSubDir = count($subDir);
            if ($numSubDir > 1) {
                $bitdocDB[$count]['subdirectory'] = $subDir['0'];
            } else {
                // Root directory.
                $bitdocDB[$count]['subdirectory'] = '';
            }

            // And then data from inside the document itself.
            if (isset($extractedContent['date'])){
                $bitdocDB[$count]['date']  = $extractedContent['date'];
            }

            $count++;
        }

        return $bitdocDB;
    }

    /**
     * Update the index database.
     *
     * With this method we're updating the custom JSON flat file database with
     * newly added documents information.
     *
     * @param  object $preParser
     * @param  string $documentDir The Markdown directory.
     * @param  string $filelist List of files to be added to the DB index.
     * @param  string $bitdocDBOld The old index file.
     * @param  array $deletedFilesDiff
     * @param  array $addedFilesDiff
     *
     * @return array $bitdocDBFinal The updated DB index.
     */
    public function updateIndexDatabase(
        $preParser,
        $documentDir,
        $filelist,
        $bitdocDBOld,
        $deletedFilesDiff,
        $addedFilesDiff
    ) {
        $bitdocDBOld = json_decode($bitdocDBOld, true);

        // Lets first deal with any deleted stuff and remove that from our index.
        foreach ($bitdocDBOld as $key => $value) {
            foreach ($deletedFilesDiff as $key2 => $value2) {
                if ($value['filename'] == $key2) {
                    unset($bitdocDBOld[$key]);
                }
            }
        }

        // Next we'll loop through our $filelist array and handle any updated
        // content.
        foreach ($bitdocDBOld as $key => $value) {

            // For each updated file we have to run through our entire old index
            // database and then update fields as needed.
            foreach ($filelist as $key2 => $value2) {

                // When the filenames match, we'll overwrite data.
                if ($value['filename'] == $value2) {

                    $extractedContent = $preParser->preParse($documentDir.'/'.$value2);

                    // Lets build our DB.
                    // We need a unique ID, we'll use the filename including path.
                    $bitdocDBOld[$key]['id'] = hash('sha1', $documentDir.'/'.$value2);

                    // We need a hash of the content of the file in order to check for changes.
                    $bitdocDBOld[$key]['content_hash'] = hash_file('sha1', $documentDir.'/'.$value2);

                    // We also want a last modified timestamp.
                    $bitdocDBOld[$key]['last_modified'] = filemtime($documentDir.'/'.$value2);

                    // Then we need the filename.
                    $bitdocDBOld[$key]['filename'] = $value2;

                    // And the subdirectory to which the file belongs.
                    $subDir = explode('/', $value2);
                    $numSubDir = count($subDir);
                    if ($numSubDir > 1) {
                        $bitdocDBOld[$key]['subdirectory'] = $subDir['0'];
                    } else {
                        // Root directory.
                        $bitdocDBOld[$key]['subdirectory'] = '';
                    }

                    // And then data from inside the document itself.
                    if (isset($extractedContent['date'])){
                        $bitdocDBOld[$key]['date']  = $extractedContent['date'];
                    }
                }
            }
        }

        // Last, but not least, we'll handle newly added content.
        $bitdocDBNew = $this->addIndexDatabase(
            $preParser,
            $documentDir,
            $addedFilesDiff);

        $bitdocDBFinal = array_merge($bitdocDBNew, $bitdocDBOld);

        return $bitdocDBFinal;
    }

    /**
     * Update the index database.
     *
     * With this method we're adding the custom JSON flat file database with
     * newly added documents information.
     *
     * @param  object $preParser
     * @param  string $documentDir The Markdown directory.
     * @param  array $addedFilesDiff
     *
     * @return array $bitdocDB The updated index.
     */
    public function addIndexDatabase(
        $preParser,
        $documentDir,
        $addedFilesDiff
    ) {

        $filelist = array();
        foreach ($addedFilesDiff as $key => $value) {
            $filelist[] = $key;
        }

        $bitdocDB = array();

        $count = 0;

        // With this loop we're creating a custom JSON flat file database with all
        // the relevant document information.
        foreach ($filelist as $key => $value) {

            $extractedContent = $preParser->preParse($documentDir.'/'.$filelist[$key]);

            // Lets build our DB.
            // We need a unique ID, we'll use the filename including path.
            $bitdocDB[$count]['filename'] = $filelist[$key];

            $bitdocDB[$count]['id'] = hash('sha1', $documentDir.'/'.$filelist[$key]);

            // We need a hash of the content of the file in order to check for changes.
            $bitdocDB[$count]['content_hash'] = hash_file('sha1', $documentDir.'/'.$filelist[$key]);

            // We also want a last modified timestamp.
            $bitdocDB[$count]['last_modified'] = filemtime($documentDir.'/'.$filelist[$key]);

            // And the subdirectory to which the file belongs.
            $subDir = explode('/', $filelist[$key]);

            $numSubDir = count($subDir);
            if ($numSubDir > 1) {
                $bitdocDB[$count]['subdirectory'] = $subDir['0'];
            } else {
                // Root directory.
                $bitdocDB[$count]['subdirectory'] = '';
            }

            // And then data from inside the document itself.
            if (isset($extractedContent['date'])){
                $bitdocDB[$count]['date'] = $extractedContent['date'];
            }

            $count++;
        }

        return $bitdocDB;
    }
}
