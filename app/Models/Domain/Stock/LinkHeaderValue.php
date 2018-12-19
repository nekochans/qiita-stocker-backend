<?php
/**
 * LinkHeaderValue
 */

namespace App\Models\Domain\Stock;

/**
 * Class LinkHeaderValue
 * @package App\Models\Domain\Stock
 */
class LinkHeaderValue
{
    /**
     * URI base
     *
     * @var string
     */
    private $uriBase;

    /**
     * Page
     *
     * @var int
     */
    private $page;

    /**
     * Per Page
     *
     * @var int
     */
    private $perPage;

    /**
     * Relation
     *
     * @var string
     */
    private $relation;

    /**
     * LinkHeaderValue constructor.
     * @param string $uriBase
     * @param int $page
     * @param int $perPage
     * @param string $relation
     */
    public function __construct(string $uriBase, int $page, int $perPage, string $relation)
    {
        $this->uriBase = $uriBase;
        $this->page = $page;
        $this->perPage = $perPage;
        $this->relation = $relation;
    }

    /**
     * @return string
     */
    public function getUriBase(): string
    {
        return $this->uriBase;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * @return string
     */
    public function getRelation(): string
    {
        return $this->relation;
    }

    /**
     * Linkを作成する
     *
     * @return string
     */
    public function buildLink(): string
    {
        $uri = sprintf(
            '%s?page=%d&per_page=%d',
            $this->getUriBase(),
            $this->getPage(),
            $this->getPerPage()
        );

        return sprintf('<%s>; rel="%s"', $uri, $this->getRelation());
    }
}
