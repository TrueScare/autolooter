<?php

namespace App\Struct;

class PaginationInfo
{
    private int $page;
    private int $pageSize;
    private string $searchTerm;

    /**
     * @param int $page
     * @param int $pageSize
     * @param String|null $searchTerm
     */
    public function __construct(int $page,int $pageSize, ?String $searchTerm)
    {
        $this->page = $page;
        $this->pageSize = $pageSize;
        $this->searchTerm = $searchTerm?? "";
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param mixed $page
     */
    public function setPage(mixed $page): void
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * @param mixed $pageSize
     */
    public function setPageSize(mixed $pageSize): void
    {
        $this->pageSize = $pageSize;
    }

    public function getSearchTerm(): string
    {
        return $this->searchTerm;
    }

    public function setSearchTerm(string $searchTerm): void
    {
        $this->searchTerm = $searchTerm;
    }
}