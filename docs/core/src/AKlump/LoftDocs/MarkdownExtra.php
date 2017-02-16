<?php
/**
 * @file
 * Defines the MarkdownExtra class.
 *
 * @ingroup markdown_extra
 * @{
 */
namespace AKlump\LoftDocs;

/**
 * Represents a MarkdownExtra object class.
 *
 * @brief Extends Markdown Extra with more token replacements.
 */
class MarkdownExtra extends \Michelf\MarkdownExtra
{

    public static function defaultTransform($text)
    {
        //
        //
        // Replace video tags
        // ![:video images/poster.png](videos/wysiwyg-config.m4v)
        // ![:video](videos/wysiwyg-config.m4v)
        //
        $text = preg_replace("/!\[\:video\s*(.+)?\]\((.+?)\)/", "<video poster=\"$1\" controls src=\"$2\"></video>", $text);

        return parent::defaultTransform($text);
    }
}


