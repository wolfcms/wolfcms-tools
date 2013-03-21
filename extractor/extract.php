#!/usr/bin/php

<?php
/*
 * Language String Extractor for Wolf CMS plugins. <http://www.wolfcms.org>
 * Copyright (C) 2013 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is licensed under the GNU GPLv3 license.
 */

/**
 *
 * This script will generate a file with translatable strings that can be
 * imported into a Transifex project as a source language resource.
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @copyright Martijn van der Kleijn, 2013
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 */
 
 

 

/**
 */
final class Extractor {

    private static $PLUGINS_ROOT = '';
    
    
    public final function __construct($root) {
        self::$PLUGINS_ROOT = $root;
    }


    public final function extract() {
        $id = '';
        $path = '';
    
        //$files = array();
        //$basedir = self::$PLUGINS_ROOT;
        //$dirs = $this->listdir($basedir, true);

        //foreach ($dirs as $id => $path) {
            $tmp = array();
            $strings = array();
            $fsize = filesize($path);

            if ($fsize > 0) {
                $fh = fopen($path, 'r');
                $data = fread($fh, $fsize);
                fclose($fh);

                if (strpos($data, '__(\'')) {
                    $data = substr($data, strpos($data, '__(\'')+4);
                    $tmp = explode('__(\'', $data);

                    foreach ($tmp as $string) {
                        $endpos = strpos($string, '\'');
                        while (substr($string, $endpos-1, 1) == "\\") {
                            $endpos = $endpos + strpos(substr($string, $endpos+1, strpos($string, '\'')), '\'') + 1;
                        }
                        $strings[] = substr($string, 0, $endpos);
                    }

                    if (sizeof($strings) > 0) {
                        $files[$path] = $strings;
                    }
                }

                if (strpos($data, '__("')) {
                    $data = substr($data, strpos($data, '__("')+4);
                    $tmp = explode('__("', $data);

                    foreach ($tmp as $string) {
                        $endpos = strpos($string, '"');
                        while (substr($string, $endpos-1, 1) == "\\") {
                            $endpos = $endpos + strpos(substr($string, $endpos+1, strpos($string, '"')), '"') + 1;
                        }
                        $strings[] = substr($string, 0, $endpos);
                    }

                    if (sizeof($strings) > 0) {
                        $files[$path] = $strings;
                    }
                }
            }
        // }

        // Write out templates
        foreach ($files as $file => $strings) {
            echo "\n\n\n\n";
            echo 'File: '.$file;
            writeTemplate($file, $strings);
        }
        
        // Write out templates
        $outhandle = @fopen('out.txt', 'a');
        fwrite($outhandle, $buffer."\n");
        
        fclose($outhandle); // Close the file.
    }

    private final function listdir($start_dir='.', $plugins = false) {
        $files = array();
        if (is_dir($start_dir)) {
            $fh = opendir($start_dir);
            while (($file = readdir($fh)) !== false) {
                # loop through the files, skipping . and .., and recursing if necessary
                if (strcmp($file, '.')==0 || strcmp($file, '..')==0) {
                    continue;
                }
                $filepath = $start_dir . '/' . $file;
                if ($plugins) {
                    if ( is_dir($filepath) && !strpos($filepath, 'i18n') ) {
                        $files = array_merge($files, $this->listdir($filepath, $plugins));
                    }
                    else {
                        if (!strpos($filepath, 'I18n') && strpos($filepath, '.php', strlen($filepath) - 5) || strpos($filepath, '.phtml', strlen($filepath) - 7)) {
                            array_push($files, $filepath);
                        }
                    }
                }
                else {
                    if ( is_dir($filepath) && !strpos($filepath, 'i18n') && !strpos($filepath, 'plugins') ) {
                        $files = array_merge($files, $this->listdir($filepath, $plugins));
                    }
                    else {
                        if (!strpos($filepath, 'I18n') && strpos($filepath, '.php', strlen($filepath) - 5) || strpos($filepath, '.phtml', strlen($filepath) - 7)) {
                            array_push($files, $filepath);
                        }
                    }
                }
            }
            closedir($fh);
        }
        else {
            # false if the function was called with an invalid non-directory argument
            $files = false;
        }

        return $files;
    }
    
    /**
     * Outputs the plugin template.
     *
     * @param string $pluginname
     * @param array  $strings
     */
    private final function writeTemplate($pluginname, $strings) {
        echo '<?php

        <?php

        /**
         * Wolf CMS '.$pluginname.' plugin language file
         *
         * @package Translations
         */

        return array(
        ';

        $strings = removeDoubles($strings);
        sort($strings);

        foreach ($strings as $string) {
            echo "    '".$string."' => '".$string."',\n";
        }    

        echo "    );\n\n\n\n\n\n";
    }

    /**
     * Removes any double entries in the array.
     *
     * @param array $array
     * @return array 
     */
    private final function removeDoubles($array) {
        $result = array();
            
        foreach ($array as $string) {
            if (!in_array($string, $result))
            $result[] = $string;
        }

        return $result;
    }

}
