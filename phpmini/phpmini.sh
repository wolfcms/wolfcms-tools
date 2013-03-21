#!/bin/bash

###
### phpmini.sh 
###
### intended to minify php files in the given directory and all subdirs.
### it is NOT intended to obfuscate. It is for filesize reduction only.
###
### written for Wolf CMS by Martijn van der Kleijn, 2012
### contact <martijn.niji@gmail.com>
###
### script licensed under MIT
###

# Displays help message
function show_help {
    cat <<HELPEND

Usage: `basename $0` options
	-d <directory>	Use the given directory as the search base, otherwise the current working directory is assumed.
	-e <extension>	Search for PHP files with given extension. Defaults to '.php'.
	-k		Keep the original files by appending '.orig' to the filename.
	-K <extension>	Use the given extension when keeping the original files instead of the default '.orig' extension.
	-v		Be verbose and display what is happening.
	-t		Test mode; does not run any command but simply tells what it would have done.

	-h		Display help message

HELPEND
}

# Setup some vars
OPTION_STRING='h?vtkK:d:e:'
BASE_DIR=.
BAK_EXT=.orig
PHP_EXT=.php
keep=0
keepe=0
verbose=0
testing=0

while getopts ${OPTION_STRING} opt; do
  case $opt in
    h|\?)
      show_help >&2;
      exit 0;
      ;;
    d)
      BASE_DIR=${OPTARG};
      ;;
    e)
      PHP_EXT=${OPTARG};
      ;;
    k|K)
      if [ $opt = "K" ]; then
          keepe=1;
          BAK_EXT="${OPTARG}";
      else
          keep=1;
      fi
      ;;
    v)
      verbose=1;
      ;;
    t)
      testing=1;
      verbose=1;
      ;;
    *)
      echo "Invalid option: -$opt ${OPTARG}" >&2;
      show_help;
      exit 0;
      ;;
  esac
done


# Do the actual work
find ${BASE_DIR} -type f -name "*${PHP_EXT}" | while read FILE
do
    if [ $testing -eq 1 ]; then
        echo "TEST MODE: "
    else
        echo "MINIFYING ${FILE}"
    fi

    # create minified versions
    if [ $testing -eq 0 ]; then
        php -w ${FILE} > ${FILE}.mini
    fi
    if [ $verbose -eq 1 ]; then
        echo " - Created minified version of ${FILE}"
    fi

    if [ $keep -eq 1 ] || [ $keepe -eq 1 ]
    then
        if [ $testing -eq 0 ]; then
            mv ${FILE} ${FILE}${BAK_EXT}
        fi
        if [ $verbose -eq 1 ]; then
            echo " - Moved original file to ${FILE}${BAK_EXT}"
        fi
    else
        if [ $testing -eq 0 ]; then
            rm -f ${FILE}
        fi
        if [ $verbose -eq 1 ]; then
            echo " - Removed original file"
        fi
    fi

    # Rename mini to orig name
    if [ $testing -eq 0 ]; then
        mv ${FILE}.mini ${FILE}
    fi
    if [ $verbose -eq 1 ]; then
        echo " - Renamed minified file ${FILE}.mini to ${FILE}"
    fi
done
