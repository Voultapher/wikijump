<?php
declare(strict_types=1);

namespace Wikijump\Services\Wikitext\FFI;

use Exception;
use FFI;
use Ozone\Framework\Database\Criteria;
use Wikidot\DB\SitePeer;
use Wikijump\Services\Wikitext;
use Wikijump\Services\Wikitext\HtmlOutput;
use Wikijump\Services\Wikitext\TextOutput;

/**
 * Class FtmlFfi, for interacting directly with the FTML FFI.
 * You probably want to use WikitextInterface instead.
 *
 * @package Wikijump\Services\Wikitext\FFI
 */
final class FtmlFfi
{
    const HEADER = '/usr/local/include/ftml.h';
    const LIBRARY = '/usr/local/lib/libftml.so';

    // Singleton management
    private static FFI $ffi;

    // Constant values
    public static int $META_NAME;
    public static int $META_HTTP_EQUIV;
    public static int $META_PROPERTY;

    public static int $WIKITEXT_MODE_PAGE;
    public static int $WIKITEXT_MODE_DRAFT;
    public static int $WIKITEXT_MODE_FORUM_POST;
    public static int $WIKITEXT_MODE_DIRECT_MESSAGE;
    public static int $WIKITEXT_MODE_LIST;

    public static FFI\CType $C_CHAR;
    public static FFI\CType $C_STRING;
    public static FFI\CType $FTML_PAGE_INFO;
    public static FFI\CType $FTML_HTML_OUTPUT;
    public static FFI\CType $FTML_TEXT_OUTPUT;
    public static FFI\CType $FTML_WIKITEXT_SETTINGS;

    private function __construct()
    {
    }

    /**
     * Initializes this class. Do not use.
     * This should only be called once, which is done in FtmlFfi.php
     */
    public static function _init()
    {
        // Load FFI environment.
        //
        // I tried using FFI::load() but had issues with symbol resolution.
        // Ideally this would be done in the preloader, so we can set ffi.enabled=preload,
        // however I was not able to get this to work.
        // See https://scuttle.atlassian.net/browse/WJ-504
        $header = file_get_contents(self::HEADER);
        self::$ffi = FFI::cdef($header, self::LIBRARY);

        // Create constants
        self::$META_NAME = self::$ffi->META_NAME;
        self::$META_HTTP_EQUIV = self::$ffi->META_HTTP_EQUIV;
        self::$META_PROPERTY = self::$ffi->META_PROPERTY;

        self::$WIKITEXT_MODE_PAGE = self::$ffi->WIKITEXT_MODE_PAGE;
        self::$WIKITEXT_MODE_DRAFT = self::$ffi->WIKITEXT_MODE_DRAFT;
        self::$WIKITEXT_MODE_FORUM_POST = self::$ffi->WIKITEXT_MODE_FORUM_POST;
        self::$WIKITEXT_MODE_DIRECT_MESSAGE = self::$ffi->WIKITEXT_MODE_DIRECT_MESSAGE;
        self::$WIKITEXT_MODE_LIST = self::$ffi->WIKITEXT_MODE_LIST;

        self::$C_CHAR = self::type('char');
        self::$C_STRING = self::type('char *');
        self::$FTML_PAGE_INFO = self::type('struct ftml_page_info');
        self::$FTML_HTML_OUTPUT = self::type('struct ftml_html_output');
        self::$FTML_TEXT_OUTPUT = self::type('struct ftml_text_output');
        self::$FTML_WIKITEXT_SETTINGS = self::type('struct ftml_wikitext_settings');
    }

    // ftml export methods
    public static function renderHtml(
        string $wikitext,
        Wikitext\PageInfo $page_info,
        Wikitext\WikitextSettings $settings
    ): HtmlOutput {
        $site_id = self::getSiteId($page_info->site);
        if ($site_id === null) {
            // No site for current context! Return an error.
            throw new Exception('Current site not found: ' . $page_info->site);
        }

        // Convert objects
        $c_page_info = new PageInfo($page_info);
        $c_settings = new WikitextSettings($settings);
        $output = self::make(self::$FTML_HTML_OUTPUT);

        // Render call
        self::$ffi->ftml_render_html(
            FFI::addr($output),
            $wikitext,
            $c_page_info->pointer(),
            $c_settings->pointer(),
        );

        // Convert result back to PHP
        return OutputConversion::makeHtmlOutput($site_id, $output);
    }

    public static function renderText(
        string $wikitext,
        Wikitext\PageInfo $page_info,
        Wikitext\WikitextSetings $settings
    ): TextOutput {
        // Convert objects
        $c_page_info = new PageInfo($page_info);
        $c_settings = new WikitextSettings($settings);
        $output = self::make(self::$FTML_TEXT_OUTPUT);

        // Render call
        self::$ffi->ftml_render_text(
            FFI::addr($output),
            $wikitext,
            $c_page_info->pointer(),
            $c_settings->pointer(),
        );

        // Convert result back to PHP
        return OutputConversion::makeTextOutput($output);
    }

    public static function freeHtmlOutput(FFI\CData $data)
    {
        self::$ffi->ftml_destroy_html_output(FFI::addr($data));
    }

