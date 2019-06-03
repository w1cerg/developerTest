<?php

namespace Pwd;

use \Bitrix\Main\Data\Cache;

/**
 * Пример использования:
 *
 * $result = (new \Pwd\CIBlockElement)->getElements(
 *     ['IBLOCK_ID' => IBLOCK_ID__CATALOG, '?CODE' => 'super-product',
 *     ['IBLOCK_ID', 'ID', 'NAME', 'CODE']
 * );
 */
class CIBlockElement
{
    const CACHE_FOLDER = '/pwd/ciblockelement/';
    const CACHE_TIME = 86400;

    protected $filter;
    protected $select;
    protected $order;
    /**
     * @var \Bitrix\Main\Data\Cache
     */
    protected $bxCache;
    /**
     * @var \CCacheManager
     */
    protected $cacheManager;

    protected $defaultParams = [
        'select' => ['ID', 'IBLOCK_ID'],
        'order' => ['SORT' => 'ASC']
    ];

    public function __construct()
    {
        $this->bxCache = Cache::createInstance();
        $this->cacheManager  = $GLOBALS['CACHE_MANAGER'];
    }

    /**
     * Возвращает массив элементов
     *
     * @param array $filter
     * @param array $select
     * @param array $order
     * @return array
     */
    public function getElements($filter = [], $select = [], $order = [])
    {
        $this->filter = $filter;
        $this->select = $select;
        $this->order = $order;

        $getListParams = $this->getParams();

        $cacheId = $this->getCacheId($getListParams);
        $elements = $this->initCache($cacheId);
        if ($elements !== null ) {
            return $elements;
        }

        $elements = [];

        $rsElement = \CIBlockElement::GetList(
            $getListParams['order'],
            $getListParams['filter'],
            $getListParams['group'],
            $getListParams['navStart'],
            $getListParams['select']
        );
        $this->cacheManager->StartTagCache(self::CACHE_FOLDER);
        while ($element = $rsElement->Fetch()) {
            $elements[$element['ID']] = $element;
        }
        $this->cacheManager->EndTagCache();

        $this->setCache($elements);
        return $elements;
    }

    protected function getParams()
    {
        $params = array(
            'order'    => $this->order,
            'filter'   => $this->filter,
            'group'    => false,
            'navStart' => false,
            'select'   => $this->select
        );

        if (empty($params['order']))
        {
            $params['order'] = $this->defaultParams['order'];
        }

        if (empty($params['select']))
        {
            $params['select'] = $this->defaultParams['select'];
        }

        return $params;
    }

    protected function getCacheId($params)
    {
        return md5(serialize($params));
    }

    protected function initCache($cacheId)
    {
        if ($this->bxCache->initCache(self::CACHE_TIME, $cacheId, self::CACHE_FOLDER)) {
            $data = $this->bxCache->GetVars();
            if (isset($data) && is_array($data)) {
                return $data;
            }
        }

        return null;
    }

    protected function setCache($data)
    {
        if ($this->bxCache->startDataCache()) {
            $this->bxCache->endDataCache($data);
        }
    }
}