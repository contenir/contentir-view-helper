<?php

namespace Contenir\View\Helper;

use Laminas\Uri\Exception\InvalidArgumentException;
use Laminas\View\Helper\AbstractHelper;
use Laminas\Uri\UriFactory;

class UrlFormat extends AbstractHelper
{
    use PHPViewTrait;

    protected string $_format = '%scheme%%host%%path%';

    public function __invoke($url = null, $format = null): array|string|null
    {
        if ($format === null) {
            $format = $this->_format;
        }

        $view = $this->getPHPView();

        if (! preg_match('/^[a-z]+:\/\//', (string)$url)) {
            if ($url[0] === '/') {
                $url = $view->ServerUrl() . $url;
            } else {
                $url = 'http://' . $url;
            }
        }

        try {
            $uri          = UriFactory::factory($url);
            $formattedUrl = preg_replace_callback(
                '/%(\w+)%/',
                function ($matches) use ($uri) {
                    switch ($matches[1]) {
                        case 'scheme':
                            $part = $uri->getScheme();
                            if ($part) {
                                $part .= '://';
                            }
                            break;

                        case 'userinfo':
                            $part = $uri->getUserInfo();
                            break;

                        case 'host':
                            $part = $uri->getHost();
                            break;

                        case 'port':
                            $part = $uri->getPort();
                            break;

                        case 'path':
                            $part = $uri->getPath();
                            break;

                        case 'query':
                            $part = $uri->getQuery();
                            if ($part) {
                                $part = '?' . $part;
                            }
                            break;

                        case 'fragment':
                            $part = $uri->getFragment();
                            if ($part) {
                                $part = '#' . $part;
                            }
                            break;

                        default:
                            $part = '';
                            break;
                    }

                    return $part;
                },
                $format
            );
        } catch (InvalidArgumentException) {
            $formattedUrl = '';
        }

        return $formattedUrl;
    }
}
