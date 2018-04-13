#!/usr/bin/php -q
<?php

/*
 * This script is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

/**
 * This is a cli script:
 *
 * Clone TYPO3 Extension
 *
 * Copies an existing extension to a new extension key.
 *
 * All files and folders of a given TYPO3 extension folder are copied to a new extension folder.
 * In every file the appearance of the extension key is replaced in it's variants like: my_ext, MyExt, ...
 *
 * WARNING: the conversion is done by simple search and replace and might produce wrong results and could break your code!
 * Review the changed files!
 *
 * @author Rene Fritz <r.fritz@colorcube.de>
 * @since  2016
 * @see <https://github.com/colorcube/typo3_clone_extension/>
 */
class clone_extension {

    // don't do any string replace in these file types
    const FILE_COPY_ONLY = array('png', 'jpg', 'jpeg', 'jp2', 'gif', 'pdf');

    // don't do any string replace in these directories
    const DIR_COPY_ONLY = array('.git', 'node_modules');

    /**
     * starts the cli
     */
    public function __construct()
    {
        $args = $_SERVER['argv'];

        if (!isset($args[1]) || !isset($args[2])) {
            $this->usage();
            $this->description();
            exit (1);
        }

        $source = $args[1];
        $dest = $args[2];

        if (!file_exists($source)) {
            echo "Source folder does not exist!\n";
            $this->usage();
            exit (1);
        }
        if (!is_dir($source)) {
            echo "Source is not a folder!\n";
            $this->usage();
            exit (1);
        }

        if (!file_exists($source.'/ext_emconf.php')) {
            echo "The source seems not to be an extension (ext_emconf.php missing)!\n";
            $this->usage();
            exit (1);
        }

        if (file_exists($dest)) {
            echo "Destination exists already!\n";
            $this->usage();
            exit (1);
        }

        $this->cloneExtension($source, $dest);

        exit (0);
    }


    /**
     * print description of cli
     */
    protected function description()
    {
        echo "\nClone TYPO3 Extension

Copies an existing extension to a new extension key.

All files and folders of a given TYPO3 extension folder are copied to a new extension folder.
In every file the appearance of the extension key is replaced in it's variants like: my_ext, MyExt, ...

WARNING: the conversion is done by simple search and replace and might produce wrong results and could break your code!
Review the changed files!\n";
    }


    /**
     * print usage message
     */
    protected function usage()
    {
        echo "\nusage: " . basename($_SERVER['argv'][0]) . " path/to/my_ext path/to/new_ext\n";
    }


    /**
     * The processing happens here
     *
     * @param string $source source directory
     * @param string $dest destination directory
     */
    protected function cloneExtension($source, $dest)
    {
        $source = rtrim($source, DIRECTORY_SEPARATOR);
        $dest = rtrim($dest, DIRECTORY_SEPARATOR);

        // get extension keys from paths
        $actualkey = basename($source);
        $newkey = basename($dest);

        $source = $source . DIRECTORY_SEPARATOR;
        $dest = $dest . DIRECTORY_SEPARATOR;

        // that's the strings we want to replace in all variations
        $renames = array();
        // extension key with old »tx_«-prefix  – tx_oldkey → tx_newkey
        $renames['tx_' . str_replace('_', '', $actualkey)] = 'tx_' . str_replace('_', '', $newkey);
        // extension key (Folders, Files, Keys) – old_key → new_key
        $renames[$actualkey] = $newkey;
        // extension-key with underscores as minus (Composer) – old-key → new-key
        $renames[str_replace('_', '-', $actualkey)] = str_replace('_', '-', $newkey);
        // Human Readable Key (Labels) – Old Key → New Key
        $renames[ucwords(str_replace(array('_', '-'), ' ', $actualkey))] = ucwords(str_replace(array('_', '-'), ' ', $newkey));
        // Upper Camel Case (Namespaced Classes) – OldKey → NewKey
        $renames[static::underscoredToUpperCamelCase($actualkey)] = static::underscoredToUpperCamelCase($newkey);
        // Upper Camel Case (Namespaced Classes) with old »Tx«-prefix – TxOldkey → TxNewkey
        $renames[static::underscoredToUpperCamelCase('tx_' . str_replace('_', '', $actualkey))] = static::underscoredToUpperCamelCase('tx_' . str_replace('_', '', $newkey));

        // for the db table renaming hint
        $dbPrefixOld = str_replace('_', '', $actualkey);
        $dbPrefixNew = str_replace('_', '', $newkey);

        echo "Copy Extension: {$source} > {$dest}\n\n";
        echo "with renaming strings:\n";
        foreach ($renames as $renameFrom => $renameTo) {
            echo "{$renameFrom} > {$renameTo}\n";
        }
        echo "\n";

        $search = array_keys($renames);
        $replace = array_values($renames);

        $this->copyAllFilesWithRename($source, $dest, $search, $replace);

        echo "\n";

        echo "You might want to update the git index with:\ngit add -A\n\n";

        echo "If you want to rename the database tables. Generate rename tables sql with this query (insert datbase name!):\n";
        echo "SELECT CONCAT( 'RENAME TABLE ', table_name, ' TO ', REPLACE(table_name,'$dbPrefixOld','$dbPrefixNew'), ';' )
FROM information_schema.tables
WHERE table_schema = '###MY-DB-NAME###' AND table_name LIKE '%$dbPrefixOld%';";
        echo "\n\n";
    }

