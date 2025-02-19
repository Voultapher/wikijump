<?php
declare(strict_types=1);

namespace Wikijump\Services\Wikitext\FFI;

use FFI;
use Wikijump\Services\Wikitext;

/**
 * Class PageInfo, representing an input 'struct ftml_page_info' object.
 * See Wikijump\Services\Wikitext\PageInfo for a version of this class
 * intended for general, non-FFI consumption.
 * @package Wikijump\Services\Wikitext\FFI
 */
class PageInfo
{
    private FFI\CData $c_data;

    public function __construct(Wikitext\PageInfo $page_info)
    {
        $tag_array = FtmlFfi::listToPointer(
            FtmlFfi::$C_STRING,
            $page_info->tags,
            fn(string $tag) => FtmlFfi::string($tag),
        );

        $this->c_data = FtmlFfi::make(FtmlFfi::$FTML_PAGE_INFO);
        $this->c_data->page = FtmlFfi::string($page_info->page);
        $this->c_data->category = FtmlFfi::string($page_info->category);
        $this->c_data->site = FtmlFfi::string($page_info->site);
        $this->c_data->title = FtmlFfi::string($page_info->title);
        $this->c_data->alt_title = FtmlFfi::string($page_info->alt_title);
        $this->c_data->tags_list = $tag_array->pointer;
        $this->c_data->tags_len = $tag_array->length;
        $this->c_data->language = FtmlFfi::string($page_info->language);
    }

    public function pointer(): FFI\CData
    {
        return FFI::addr($this->c_data);
    }

    function __destruct()
    {
        FtmlFfi::freePointer(
            $this->c_data->tags_list,
            $this->c_data->tags_len,
            fn(FFI\CData $c_data) => FFI::free($c_data),
        );

        FFI::free($this->c_data->page);
        FFI::free($this->c_data->category);
        FFI::free($this->c_data->site);
        FFI::free($this->c_data->title);
        FFI::free($this->c_data->alt_title);
        FFI::free($this->c_data->language);
        FFI::free($this->c_data);
    }
}
