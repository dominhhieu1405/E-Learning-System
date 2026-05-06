<?php

namespace Services;

use Phpfastcache\CacheManager;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Phpfastcache\Drivers\Files\Config as FilesConfig;
use Phpfastcache\Drivers\Redis\Config as RedisConfig;
use Phpfastcache\Exceptions\PhpfastcacheDriverCheckException;
use Phpfastcache\Exceptions\PhpfastcacheDriverException;
use Phpfastcache\Exceptions\PhpfastcacheDriverNotFoundException;
use Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException;
use Phpfastcache\Exceptions\PhpfastcacheInvalidConfigurationException;
use Phpfastcache\Exceptions\PhpfastcacheLogicException;
use ReflectionException;

class Cache
{
    public static mixed $getInstance;
    public static string $type = 'files';

    /**
     * @throws PhpfastcacheDriverNotFoundException
     * @throws PhpfastcacheInvalidConfigurationException
     * @throws PhpfastcacheDriverCheckException
     * @throws PhpfastcacheLogicException
     * @throws ReflectionException
     * @throws PhpfastcacheDriverException
     * @throws PhpfastcacheInvalidArgumentException
     */
    public static function load($path = ROOT_PATH . '/resources/cache', $securityKey = 'study'): ExtendedCacheItemPoolInterface
    {
        if (self::$getInstance === null) {

            switch (self::$type) {
                case 'redis':
                    $driver = 'redis';
                    $config = new RedisConfig([
                        'host' => '127.0.0.1', //Default value
                        'port' => 6379, //Default value
                        'password' => '', //Default value
                        'database' => '', //Default value
                        'path' => $path
                    ]);
                    break;
                case 'files':
                default:
                    $driver = "files";
                    $config = new FilesConfig(['path' => $path, 'securityKey' => $securityKey]);
                    $config->setDefaultChmod(0777);

                    break;
            }

            $config->setDefaultFileNameHashFunction('md5');
            $config->setCompressData(true);

            self::$getInstance = CacheManager::getInstance($driver, $config);
        }

        return self::$getInstance;
    }


}