<?php

namespace AKlump\LoftDocs\DynamicContent;

use AKlump\LoftDocs\Compiler;
use AKlump\LoftLib\Bash\Bash;
use AKlump\LoftLib\Bash\Color;
use AKlump\LoftLib\Storage\FilePath;

/**
 * Handle compilation of apib docs.
 */
class ApiBlueprint {

  /**
   * Path the header file, e.g. _apib.twig.md.
   *
   * @var \AKlump\LoftLib\Storage\FilePath
   */
  protected $header;

  /**
   * Path the directory of resource files, e.g. apib/.
   *
   * @var \AKlump\LoftLib\Storage\FilePath
   */
  protected $resources;

  /**
   * The current compiler.
   *
   * @var \AKlump\LoftDocs\Compiler
   */
  protected $compiler;

  /**
   * Absolute system path to the Aglio executable.
   *
   * @var string
   */
  protected $aglio;

  /**
   * The options to send to Aglio.
   *
   * @var array
   *
   * @link https://github.com/danielgtaylor/aglio
   */
  protected $aglioOptions;

  /**
   * ApiBlueprint constructor.
   *
   * @param \AKlump\LoftDocs\Compiler $compiler
   *   The compiler instance.
   * @param \AKlump\LoftLib\Storage\FilePath $header
   *   The header file.
   * @param \AKlump\LoftLib\Storage\FilePath $resources
   *   Directory of resource partials.
   */
  public function __construct(
    Compiler $compiler,
    FilePath $header,
    FilePath $resources
  ) {
    $this->compiler = $compiler;
    $this->header = $header;
    $this->resources = $resources;
  }

  /**
   * Set the system path to Aglio.
   *
   * @param string $path
   *   The system path to the executable.
   * @param array $options
   *   Options to send to Aglio.  E.g., ['--theme-variables flatly',
   *   '--theme-full-width'].
   *
   * @return $this
   *   Self for chaining.
   *
   * @link https://github.com/danielgtaylor/aglio#executable
   */
  public function setAglio($path = '', array $options = []) {
    $this->aglio = $path;
    $this->aglioOptions = $options;

    return $this;
  }

  /**
   * Compile the API Blueprint documentation.
   */
  public function compile() {
    // Create the master page based on our YAML config.
    $master_content = $this->header->load()->get();
    $master_content = trim($master_content) . PHP_EOL . PHP_EOL;
    $files = $this->resources->children('/\.apib$/');

    $api_resources = [];
    $files->each(function ($item) use (&$master_content, &$api_resources) {
      preg_match('/#+ Group (.+)/', $item->load()->get(), $matches);
      $api_resources[] = [
        'group' => isset($matches[1]) ? trim($matches[1]) : '',
        'markup' => '<!-- include(_' . $item->getBasename() . ') -->',
      ];
      $this->compiler->addInclude('_' . $item->getBasename(), $item->load()
        ->get());
    });

    // Sort the resource partials by group ASC.
    uasort($api_resources, function ($a, $b) {
      return strcasecmp($a['group'], $b['group']);
    });

    // Replace the token with resource markup.
    $resources = array_reduce($api_resources, function ($carry, $item) {
        return $carry . $item['markup'] . PHP_EOL;
      }) . PHP_EOL;
    $master_content = str_replace('{{ apib.resources }}', $resources, $master_content);

    // This is the file Aglio will use to generate apib.md.
    $master = $this->compiler->addInclude('_master.apib', $master_content);

    // This file is the final HTML output from Aglio.
    $output = $this->compiler->addSourceFile('apib.html', '')->destroy();

    try {
      Bash::exec([
        $this->aglio,
        implode(' ', $this->aglioOptions),
        '-i ' . $master->getPath(),
        '-o ' . $output->getPath(),
      ]);
    }
    catch (\Exception $exception) {
      echo Color::wrap('red', $exception->getMessage()) . PHP_EOL;
    }

  }

}
