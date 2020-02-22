<?php

namespace AKlump\LoftDocs;

use AKlump\Data\Data;
use AKlump\LoftLib\Storage\FilePath;

/**
 * Provide compiling functionality.
 */
class Compiler {

  /**
   * The path to the original source directory.
   *
   * @var \AKlump\LoftLib\Storage\FilePath
   */
  protected $pathToStaticSourceFiles;

  /**
   * The path to the cache/source directory.
   *
   * @var \AKlump\LoftLib\Storage\FilePath
   */
  protected $pathToDynamicSourceFiles;

  /**
   * The path to the outline file.
   *
   * @var \AKlump\LoftLib\Storage\FilePath
   */
  protected $pathToOutline;

  public function __construct(FilePath $static_source, FilePath $dynamic_source, Filepath $outline) {
    $this->pathToStaticSourceFiles = $static_source;
    $this->pathToDynamicSourceFiles = $dynamic_source;
    FilePath::ensureDir($this->pathToDynamicSourceFiles->getPath());
    $this->pathToOutline = $outline;
    if (!$outline->exists()) {
      throw new \RuntimeException("\$outline does not exist.");
    }
  }

  /**
   * Create a compile-time only source file.
   *
   * Use this from hooks to create dynamic content.  This is cached and
   * destroyed before the next compile.  For include files you should use
   * ::addInclude instead.
   *
   * @param string $basename
   *   The basename of the source file.
   * @param string $contents
   *   The file contents.
   *
   * @return \AKlump\LoftLib\Storage\FilePath
   *   The source file
   *
   * @see addInclude
   */
  public function addSourceFile($basename, $contents) {
    return $this->pathToDynamicSourceFiles->to($basename)
      ->put($contents)
      ->save();
  }

  /**
   * Create a dynamic include file.
   *
   * This file will be accessible during the build process.  Since it's an
   * include file, it will not appear as it's own page and will not be indexed.
   *  For indexed or page content use ::addSourceFile.
   *
   * @param string $basename
   *   The basename of the file, which must begin with underscore, e.g.
   *   "_headline.md".
   * @param string $contents
   *   The file contents.
   *
   * @return \AKlump\LoftLib\Storage\FilePath
   *   The source file
   *
   * @throws \InvalidArgumentException
   *   If $basename does not begin with an underscore.
   */
  public function addInclude($basename, $contents) {
    return $this->validateIncludeBasename($basename)
      ->addSourceFile($basename, $contents);
  }

  /**
   * Check that $basename begins with an underscore.
   *
   * @param string $basename
   *   The basename to load.
   *
   * @return $this
   *   Self for chaining.
   */
  private function validateIncludeBasename($basename) {
    if (substr($basename, 0, 1) !== '_') {
      throw new \InvalidArgumentException("Include files must begin with an underscore; did you mean _\"" . $basename . '"?');
    }

    return $this;
  }

  /**
   * Get an instance of FilePath for an include file.
   *
   * We will look in dynamic files first, then static.
   *
   * @param string $basename
   *   The basename.
   *
   * @return \AKlump\LoftLib\Storage\FilePath
   *   The include filepath instance.
   */
  public function getInclude($basename) {
    $this->validateIncludeBasename($basename);
    $include = $this->pathToDynamicSourceFiles->to($basename);
    if (!$include->exists()) {
      $include = $this->pathToStaticSourceFiles->to($basename);
    }

    return $include;
  }

  /**
   * Return a source file.
   *
   * First tries to get a processed source file, then falls back to the
   * un-processed source files.
   *
   * @param string $basename
   *   The basename.
   *
   * @return \AKlump\LoftLib\Storage\FilePath
   *   The source filepath instance.
   */
  public function getSource($basename) {
    $file = $this->pathToDynamicSourceFiles->to($basename);
    if (!$file->exists()) {
      $file = $this->pathToStaticSourceFiles->to($basename);
    }

    return $file;
  }

  /**
   * Return all source files from a directory.
   *
   * @param string $path_to_dir
   *   A directory to scan for source files.
   *
   * @return array
   *   Each element has keys:
   *   - path
   *   - source
   *   - compiled
   */
  public static function indexSourceFiles($path_to_dir) {
    $index = is_dir($path_to_dir) ? scandir($path_to_dir) : array();

    return empty($index) ? $index : array_values(array_map(function ($basename) use ($path_to_dir) {
      $info = pathinfo($path_to_dir . "/$basename");
      $info += [
        'path' => $info['dirname'] . '/' . $info['basename'],
        'filename_compiled' => self::getCompiledFilenameFromSourcePath($basename),
      ];

      return $info;
    }, array_filter($index, function ($item) {
      return !in_array($item, [
          '.',
          '..',
          '.DS_Store',
          'search--results.md',
        ]) && substr($item, 0, 1) !== '_';
    })));
  }

