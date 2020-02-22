#!/usr/bin/env bash
# Prepare the variables
json=$($docs_php "$CORE/plugins/twig/vars.php" "$docs_outline_file" "$basename"  "$get_version_return")

destination="$docs_website_dir/index.html"
$docs_php "$CORE/plugins/twig/build.php" "$docs_tpl_dir" "index.html" "$destination" "$json"
_check_file "$destination"

