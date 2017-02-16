<?php
/**
 * @file
 * Tests for the advanced_help.php
 *
 * @ingroup loft_docs
 * @{
 */
require_once dirname(__FILE__) . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/LoftPHPUnit_Framework_TestCase.php';

class advancedHelpTest extends LoftPHPUnit_Framework_TestCase
{

    public function testImages()
    {
        $subject = <<<EOD
<img src="http://www.petsfoto.com/wp-content/uploads/2010/08/Cat82.jpg" />
<img src="/2010/08/Cat82.jpg" />
<img src="Cat82.jpg" />
<img class="image" alt="A cat image" src="http://www.petsfoto.com/wp-content/uploads/2010/08/Cat82.jpg" />
<img src="/2010/08/Cat82.jpg" alt="A cat image" />
<img alt="A cat image" src="Cat82.jpg" class="animals" />
<img alt="A cat image" src="&path&Cat82.jpg" class="animals" />
<img alt="A cat image" src="&base_url&sites/default/files/images/Cat82.jpg" class="animals" />
EOD;
        $path = $this->writeFile($subject, 'source.html');

        $control = <<<EOD
<img src="http://www.petsfoto.com/wp-content/uploads/2010/08/Cat82.jpg" />
<img src="&path&2010/08/Cat82.jpg" />
<img src="&path&Cat82.jpg" />
<img class="image" alt="A cat image" src="http://www.petsfoto.com/wp-content/uploads/2010/08/Cat82.jpg" />
<img src="&path&2010/08/Cat82.jpg" alt="A cat image" />
<img alt="A cat image" src="&path&Cat82.jpg" class="animals" />
<img alt="A cat image" src="&path&Cat82.jpg" class="animals" />
<img alt="A cat image" src="&base_url&sites/default/files/images/Cat82.jpg" class="animals" />
EOD;

        $return = $this->includeCLI(dirname(__FILE__) . '/../advanced_help.php', $path);

        $this->assertEquals($control, $return);
    }

    public function testHelpLinks()
    {
        $subject = <<<EOD
<a href="&base_url&admin/settings/site-configuration">
<a href="http://drupal.org/admin/settings/site-configuration">
<a href="/help_page.html">
<a href="help_page.html">
<a href="/dir/help_page.html">
<a href="dir/help_page.html">
EOD;
        $path = $this->writeFile($subject, 'source.html');

        $control = <<<EOD
<a href="&base_url&admin/settings/site-configuration">
<a href="http://drupal.org/admin/settings/site-configuration">
<a href="&topic:cool_module/help_page&">
<a href="&topic:cool_module/help_page&">
<a href="&topic:cool_module/dir/help_page&">
<a href="&topic:cool_module/dir/help_page&">
EOD;

        $return = $this->includeCLI(dirname(__FILE__) . '/../advanced_help.php', $path, 'cool_module');

        $this->assertEquals($control, $return);
    }

    public function testRemoveH1Tag()
    {
        $subject = <<<EOD
<h1>My page title</h1>
<h2>My page subtitle</h2>
<h1 class="page-title">My page title</h1>
<h2>My page subtitle</h2>
EOD;
        $path = $this->writeFile($subject, 'source.html');

        $control = <<<EOD
<h2>My page subtitle</h2>
<h2>My page subtitle</h2>
EOD;

        $return = $this->includeCLI(dirname(__FILE__) . '/../advanced_help.php', $path, 'cool_module');

        $this->assertEquals($control, $return);
    }
}

/** @} */ //end of group: loft_docs
