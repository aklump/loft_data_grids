#!/usr/bin/env bash

# Prepare the variables
json=$($docs_php "$CORE/plugins/twig/vars.php" "$docs_outline_file" "$basename"  "$get_version_return")

destination="$PWD/$docs_website_dir/$html_file"
$docs_php "$CORE/plugins/twig/build.php" "$docs_tpl_dir" "$file" "$destination" "$json"
_check_file "$destination"

