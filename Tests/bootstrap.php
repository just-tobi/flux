<?php
// Register composer autoloader
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
	throw new \RuntimeException(
		'Could not find vendor/autoload.php, make sure you ran composer.'
	);
}

/** @var Composer\Autoload\ClassLoader $autoloader */
$autoloader = require __DIR__ . '/../vendor/autoload.php';
$autoloader->addPsr4('FluidTYPO3\\Flux\\Tests\\Fixtures\\', __DIR__ . '/Fixtures/');
$autoloader->addPsr4('FluidTYPO3\\Flux\\Tests\\Unit\\', __DIR__ . '/Unit/');
$autoloader->addPsr4('TYPO3\\CMS\\Core\\', __DIR__ . '/../vendor/typo3/cms/typo3/sysext/core/Classes/');
$autoloader->addPsr4('TYPO3\\CMS\\Extbase\\', __DIR__ . '/../vendor/typo3/cms/typo3/sysext/extbase/Classes/');
$autoloader->addPsr4('TYPO3\\CMS\\Fluid\\', __DIR__ . '/../vendor/typo3/cms/typo3/sysext/fluid/Classes/');
$autoloader->addPsr4('TYPO3\\CMS\\Backend\\', __DIR__ . '/../vendor/typo3/cms/typo3/sysext/backend/Classes/');
$autoloader->addPsr4('TYPO3\\CMS\\Recordlist\\', __DIR__ . '/../vendor/typo3/cms/typo3/sysext/recordlist/Classes/');
$autoloader->addPsr4('TYPO3\\CMS\\Frontend\\', __DIR__ . '/../vendor/typo3/cms/typo3/sysext/frontend/Classes/');
$autoloader->addPsr4('TYPO3\\CMS\\Lang\\', __DIR__ . '/../vendor/typo3/cms/typo3/sysext/lang/Classes/');

\FluidTYPO3\Development\Bootstrap::initialize(array(
	'cache_pages' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
	'cache_hash' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
	'extbase_reflection' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
	'l10n' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
	'fluid_template' => \FluidTYPO3\Development\Bootstrap::CACHE_PHP_NULL,
	'cache_core' => \FluidTYPO3\Development\Bootstrap::CACHE_PHP_NULL,
	'extbase_object' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
	'extbase_datamapfactory_datamap' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
	'extbase_typo3dbbackend_tablecolumns' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
	'extbase_typo3dbbackend_queries' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
	'fluidcontent' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
	'l10n' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
	'flux' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
));

class_alias('FluidTYPO3\\Flux\\Tests\\Fixtures\\Classes\\ContentController', 'FluidTYPO3\\Flux\\Controller\\ContentController');
class_alias('FluidTYPO3\\Flux\\Tests\\Fixtures\\Classes\\ContentController', 'Tx_Flux_Controller_ContentController');
