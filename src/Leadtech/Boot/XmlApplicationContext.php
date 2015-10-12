<?php
namespace Boot;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class XmlApplicationContext
 * @package Boot
 * @author  Daan Biesterbos <daan@leadtech.nl>
 * @license http://www.wtfpl.net/
 *
 * @deprecated
 */

class XmlApplicationContext extends ApplicationContext
{
   // Backward compatibility will be removed in 2.0
}
