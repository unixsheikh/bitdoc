<?php
$begin = microtime(true);

require 'app/config.php';
require 'vendor/autoload.php';

if ($config['display_errors']) {
    // Passing in the value -1 will show every possible error even when new
    // levels and constants are added in future PHP versions.
    error_reporting(-1);
    ini_set("display_errors", 1);

    // Repeated errors must occur in the same file on the same line unless
    // ignore_repeated_source is set true.
    ini_set('ignore_repeated_errors', 1);

    // When this setting is on you will not log errors with repeated messages
    // from different files or sourcelines.
    // When ignoring repeated errors, the source of error plays a role in
    // determining if errors are different.
    ini_set('ignore_repeated_source', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('ignore_repeated_errors', 1);
    ini_set('ignore_repeated_source', 1);
}

$cprint = new Bitdoc\ConsolePrint();
$cprint->printtnl('Initializing', 'OK');
$cprint->print('Scanning directories', 'OK');

// If it's not there, we need to create the index directory for the database.
if (!is_dir($config['index_directory'])) {
    if (mkdir($config['index_directory'])) {
        $cprint->print('Create new database index directory', 'OK');
    } else {
        $cprint->print('Create new '.$config['index_directory'].' directory', 'ERROR', 'red');
        $e_message = 'Could not create '.$config['index_directory'].', please verify write permissions!';
        echo "\n".$e_message."\n\n";
        exit(1);
    }
}

// We need to scan the Markdown directory in order to detects new
// files or changes to old files.
if (is_dir($config['markdown_directory'])) {
    $markdown_files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($config['markdown_directory']));
    $cprint->print('Document directory found', 'OK');
} else {
    $cprint->print('Finished scanning directories', 'ERROR', 'red');
    $e_message = "Could not find any documents in '".$config['markdown_directory']."'.\n";
    $e_message .= "Create the '".$config['markdown_directory']."' directory and populate it!\n";
    echo "\n$e_message\n\n";
    exit(1);
}

// Let's make an array and use that as a filelist for the Markdown documents.
$filelist = array();
foreach ($markdown_files as $file) {
    if ($file->isDir()){
        continue;
    }

    // Whitelist filetype.
    switch ($file->getExtension()) {
        case 'md':
        case 'markdown':
            $filename = str_replace($config['markdown_directory'].'/', '', $file->getPathname());
            $filelist[] = $filename;
            $filelist_last_modified[$filename] = filemtime($file->getPathname());
            break;
        default:
            // The file extention doesn't match any of the supported types.
            // We skip it!
            break;
    }
}

// How many Markdown files did we get?
$num_filelist = count($filelist); 
if ($num_filelist == 0) {
    $cprint->print('Scanning documents', 'ERROR', 'red');
    $e_message = "Could not find any documents in '".$config['markdown_directory']."'.\n";
    $e_message .= "Populate ".$config['markdown_directory']." with documents in order to continue!\n";
    echo "\n$e_message\n\n";
    exit(1);
}

// Now we need to look for our index database. If the database exists we need
// to detect changes in Markdown files. If the database doesn't exist, we
// create a new.
$db = new Bitdoc\Database();

// We need to store files to disk, so let's get a handler for that.
$file_handler = new Bitdoc\FileHandler();

// We also need the pre-parser.
$pre_parser = new Bitdoc\PreParser();

// We use this variable to keep track of whether we need to created a new
// index database.
$create_new_index_db = true;

if (file_exists($config['index_directory'].'/bitdoc_db_index.json')) {
    // The index database exists, let's asume it's in working order.
    $create_new_index_db = false;

    // Let's read it.
    $bitdoc_db_index = file_get_contents($config['index_directory'].'/bitdoc_db_index.json');

    // A MD5 sum file was created when the database was created, that way we can
    // validate, to some degree, that the index file hasn't been corrupted.
    $bitdoc_db_index_md5_file = '';
    if (file_exists($config['index_directory'].'/bitdoc_db_index.json'.'.md5')) {
        $bitdoc_db_index_md5_file = file_get_contents($config['index_directory'].'/bitdoc_db_index.json.md5');
    }

    // Let's check for corruption.
    if ($bitdoc_db_index_md5_file != hash('md5', $bitdoc_db_index)) {
        // The file has been corrupted, we'll have to re-build the index and
        // create a new md5 file.
        $create_new_index_db = true;
    }
}

