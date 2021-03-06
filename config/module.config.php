<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
return array(
    'router' => array(
        'routes' => array(
            
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'visio-crud-modeler' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/visio-crud-modeler',
                    'defaults' => array(
                        '__NAMESPACE__' => 'VisioCrudModeler\Controller',
                        'controller' => 'Index',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller[/:action]][/:id]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array()
                        )
                    )
                )
            )
        )
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory'
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator'
        )
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo'
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'VisioCrudModeler\Controller\Index' => 'VisioCrudModeler\Controller\IndexController',
            'VisioCrudModeler\Controller\Customer' => 'VisioCrudModeler\Controller\CustomerController',
            'VisioCrudModeler\Controller\Console' => 'VisioCrudModeler\Controller\ConsoleController'
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'visiocrudmodeler/index/index' => __DIR__ . '/../view/visio-crud-modeler/index/index.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view'
        )
    ),
    
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
                'crud-list-generators' => array(
                    'options' => array(
                        'route' => 'list generators',
                        'defaults' => array(
                            'controller' => '\VisioCrudModeler\Controller\Console',
                            'action' => 'list'
                        )
                    )
                ),
                'crud-generate' => array(
                    'options' => array(
                        'route' => 'generate [<generator>] [--author=] [--copyright=] [--project=] [--license=] [--modulesDirectory=] [--moduleName=] [--adapterServiceKey=]',
                        'defaults' => array(
                            'controller' => '\VisioCrudModeler\Controller\Console',
                            'action' => 'generate'
                        )
                    )
                )
            )
        )
    ),
    'VisioCrudModeler' => array(
        'params' => array(
            'author' => 'VisioCrudModeler',
            'copyright' => 'HyPHPers',
            'project' => 'VisioCrudModeler generated models',
            'license' => 'MIT',
            'modulesDirectory' => getcwd() . '/modules',
            'moduleName' => 'Crud',
            'adapterServiceKey' => '\Zend\Db\Adapter\Adapter'
        )
    )
);
