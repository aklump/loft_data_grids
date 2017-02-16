#!/usr/bin/env bash
echo '' > "$docs_kit_dir/$tmp_file"
$docs_php "$CORE/plugins/kit/page_vars.php" "$docs_outline_file" "$basename"  "$get_version_return" >> "$docs_kit_dir/$tmp_file"
echo '<!-- @include '$docs_tpl_dir'/header.kit -->' >> "$docs_kit_dir/$tmp_file"
cat $file >> "$docs_kit_dir/$tmp_file"
echo '<!-- @include '$docs_tpl_dir'/footer.kit -->' >> "$docs_kit_dir/$tmp_file"

$docs_php "$CORE/iframes.php" "$docs_kit_dir/$tmp_file" "$docs_credentials" > "$docs_kit_dir/$kit_file"
rm "$docs_kit_dir/$tmp_file"
_check_file "$docs_kit_dir/$kit_file"
