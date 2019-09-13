<?php
namespace Ecentral\EcStyla\Controller;

use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 entwicklung@ecentral.de <>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class ContentHubController
 * @package Ecentral\EcStyla\Controller
 */
class ContentHubController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    const API_URI_QUERYSTRING = '%s?url=/%s';

    /**
     * @var \TYPO3\CMS\Core\Cache\CacheManager
     */
    protected $cache;

    /**
     * @var array
     */
    protected $disabledMetaTagsArray = [];

    /**
     * Default lifetime of cached data
     * @var int
     */
    protected $cachePeriod = 3600;

    /** @var  \TYPO3\CMS\Extbase\Object\ObjectManager */
    protected $objectManager;

    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
    }

    /**
     * action show
     *
     * Add seo relevant elements to html body, either by fetching
     * the data from remote or using the cached data.
     *
     * @return void
     */
    public function showAction()
    {
        if (!array_key_exists('api_url',$this->settings)
            || !array_key_exists('contenthub_segment',$this->settings)) {
            $pluginConfigFromSetup = '';
            TypoScriptParser::includeFile('typo3conf/ext/ec_styla/Configuration/TypoScript/setup.ts' ,1 ,false,$pluginConfigFromSetup);
            /** @var TypoScriptParser $typoScriptParser */
            $typoScriptParser = $this->objectManager->get(TypoScriptParser::class);
            $typoScriptParser->parse($pluginConfigFromSetup);
            $this->settings['api_url'] = $typoScriptParser->setup['plugin.']['tx_ecstyla_contenthub.']['settings.']['api_url'];
            $this->settings['contenthub_segment'] = $typoScriptParser->setup['plugin.']['tx_ecstyla_contenthub.']['settings.']['contenthub_segment'];
        }

        $this->cache = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class)->getCache('ec_styla');
        $cacheIdentifier = $this->getCacheIdentifier();
        $cachedContent = $this->cache->get($cacheIdentifier);

        if (false == $cachedContent) {
            $path = strtok(str_replace(
                $this->getControllerContext()->getRequest()->getBaseUri(),
                '',
                $this->getControllerContext()->getRequest()->getRequestUri()
            ), '?');

            $url = sprintf(
                $this->settings['api_url'] . self::API_URI_QUERYSTRING,
                $this->settings['contenthub']['id'],
                $path
            );

            $request = GeneralUtility::makeInstance(\Ecentral\EcStyla\Utility\StylaRequest::class);
            $content = $request->get($url);
            if (null !== $content) {
                $this->cachePeriod = $request->getCachePeriod();
                $this->cacheContent(
                    $content,
                    array (
                        'styla',
                        $this->settings['contenthub']['id']
                    )
                );
            }
       } else {
            $content = $cachedContent;
        }

        $signalSlotDispatcher = $this->objectManager->get(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
        list($content) = $signalSlotDispatcher->dispatch(__CLASS__, 'beforeProcessingSeoContent', array($content));

        if (!$content || $content->error) {
            $this->view->assign('seoHtml', '');
            return;
        }

        $this->disabledMetaTagsArray = array_map('trim', explode(',', $this->settings['disabled_meta_tags']));

        foreach ($content->tags as $item) {
            if ('' != ($headerElement = $this->getHtmlForTagItem($item))) {
                // If Cache-Control is set to no-cache upon request, the page renderer
                // may not add additional meta information for this request. Hence the
                // additional header elements are directly added to the header elements list.
                $GLOBALS['TSFE']->additionalHeaderData[] = $headerElement;
            }
        }

        $this->view->assign('seoHtml', $content->html->body);
    }

    /**
     * Cache serializable item
     *
     * @param $item
     * @param array $tags
     * @param int $cachePeriod
     */
    protected function cacheContent($item, $tags = array('styla'), $cachePeriod = 3600) {
        $this->cache->set(
            $this->getCacheIdentifier(),
            $item,
            $tags,
            $cachePeriod
        );
    }

    /**
     * Get cache identifier
     *
     * @return string
     */
    protected function getCacheIdentifier() {
        $path = strtok($this->getControllerContext()->getRequest()->getRequestUri(), '?');

        return 'styla-' . $this->settings['contenthub']['id'] . '-'. md5($path);
    }

    /**
     * Return html element for item
     *
     * TODO: Implement generic approach
     *
     * @param $item
     * @return string
     */
    protected function getHtmlForTagItem($item) {
        switch ($item->tag) {
            case 'meta':
                if(null != $item->attributes->name && !in_array($item->attributes->name, $this->disabledMetaTagsArray)) {
                    return '<meta name="' . $item->attributes->name  . '" content="' . $item->attributes->content . '" />';
                }
                if (!in_array($item->attributes->property, $this->disabledMetaTagsArray)) {
                    return '<meta property="' .  $item->attributes->property  . '" content="' . $item->attributes->content . '" />';
                }
                return '';
                break;
            case 'link':
                return '<link rel="' . $item->attributes->rel . '" href="' . $item->attributes->href . '" />';
                break;
            case 'title':
                return '<title>' . $item->content . '</title>';
                break;
            default:
                return '';
        }
    }

    protected function removeTYPO3MetaTags() {
        $metaTagManager = $this->objectManager->get(MetaManagrw::class)->getManagerForProperty('og:title');

    }
}
