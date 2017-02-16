<?php
/**
 * @file
 * An include file that is expecting the following vars
 *
 * - $info
 *   Keyed by filename (no path, no extension)
 *   Values are arrays with keys:
 *   - parent
 *   - weight
 *   - title
 * - $json_file string The output path of the json file.
 *
 * @see  files_to_json.inc
 * @see  ini_to_json.php
 */
$g = new \AKlump\Data\Data();

if (!empty($json_file)) {

    $json = array(
        'chapters' => array(),
        'sections' => array(),
        'settings' => array(),
    );

    // If we have the merge file, merge it in.
    $merge_json = null;
    if (!empty($merge_file) && file_exists($merge_file) && ($merge_json = file_get_contents($merge_file))) {
        if (!($merge_json = json_decode($merge_json, true))) {
            echo "merge file: $merge_file contains invalid json";
        }
        else {
            $json = json_outline_merge($json, $merge_json);
        }
    }

    //
    //
    // Search support
    
    // TODO Need to add support for search === false
    $search_results_file = $source_dir . '/search--results.md';
    if (file_exists($search_results_file)) {
        // Presence of the file overrides settings.
        $g->fill($json, 'settings.search', 'tipuesearch');
    }
    // Write the search results file if settings say to
    if (!empty($json['settings']['search']) && file_exists($search_results_file) && !file_get_contents($search_results_file)) {
        file_put_contents($search_results_file, '# Search Results');
    }

    $chapter_sort = 0;
    $section_sort = 0;
    $chapter_index = array();
    foreach ($info as $file => $data) {
        if (empty($data['title'])) {
            continue;
        }

        $chapter = null;
        $section = array(
            'id'    => clean_id($file),
            'title' => $data['title'],
            'file'  => isset($data['file']) ? $data['file'] : $file . get_md_source_file_extension($file),
            'sort'  => isset($data['weight']) ? $data['weight'] : $section_sort++,
        );

        $chapter_id = clean_id($data['parent']);
        if (!empty($data['parent'])) {
            $section['chapter'] = $chapter_id;
            if (!in_array($chapter_id, $chapter_index)) {
                $chapter_index[] = $chapter_id;
                $chapter = array(
                    'id'    => $chapter_id,
                    'title' => clean_title($data['parent']),
                    'sort'  => $chapter_sort++,
                );
            }
        }
        $json['sections'][] = $section;
        if ($chapter) {
            $json['chapters'][] = $chapter;
        }
    }

    // Merge json one more time to trump the auto or .ini
    $json = $merge_json ? json_outline_merge($json, $merge_json) : $json;

    // Write out the json file.
    file_put_contents($json_file, json_encode($json, JSON_PRETTY_PRINT));
}
else {
    echo "Missing \$json_file.";
}
