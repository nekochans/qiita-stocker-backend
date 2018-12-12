<?php
/**
 * StockEntity
 */

namespace App\Models\Domain\Stock;

/**
 * Class StockEntity
 * @package App\Models\Domain\Stock
 */
class StockEntity
{
    /**
     * ストックID
     *
     * @var int
     */
    private $Id;

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
     * StockEntity constructor.
     * @param StockEntityBuilder $builder
     */
    public function __construct(StockEntityBuilder $builder)
    {
        $this->id = $builder->getId();
        $this->articleId = $builder->getArticleId();
        $this->title = $builder->getTitle();
        $this->userId = $builder->getTitle();
        $this->profileImageUrl = $builder->getProfileImageUrl();
        $this->articleCreatedAt = $builder->getArticleCreatedAt();
        $this->tags = $builder->getTags();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->Id;
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
