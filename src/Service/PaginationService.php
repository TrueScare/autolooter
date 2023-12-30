<?php

namespace App\Service;

use App\Struct\PaginationInfo;
use Symfony\Component\HttpFoundation\Request;

class PaginationService
{
    public const PAGE_SIZE10 = 10;
    public const PAGE_SIZE25 = 25;
    public const PAGE_SIZE50 = 50;

    public function getPaginationInfoFromRequest(Request $request): PaginationInfo
    {
        $page = $request->query->get('page');
        $page = $page <= 0 ? $page = 1 : $page;
        $pageSize = $request->query->get('pageSize') ?? self::PAGE_SIZE25;
        $searchTerm = $request->query->get('searchTerm');

        return new PaginationInfo($page, $pageSize, $searchTerm);
    }
}