  /**
   * Get the compiled filename from a source file.
   *
   * @param string $source_path
   *   Expected "file.md" or "file.twig.md".
   *
   * @return string
   *   The compiled filename, without extension, e.g. "file".
   */
  public static function getCompiledFilenameFromSourcePath($source_path) {
    return preg_replace('/\.twig$/', '', pathinfo($source_path, PATHINFO_FILENAME));
  }

  /**
   * Detect if a filename points to a markdown file.
   *
   * @param string $path
   *
   * @return bool
   */
  public static function pathIsSection($path) {
    $ext = pathinfo(strtolower($path), PATHINFO_EXTENSION);
    $valid = get_markdown_extensions();
    //@todo want to be able to show text files?
    // return in_array($ext, array('txt') + get_markdown_extensions());

    return in_array($ext, $valid);
  }

  /**
   * Get a settings value.
   *
   * Settings are reported in the outline file under the key `settings`.
   *
   * @param string $setting_key
   *   The setting name/key, e.g., "tasklist.aggregate".
   * @param null $default
   *   The default value
   *
   * @return mixed
   *   The value of the setting or $default.
   */
  public function getSetting($setting_key, $default = NULL) {
    $g = new Data();
    $json = $this->pathToOutline->load()->getJson(TRUE);
    $json += ['settings' => []];

    return $g->get($json, 'settings.' . $setting_key, $default);
  }

  /**
   * Add variables which are used in the twig rendering.
   *
   * @param array $variables
   *   An associative array of replacement vars.
   *
   * @return $this
   *   Self, for chaining.
   */
  public function addVariables(array $variables) {
    $files = $this->pathToDynamicSourceFiles->to('_variables.json');
    if ($files->exists()) {
      $variables += $files->load()->getJson();
    }
    $files->putJson($variables)->save();

    return $this;
  }

  /**
   * Return the variables for use with the twig rendering.
   *
   * @return array
   *   The twig template data.
   */
  public function getVariables() {
    $global = $this->pathToOutline->load()->getJson(TRUE);
    $variables = $global['variables'] ?? [];
    $files = $this->pathToDynamicSourceFiles->to('_variables.json');
    if ($files->exists()) {
      $variables += $files->load()->getJson(TRUE);
    }

    return $variables;
  }

  /**
   * Return data for all sections.
   *
   * @return array
   *   An array of section data, keyed by id.
   */
  public function getSectionsById() {
    $ids = [];
    $outline = $this->pathToOutline->load()->getJson(TRUE);
    if (empty($outline['sections'])) {
      return $ids;
    }

    return array_combine(array_map(function ($section) {
      return $section['id'];
    }, $outline['sections']), $outline['sections']);
  }

  /**
   * Replace links to other pages on the site with actual filenames.
   *
   * This should run on HTML markup.
   *
   * Internal links--what this looks for--look like this (quotes included).
   *
   *    "@page2:part4"
   *
   * ...where "page2" is a page id and "part4" is the id of an html header.  It
   * will get replaced with something like...
   *
   *    "page-two-filename.html#part4"
   *
   * The counterpart, or target to the above link would look like this, on a
   * page with an id of "page2".  The following:
   *
   *    <h3>:part4
   *
   * ... gets replaced with:
   *
   *    <h3 id="part4">
   *
   * @param string $contents
   *   The HTML file contents.
   *
   * @return string
   *   The HTML file contents with actual links to filenames.
   */
  public function processInternalLinks($contents, $extension = 'html') {
    $ids = $this->getSectionsById();

    foreach (array_keys($ids) as $id) {
      $contents = preg_replace_callback('/"@(' . preg_quote($id) . ')(?:\:([^\s]+))?"/', function ($matches) use ($ids, $extension) {
        $matches += [NULL, NULL, NULL];
        if (($section = $ids[$matches[1]])) {
          return '"' . rtrim($section['file'] . '.' . $extension . '#' . $matches[2], '#') . '"';
        }
        else {
          throw new \RuntimeException("Invalid iternal link: \"$matches[1]\".");
        }
      }, $contents);

      // Replace the section header ids.
      $regex = '/<(h\d)(.*)>\:([^\s]+)\s*/i';
      $replacement = '<$1$2 id="$3">';
      $contents = preg_replace($regex, $replacement, $contents);
    }

    // Check for unhandled links and throw an exception.
    preg_match_all('/"@([^\s]+)(?:\:([^\s]+))?"/', $contents, $matches, PREG_SET_ORDER);
    if (count($matches)) {
      throw new \RuntimeException("Invalid link id(s): " . implode(', ', array_map(function ($found) {
          return $found[1];
        }, $matches)));
    }

    return $contents;
  }

