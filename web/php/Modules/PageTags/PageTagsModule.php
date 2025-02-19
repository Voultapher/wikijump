<?php

namespace Wikidot\Modules\PageTags;

use Illuminate\Support\Facades\DB;
use Ozone\Framework\Database\Criteria;
use Wikidot\DB\PagePeer;
use Wikidot\DB\AllowedTags;
use Ozone\Framework\SmartyModule;
use Wikidot\Utils\ProcessException;
use Wikidot\Utils\WDPermissionManager;

class PageTagsModule extends SmartyModule
{

    public function build($runData)
    {
        $user = $runData->getUser();
        $pl = $runData->getParameterList();
        $pageId = $pl->getParameterValue("pageId");
        $site = $runData->getTemp("site");

        if (!is_numeric($pageId)) {
            throw new ProcessException(_("The page cannot be found or does not exist."), "no_page");
        }

        $page = PagePeer::instance()->selectByPrimaryKey($pageId);

        if ($page == null || $page->getSiteId() != $site->getSiteId()) {
            throw new ProcessException(_("Error getting page information."), "no_page");
        }

        $category = $page->getCategory();

        WDPermissionManager::instance()->hasPagePermission('edit', $user, $category, $page);

        // Fetch the tags and convert them to a string.
        $tags = PagePeer::getTags($pageId);
        $tags = implode(" ", $tags);

        $runData->contextAdd("tags", $tags);
    }
}