    public static function freeTextOutput(FFI\CData $data)
    {
        self::$ffi->ftml_destroy_text_output(FFI::addr($data));
    }

    public static function settingsFromMode(int $c_mode): WikitextSettings
    {
        $c_settings = self::make(self::$FTML_WIKITEXT_SETTINGS);
        self::$ffi->ftml_wikitext_settings_from_mode(FFI::addr($c_settings), $c_mode);
        return new WikitextSettings($c_settings);
    }

    public static function version(): string
    {
        return self::$ffi->ftml_version();
    }

    // FFI utilities

    /**
     * Allocate a new instance of the given C object.
     *
     * You must run FFI::free() on the object when you're done
     * with it, or you will cause a memory leak.
     *
     * @param FFI\CType $ctype
     * @return FFI\CData
     */
    public static function make(FFI\CType $ctype): ?FFI\CData
    {
        // Handle zero-width types
        if (FFI::sizeof($ctype) === 0) {
            return null;
        }

        return FFI::new($ctype, false, false);
    }

    /**
     * Gets the PHP FFI C type described by the string.
     * See also the static type constants on the class.
     *
     * @param string $type
     * @return FFI\CType
     */
    public static function type(string $type): FFI\CType
    {
        return self::$ffi->type($type);
    }

    /**
     * Converts an FFI C string into a nullable PHP string.
     * That is, it handles C NULL properly.
     */
    public static function nullableString(?FFI\CData $data): ?string
    {
        if ($data === null || FFI::isNull($data)) {
            return null;
        } else {
            return FFI::string($data);
        }
    }

    /**
     * Gets the PHP FFI C array type, for the given FFI type and the length.
     *
     * For instance, to produce a 'char[24]', call arrayType(FtmlFfi::C_CHAR, [24]).
     * To produce deep arrays, such as 'int[8][8]', then $dimensions is [8, 8].
     *
     * @param FFI\CType $ctype
     * @param array $dimensions
     * @return FFI\CType
     */
    public static function arrayType(FFI\CType $ctype, array $dimensions): FFI\CType
    {
        return FFI::arrayType($ctype, $dimensions);
    }

    /**
     * Clones a PHP string into a newly-allocated C string.
     *
     * Not to be confused with FFI::string, which converts
     * a C string into a PHP string.
     *
     * @param ?string $value The string to be cloned, or null
     * @return FFI\CData The C-string created (char *)
     */
    public static function string(?string $value): ?FFI\CData
    {
        // Check for null
        if ($value === null) {
            return null;
        }

        // Allocate C buffer
        $length = strlen($value); // gets bytes, not chars
        $type = self::arrayType(self::$C_CHAR, [$length + 1]);
        $buffer = self::make($type);

        // Copy string data, add null byte
        FFI::memcpy($buffer, $value, $length);
        $buffer[$length] = 0;
        return $buffer;
    }

    public static function getSiteId(string $site): ?string
    {
        $c = new Criteria();
        $c->add('unix_name', $site);
        $c->add('site.deleted', false);
        $site = SitePeer::instance()->selectOne($c);
        return $site ? $site->getSiteId() : null;
    }

    /**
     * Converts a list in the form of a PHP array into a pointer
     * suitable for passing into C FFIs. Applies a transformation
     * to each PHP item to produce the C item.
     *
     * @param FFI\CType $type The C type of the items in the array
     * @param array $list The PHP list containing the items
     * @param callable $convert_fn Converts a PHP item to its C equivalent
     * @returns array with keys "pointer" and "length"
     */
    public static function listToPointer(
        FFI\CType $type,
        array $list,
        callable $convert_fn
    ): array {
        // Allocate heap array
        $length = count($list);
        $pointer_type = self::arrayType($type, [$length]);
        $pointer = self::make($pointer_type);

        // Copy string elements
        foreach ($list as $index => $item) {
            $pointer[$index] = $convert_fn($item);
        }

        return [
            'pointer' => $pointer,
            'length' => $length,
        ];
    }

    /**
     * Frees an allocated array as created by listToPointer().
     *
     * This calls the passed destructor function on each element.
     *
     * @param ?FFI\CData $pointer The pointer to the allocated array
     * @param int $length The length of this array, in items
     * @param callable $free_fn The function used to free the item
     */
    public static function freePointer(
        ?FFI\CData $pointer,
        int $length,
        callable $free_fn
    ) {
        if ($pointer === null) {
            // Nothing to free, empty array
            return;
        }

        for ($i = 0; $i < $length; $i++) {
            $free_fn($pointer[$i]);
        }

        FFI::free($pointer);
    }

    /**
     * Converts a C FFI pointer into a PHP array, applying a transformation
     * to each C item to produce the owned PHP object.
     *
     * @returns array with the converted objects
     */
    public static function pointerToList(
        ?FFI\CData $pointer,
        int $length,
        callable $convert_fn
    ): array {
        $list = [];

        for ($i = 0; $i < $length; $i++) {
            $list[] = $convert_fn($pointer[$i]);
        }

        return $list;
    }
}

// Initialize FFI
FtmlFfi::_init();