  /**
   * Return all files in a directory.
   *
   * @param $path_to_directory
   *
   * @return \AKlump\LoftLib\Component\Storage\FilePath|\AKlump\LoftLib\Component\Storage\FilePathCollection|null
   */
  public function getFilesInDirectory($path_to_directory, $filename_match_regex = NULL) {
    return FilePath::create($path_to_directory)
      ->children($filename_match_regex);
  }

  /**
   * Create markdown from source code.
   *
   * This should be used when you want to add source code to your
   * documentation.  Write the code as a native file and then use this method
   * to pull that file into the documentation build.  This allows certain
   * meta-comments that allows you to add a page title, markdown, break your
   * code block into sections, etc.  Read on for more info.
   *
   * The following comments take on special meaning when parsed by this method,
   * sprinkle these in your source code file and the generated markdown can be
   * spruced up and made easier to read.  Experiment with their usage to see
   * how they work, but they should be self-explanatory.
   * - '// @loftDocs.title(Lorem Ipsum)' - Set the page title
   * - '// @loftDocs.markdown(## Lorem Subtitle)' - Add markdown
   * - '// @loftDocs.break' - Split the <pre> tag at that point.
   *
   * Here is code that could be the contents of a hook file showing how to
   * generate pages from test classes:
   *
   * @code
   * $example_files = $compiler->getFilesInDirectory(__DIR__ .
   *   '/../../tests/src/', '/Test\.php$/');
   *
   * foreach ($example_files as $example_file) {
   *   $markup = $compiler->createMarkdownFromSourceCodeFile($example_file);
   *   $compiler->addSourceFile($example_file->getFilename() . '.md', $markup);
   * }
   * @endcode
   *
   * @param \AKlump\LoftLib\Storage\FilePath $code_file
   *   The filepath to the source code.
   * @param bool $with_header
   *   True, the frontmatter and page title will be prepended to the source
   *   code.  Set this to false to omit this.
   *
   * @return string
   *   Contents ready to be saved using ::addSourceFile() or ::addInclude().
   */
  public function createMarkdownFromSourceCodeFile(FilePath $code_file, $with_header = TRUE) {
    $code = $code_file->load()->get();

    // Extract the title.
    $title = $code_file->getFilename();
    if (preg_match('#\/\/\s*@loftDocs.title\((.+)\)\s*#', $code, $matches)) {
      $title = trim($matches[1]);
      $code = str_replace($matches[0], '', $code);
    }
    // Extract the id.
    $id = str_replace(' ', '_', strtolower($code_file->getFilename()));
    if (preg_match('#\/\/\s*@loftDocs.id\((.+)\)#', $code, $matches)) {
      $id = $matches[1];
      $code = str_replace($matches[0], '', $code);
    }

    // Fix the php open tag.
    $code = str_replace('<?php', '&lt;?php', $code);

    // Split code at breaks.
    $sections = explode('// @loftDocs.break', $code);
    $sections = array_map(function ($item) {
      return preg_replace('#\n*\/\/\s*@loftDocs.markdown\((.+)\)\n+#', "</pre>\n\\1\n<pre>", $item);
    }, $sections);

    $code = '<pre>' . implode("</pre>\n---\n<pre>", array_map('trim', $sections)) . '</pre>';
    $code = str_replace('<pre></pre>', '', $code);

    // Create the page in the index.
    $lines = [];
    if ($with_header) {
      $lines[] = '---';
      $lines[] = 'id: ' . $id;
      $lines[] = 'title: ' . $title;
      $lines[] = '---';
      $lines[] = '# ' . $title;
      $lines[] = NULL;
    }
    $lines[] = $code;

    return implode(PHP_EOL, $lines);
  }

}