// There is no index, or it was corrupt, we need to build a new.
if ($create_new_index_db) {
    // We iterate over the Markdown files and get a new index database which
    // we then store to disk.
    $bitdoc_db_index = $db->createIndexDatabase($pre_parser, $config['markdown_directory'], $filelist);

    if (!$file_handler->storeDatabaseIndex($config['index_directory'].'/bitdoc_db_index.json', $bitdoc_db_index)) {
        $cprint->print('Create index', 'ERROR', 'red');
        $e_message = "Could not write to '".$config['index_directory']."'.\n";
        echo "\n$e_message\n\n";
        exit(1);
    }
}

// If a new index wasn't created we need to take compare the old index with the
// current Markdown content and determine if any documents have been added,
// changed or deleted.
if (!$create_new_index_db) {
    $bitdoc_db_index_data = json_decode($bitdoc_db_index, true);

    $num_data = count($bitdoc_db_index_data);

    // We'll begin by checking for the last modification date.
    // The last modification date is a field that gets inserted into the JSON
    // index database when each file is scanned and the index is build or
    // updated.
    $last_modified_old = array();

    for ($i=0; $i < $num_data; $i++) {
        $last_modified_old[$bitdoc_db_index_data[$i]['filename']] = $bitdoc_db_index_data[$i]['last_modified'];
    }

    $files_present = array();

    // array_intersect_key catches those files that exists in both arrays based
    // upon the key and not the last modified value.
    // We've got the $filelist_last_modified from the filescan of the document
    // directory at the beginning of bitdoc.php.
    $files_present = array_intersect_key($last_modified_old, $filelist_last_modified);

    // Let's get a list of deleted files.
    $deleted_files_diff = array();
    $deleted_files_diff = array_diff_assoc($last_modified_old, $files_present);

    // Then added files, if any.
    $added_files_diff = array();
    $added_files_diff = array_diff_key($filelist_last_modified, $last_modified_old);

    // Then a list of files that has been updated, if any.
    $updated_files_diff = array();
    $updated_files_diff = array_diff_assoc($last_modified_old, $filelist_last_modified);
    $updated_files_diff = array_diff($updated_files_diff, $deleted_files_diff);

    // For easy debugging.
    // echo "Deleted files:\n";
    // print_r($deleted_files_diff);

    // echo "Added files:\n";
    // print_r($added_files_diff);

    // echo "Updated files:\n";
    // print_r($updated_files_diff);
}

// Let's keep track of the building of HTML files.
$html_build_status = 'nothing';

// If the HTML directory doesn't exist, we try to create it and set the HTML build
// status to "rebuild" in order to build all the HTML files.
if (!is_dir($config['html_directory'])) {
    if (mkdir($config['html_directory'])) {
        $cprint->print('Create new '.$config['html_directory'].' directory', 'OK');
    } else {
        $cprint->print('Create new '.$config['html_directory'].' directory', 'ERROR', 'red');
        $e_message = 'Could not create '.$config['html_directory'].', please verify write permissions!';
        echo "\n".$e_message."\n\n";
        exit(1);
    }

    // This is the KISS solution - if any template include files exist, just
    // copy them to the HTML directory.
    if (is_dir($config['template'].'/includes')) {
        if ($file_handler->recurseCopy($config['template'].'/includes', $config['html_directory'].'/includes')) {
            $cprint->print('Created include files for HTML', 'OK');
        } else {
            $cprint->print('Created include files for HTML', 'ERROR', 'red');
            $e_message = 'Could not create include files in '.$config['html_directory'].', please verify
            write permissions!';
            echo "\n".$e_message."\n\n";
            exit(1);
        }
    }

    // We're creating or rebuilding all the HTML files.
    $html_build_status = 'rebuild';

// The HTML directory already exists.
} elseif (!empty($updated_files_diff) or !empty($deleted_files_diff) or !empty($added_files_diff)) {
    // Some files has been modified, added, or deleted.
    $html_build_status = 'update';
} else {
    // Nothing has changed.
    $e_message = 'No new or modified content was detected. Exiting.';
    echo "\n".$e_message."\n\n";
    exit(1);
}

