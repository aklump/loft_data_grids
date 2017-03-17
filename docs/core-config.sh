#!/bin/bash
#
# @file
# Configuration

##
# An array of output formats to disable, if any
#
#disabled = "website drupal html text mediawiki"
disabled = "drupal html text mediawiki"

##
# File path to the php you want to use for compiling
#
php = $(which php)
#php = '/Applications/MAMP/bin/php/php5.3.14/bin/php'

##
# Lynx is required for output of .txt files
#
lynx = $(which lynx)

##
# The drupal credentials for a user who can access your iframe content
#
#credentials = "http://user:pass@www.my-site.com/user/login";

##
# The name of the drupal module to build advanced help output for, if
# applicable
#
#drupal_module = 'my_module';

##
# The location of the advanced help output; this location is used in place of
# the default, if enabled.  It is relative to the directory containing core-config.sh.
#
#drupal_dir = '../help'

##
# The file path to an extra README.txt file; when README.md is compiled and
# this variable is set, the .txt version will be copied to this location.  Notice the second path will also copy the md version.  If this is not desired then omit the second path
#
# This MUST be a directory relative to the directory containing core-config.sh
#
README = '../README.md'

##
 # The file path to an extra CHANGELOG.txt file; when CHANGELOG.md is compiled and
 # this variable is set, the .txt version will be copied to this location.
 #
#CHANGELOG = '../CHANGELOG.txt ../CHANGELOG.md'

#root_dir = ""

#
# Defines pre/post hooks, shell or php scripts to call, space separated.  These must be placed in a directory called 'hooks' one level above source.
#
#pre_hooks = "pre_compile.sh pre_compile.php"
#post_hooks = "post_compile.sh post_compile.php"

#
# The path to a .info file or a .json file containing 'version' as a first level key, whose value indicates the documentation version.
# This can be relative to the directory containing core-config.sh or absolute if it begins with a /
version_file = "loft_data_grids.info"

#
# These paths are relative to the directory containing core-config.sh.
#website_dir = 'public_html'
#html_dir = 'html'
#mediawiki_dir = 'mediawiki'
#text_dir = 'text'
#drupal_dir = 'advanced_help'

#
# This controls which file extensions are run throught the markdown parser and wrapped with the header/footer tpls.
#partial_extension = '.md'

