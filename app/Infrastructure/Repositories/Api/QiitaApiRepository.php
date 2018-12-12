<?php
/**
 * QiitaApiRepository
 */

namespace App\Infrastructure\Repositories\Api;

use App\Models\Domain\Stock\StockValues;
use App\Models\Domain\Stock\StockValueBuilder;

/**
 * Class QiitaApiRepository
 * @package App\Infrastructure\Repositories\Qiita
 */
class QiitaApiRepository extends Repository implements \App\Models\Domain\QiitaApiRepository
{
    /**
     * ストック一覧を取得する
     *
     * @param string $qiitaUserName
     * @return StockValues
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function fetchStock(string $qiitaUserName): StockValues
    {
        // TODO パラメータが固定となっているが、全てのストックを取得できるように修正する
        $uri = 'https://qiita.com/api/v2/users/'.$qiitaUserName .'/stocks?page=1&per_page=20';

        $response = $this->getClient()->request('GET', $uri);
        $responseArray = json_decode($response->getBody());

        $stockValues = [];
        foreach ($responseArray as $stock) {
            $articleCreatedAt = new \DateTime($stock->created_at);
            $tagNames = $this->buildTagNamesArray($stock->tags);

            $stockValueBuilder = new StockValueBuilder();
            $stockValueBuilder->setArticleId($stock->id);
            $stockValueBuilder->setTitle($stock->title);
            $stockValueBuilder->setUserId($stock->user->id);
            $stockValueBuilder->setProfileImageUrl($stock->user->profile_image_url);
            $stockValueBuilder->setArticleCreatedAt($articleCreatedAt);
            $stockValueBuilder->setTags($tagNames);
            $stockValue = $stockValueBuilder->build();

            array_push($stockValues, $stockValue);
        }

        return new StockValues(...$stockValues);
    }

    /**
     * タグ名の配列を取得する
     *
     * @param array $tags
     * @return array
     */
    private function buildTagNamesArray(array $tags): array
    {
        $tagNames = [];
        foreach ($tags as $tag) {
            $tagName = $tag->name;
            array_push($tagNames, $tagName);
        }
        return $tagNames;
    }
}
