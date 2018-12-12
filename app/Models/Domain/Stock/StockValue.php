<?php
/**
 * StockValue
 */

namespace App\Models\Domain\Stock;

/**
 * Class StockValue
 * @package App\Models\Domain\Stock
 */
class StockValue
{
    /**
     * 記事ID
     *
     * @var string
     */
    private $articleId;

    /**
     * タイトル
     *
     * @var string
     */
    private $title;

    /**
     * ユーザID
     *
     * @var string
     */
    private $userId;

    /**
     * プロフィール画像URL
     *
     * @var string
     */
    private $profileImageUrl;

    /**
     * 記事作成日時
     *
     * @var \DateTime
     */
    private $articleCreatedAt;

    /**
     * タグ
     *
     * @var string[]
     */
    private $tags;

    /**
     * StockValue constructor.
     * @param StockValueBuilder $builder
     */
    public function __construct(StockValueBuilder $builder)
    {
        $this->articleId = $builder->getArticleId();
        $this->title = $builder->getTitle();
        $this->userId = $builder->getUserId();
        $this->profileImageUrl = $builder->getProfileImageUrl();
        $this->articleCreatedAt = $builder->getArticleCreatedAt();
        $this->tags = $builder->getTags();
    }

    /**
     * @return string
     */
    public function getArticleId(): string
    {
        return $this->articleId;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getProfileImageUrl(): string
    {
        return $this->profileImageUrl;
    }

    /**
     * @return \DateTime
     */
    public function getArticleCreatedAt(): \DateTime
    {
        return $this->articleCreatedAt;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }
}
