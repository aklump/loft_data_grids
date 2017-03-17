[markdown]:http://daringfireball.net/projects/markdown/
[codekit]:http://incident57.com/codekit/

#What Is Loft Docs?
**Loft Docs is the last project documentation tool you'll ever need.**  Loft Docs provides one central place to manage your documentation allowing you to compose in Markdown and have the benefits of simultaneous output to the following formats:

1. An indexed, multi-page, stand-alone, searchable (via js) website
2. HTML
3. Plaintext
4. MediaWiki
5. Advanced Help for Drupal

Gone are the days of having to update all your different documentation locations!

_For installation instructions [scroll down](#install)._

## Features
1. Tasklist todo item aggregation and sorting.
2. Output to many popular formats.
3. Compilation hooks for before and after.
4. Custom [website theming](#theming).
5. Searchable pages (in the website output)

## As a Reader
1. To read documentation you probably just want to load `public_html/index.html` in a browser and proceed from there.
2. Plaintext documentation may also be available in `text/`.
3. MediaWiki documentation if supported will be found in `mediawiki/`.

## As an Author
1. You will concern yourself with the `/source` directory, creating your source markdown files here.  This is the source of all documentation.

2. Only files in the `source` directory should  be edited.  All other files get created during compiling.

3. Images can be added to `source/images`.

4. Use relative links when linking to other pages inside `source`.

5. Use absolute links when linking to anything outside of `source`.


## As an Admin/Content Manager
1. You will need to read about [compiling](#compiling) below; this is the step needed to generate derivative documentation from `/source`.

### Linking to Other Help Pages
You should do the following to link internally to `source/page2.html`

    <a href="page2.html">Link to Next Page</a>

## As a Developer
If you are implementing any hooks and you need component or include files, which compile to markdown files in `/source`:

1. Put these component files in `/parts` not in `/source`.
1. Make sure the generated files begin with the underscore, e.g., `_my_compiled_file.md`.  That will indicate these files are compiled and can be deleted using `core/clean`.

## Core update
Loft Docs provides a core update feature as seen below.  From the root directory type:

    ./core/update
    
## Rationale
The rationalle behind this project is that it is easy to write markdown files, and it is easy to share a static html based documentation file, and it is easy to use Drupal Advanced Help module, and it is easy to version your documentation in git; but to do all this together at once… was NOT EASY.

But now with _Loft Docs_... it's easy.

##Contact
* **In the Loft Studios**
* Aaron Klump - Developer
* PO Box 29294 Bellingham, WA 98228-1294
* _aim_: theloft101
* _skype_: intheloftstudios
* _d.o_: aklump
* <http://www.InTheLoftStudios.com>
