<?php
/**
 * StockValueBuilder
 */

namespace App\Models\Domain\Stock;

/**
 * Class StockValueBuilder
 * @package App\Models\Domain\Stock
 */
class StockValueBuilder
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
     * @return string
     */
    public function getArticleId(): string
    {
        return $this->articleId;
    }

    /**
     * @param string $articleId
     */
    public function setArticleId(string $articleId): void
    {
        $this->articleId = $articleId;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getProfileImageUrl(): string
    {
        return $this->profileImageUrl;
    }

    /**
     * @param string $profileImageUrl
     */
    public function setProfileImageUrl(string $profileImageUrl): void
    {
        $this->profileImageUrl = $profileImageUrl;
    }

    /**
     * @return \DateTime
     */
    public function getArticleCreatedAt(): \DateTime
    {
        return $this->articleCreatedAt;
    }

    /**
     * @param \DateTime $articleCreatedAt
     */
    public function setArticleCreatedAt(\DateTime $articleCreatedAt): void
    {
        $this->articleCreatedAt = $articleCreatedAt;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param string[] $tags
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    /**
     * @return StockValue
     */
    public function build(): StockValue
    {
        return new StockValue($this);
    }
}
