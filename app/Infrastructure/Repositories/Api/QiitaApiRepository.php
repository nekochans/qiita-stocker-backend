<?php
/**
 * QiitaApiRepository
 */

namespace App\Infrastructure\Repositories\Api;

use App\Models\Domain\Stock\StockEntities;
use App\Models\Domain\Stock\StockEntityBuilder;

/**
 * Class QiitaApiRepository
 * @package App\Infrastructure\Repositories\Qiita
 */
class QiitaApiRepository implements \App\Models\Domain\QiitaApiRepository
{
    /**
     * ストック一覧を取得する
     *
     * @param string $qiitaUserName
     * @return StockEntities
     */
    public function fetchStock(string $qiitaUserName): StockEntities
    {
        $articleCreatedAt = new \DateTime();
        
        $stockEntityBuilder = new StockEntityBuilder();
        $stockEntityBuilder->setId(1);
        $stockEntityBuilder->setArticleId('c0a2609ae61a72dcc60e');
        $stockEntityBuilder->setTitle('テストタイトル');
        $stockEntityBuilder->setUserId('test-user');
        $stockEntityBuilder->setProfileImageUrl('https://avatars3.githubusercontent.com/u/3268265?v=4');
        $stockEntityBuilder->setArticleCreatedAt($articleCreatedAt);
        $stockEntityBuilder->setTags(['CORS', 'laravel5.6', 'laravel', 'php']);
        $stockEntity = $stockEntityBuilder->build();

        return new StockEntities($stockEntity);
    }
}