$cprint->print('Finished scanning directories', 'OK');
$cprint->print('Beginning to build HTML', 'OK');

// Let's make a counter for the number of HTML files that has been created,
// added, or deleted.
$html_num_files = 0;

// We can just change the $filelist array to only contain the canged files
// and then just build those.
if ($html_build_status == 'update') {

    // We'll merge the $updated_files_diff and $added_files_diff to create a new
    // filelist, if any files was added or modified.
    $new_files_list_diff = array_merge($added_files_diff, $updated_files_diff);

    $filelist = array();
    foreach ($new_files_list_diff as $key => $value) {
        $filelist[] = $key;
    }

    $cprint->print('Updating HTML files', 'OK');

    // Let's delete old files from the HTML directory if needed.
    if (!empty($deleted_files_diff)) {
        $num_delete_files = 0;
        foreach ($deleted_files_diff as $key => $value) {

            $ext = pathinfo($config['html_directory'].'/'.$key, PATHINFO_EXTENSION);
            $key = str_replace('.'.$ext, '.html', $key);

            if (file_exists($config['html_directory'].'/'.$key)) {
                unlink($config['html_directory'].'/'.$key);
                $num_delete_files++;
            }
        }

        $cprint->print($num_delete_files.' file(s) has been deleted', 'OK');
    }
}

// We convert to HTML and extract Yaml options.
foreach ($filelist as $key => $value) {

    // Get the file extention.
    $fileinfo = new SplFileInfo($filelist[$key]);
    $file_extension = $fileinfo->getExtension();

    // We first parse a file through our basic parser. It will pull out Yaml
    // options from the document, such as a "title", a "posted-date", etc.
    // Then it will clean the files up and remove those options before they are
    // feeded to the document parser.
    $yaml = $pre_parser->preParse($config['markdown_directory'].'/'.$filelist[$key]);

    // Support for other Markup languages can be added here if needed by adding
    // other extensions and using other parsers. The whilelist switch above
    // then needs to be changed as well.
    switch ($file_extension) {
    case 'md':
    case 'markdown':
        // Now we ship the cleaned up Markdown file to the Markdown parser. It will
        // convert the Markdown to HTML.
        $markdown_extra = new ParsedownExtra;
        $main_content = $markdown_extra->text($yaml['data']);

        break;
    default:
    }

    // This is a rudimentary solution, but it's fast and simple. Since the
    // template file contains PHP logic to handle the Yaml content, we can
    // simply include it here.
    // The template puts all the HTML markup into a variable called $html.
    require $config['template'].'/template.php';

    // Now we have collected the information we need, so we can store the newly
    // converted HTML file where it belongs.
    // The $html variable contains the finished HTML page made by the template
    // file.
    // Before we store the file, we just need to rename the file extension.
    $markdown_ext = array('.md', '.markdown');
    $filename = str_replace($markdown_ext, '.html', $filelist[$key]);
    
    $file_handler->storeFile($config['html_directory'].'/'.$filename, $html);

    // Let's increment HTML build counter.
    $html_num_files++;
}

// We defer the updating of the index database to the very end in case
// something goes wrong during parsing. If the parsing crashes there is no need
// to store the updated index.
if ($html_build_status == 'update') {
    $bitdoc_db_index = $db->updateIndexDatabase(
        $pre_parser,
        $config['markdown_directory'],
        $filelist,
        $bitdoc_db_index,
        $deleted_files_diff,
        $added_files_diff);

    if (!$file_handler->storeDatabaseIndex($config['index_directory'].'/bitdoc_db_index.json', $bitdoc_db_index)) {
        $cprint->print('Update index', 'ERROR', 'red');
        $e_message = "Could not write to '".$config['index_directory']."'.\n";
        echo "\n$e_message\n\n";
        exit(1);
    }
}

$cprint->print($html_num_files.' document file(s) has been added or updated', 'OK');

$end = microtime(true) - $begin;
$end = round($end, 4);
echo "\nOperation completed in: $end seconds.\n\n";
