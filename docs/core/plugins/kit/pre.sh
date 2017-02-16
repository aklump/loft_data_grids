#!/usr/bin/env bash
# Create the index file.
$docs_php "$CORE/includes/home.php" "$docs_outline_file" "$docs_tpl_dir" >> "$docs_tmp_dir/index.html"
_check_file "$docs_tmp_dir/index.html"