    /**
     * walk through directories and copy files
     *
     * @param string $from
     * @param string $to
     * @param array $search
     * @param array $replace
     */
    protected function copyAllFilesWithRename($from, $to, $search, $replace)
    {
        if (!is_dir($to)) {
            if (!mkdir($to)) {
                echo "Error: could not create dir: {$to}\n";
                exit (1);
            }
        }

        $handle = opendir($from);
        if (!$handle) {
            echo "Error: directory '{$from}' not exists!\n";
            exit (1);
        }

        while (false !== ($file = readdir($handle))) {

            // this directory shouldn't be processed - just copy to target
            if (is_dir($from . $file) && in_array($file, static::DIR_COPY_ONLY)) {
                $this->copyAllFilesWithRename($from . $file . '/', $to . $file . '/', false, false);
                // let's process the sub dir
            } elseif (is_dir($from . $file) && $file != "." && $file != "..") {
                $this->copyAllFilesWithRename($from . $file . '/', $to . $file . '/', $search, $replace);
            } elseif (is_file($from . $file)) {
                // rename file name
                if ($search)
                    $filenew = str_replace($search, $replace, $file);
                else
                    $filenew = $file;

                // this file type shouldn't be processed - just copy to target
                if (!$search || in_array(pathinfo($file, PATHINFO_EXTENSION), static::FILE_COPY_ONLY)) {
                    if (!copy($from . $file, $to . $filenew)) {
                        echo 'Error: could not write ' . $to . $filenew;
                        exit (1);
                    }
                    echo 'Write without processing ' . $to . $filenew . "\n";

                } else {
                    // process content of file
                    $ft = fopen($to . $filenew, "w");
                    if ($ft) {
                        $fs = fopen($from . $file, "r");
                        while (!feof($fs)) {
                            $buffer = fgets($fs, 4096);
                            if ($search) $buffer = str_replace($search, $replace, $buffer);
                            fwrite($ft, $buffer);
                        }
                        echo 'Write with processing ' . $to . $filenew . "\n";
                        fclose($fs);
                        fclose($ft);
                    } else {
                        echo 'Error: could not write ' . $to . $filenew;
                        exit (1);
                    }
                }

            }
        }
        closedir($handle);
    }

    /**
     * Returns a given string with underscores as UpperCamelCase.
     * Example: Converts blog_example to BlogExample
     *
     * @param string $string String to be converted to camel case
     * @return string UpperCamelCasedWord
     */
    protected static function underscoredToUpperCamelCase($string)
    {
        $upperCamelCase = str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($string))));
        return $upperCamelCase;
    }
}

$cli = new clone_extension;
