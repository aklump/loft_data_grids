<?php
/**
 * @file
 * Replace iframes with actual content for offline html
 *
 * @ingroup loft_docs
 * @{
 */
$temp_dir = sys_get_temp_dir();

// Drupal credentials for logging in to the account that can access the iframes
//http://username:password@hostname/path?arg=value#anchor';
$login = (object) parse_url($argv[2]);
$errors = array();

if (isset($argv[1])
    && ($output = file_get_contents(getcwd() . '/' . $argv[1]))
    && (preg_match_all('/<iframe\s+src="([^"]+)".*?<\/iframe>/', $output, $iframes))
) {

    $data = array(
        'name'    => $login->user,
        'pass'    => $login->pass,
        'form_id' => 'user_login',
        'op'      => 'Log in',
    );
    $cookie_file = tempnam($temp_dir, 'cookie');
    $url = $login->scheme . "://" . $login->host . $login->path;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $contents = curl_exec($ch);
    curl_close($ch);

    foreach (array_keys($iframes[0]) as $key) {
        $url = $iframes[1][$key];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        //curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        $contents = curl_exec($ch);
        curl_close($ch);
        if (empty($contents)) {
            $errors[] = "Could not convert iframe content at: $url";
        }
        else {
            $output = str_replace($iframes[0][$key], $contents, $output);
        }
    }

    // Remove the session cookie
    unlink($cookie_file);
}

print $output;
