#!/usr/bin/env bash

# Now process our CodeKit directory and produce our website
$docs_php "$CORE/webpage.php" "$docs_root_dir/$docs_kit_dir" "$docs_root_dir/$docs_website_dir" "$docs_outline_file" "$CORE"
