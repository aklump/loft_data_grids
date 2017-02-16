<?php
/**
 * @file
 * Provides conversion of html to mediawiki
 *
 * @ingroup loft_docs
 * @{
 */

require_once dirname(__FILE__) . '/vendor/autoload.php';

// Convert paths to images to include @page
if (isset($argv[1])) {
    $p = new aklump\loft_parser\MediaWikiParser($argv[1], true);
    $output = $p->parse();
}
print $output;

/** @} */ //end of group: loft_docs
