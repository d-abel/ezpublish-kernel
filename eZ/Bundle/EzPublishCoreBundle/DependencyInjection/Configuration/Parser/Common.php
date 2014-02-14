<?php
/**
 * File containing the Common class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configuration parser handling all basic configuration (aka "common")
 */
class Common extends AbstractParser
{
    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     *
     * @return void
     */
    public function addSemanticConfig( NodeBuilder $nodeBuilder )
    {
        $nodeBuilder
            ->arrayNode( 'languages' )
                ->cannotBeEmpty()
                ->info( 'Available languages, in order of precedence' )
                ->example( array( 'fre-FR', 'eng-GB' ) )
                ->prototype( 'scalar' )->end()
            ->end()
            ->arrayNode( 'database' )
                ->children()
                    ->enumNode( 'type' )->values( array( 'mysql', 'pgsql', 'sqlite' ) )->info( 'The database driver. Can be mysql, pgsql or sqlite.' )->end()
                    ->scalarNode( 'server' )->end()
                    ->scalarNode( 'port' )->end()
                    ->scalarNode( 'user' )->cannotBeEmpty()->end()
                    ->scalarNode( 'password' )->end()
                    ->scalarNode( 'database_name' )->cannotBeEmpty()->end()
                    ->scalarNode( 'charset' )->defaultValue( 'utf8' )->end()
                    ->scalarNode( 'socket' )->end()
                    ->arrayNode( 'options' )
                        ->info( 'Arbitrary options, supported by your DB driver ("driver-opts" in PDO)' )
                        ->example( array( 'foo' => 'bar', 'someOptionName' => array( 'one', 'two', 'three' ) ) )
                        ->useAttributeAsKey( 'key' )
                        ->prototype( 'variable' )->end()
                    ->end()
                    ->scalarNode( 'dsn' )->info( 'Full database DSN. Will replace settings above.' )->example( 'mysql://root:root@localhost:3306/ezdemo' )->end()
                ->end()
            ->end()
            ->scalarNode( 'cache_pool_name' )
                ->example( 'ez_site_x' )
                ->info( 'The cache pool name to use for a siteaccess / siteaccess-group, *must* be present under stash.caches: yml config. Default value is "default". NB! Setting is Deprecated, will be made redundant in future version.' )
            ->end()
            ->scalarNode( 'var_dir' )
                ->cannotBeEmpty()
                ->example( 'var/ezdemo_site' )
                ->info( 'The directory relative to web/ where files are stored. Default value is "var"' )
            ->end()
            ->scalarNode( 'storage_dir' )
                ->cannotBeEmpty()
                ->info( "Directory where to place new files for storage, it's relative to var directory. Default value is 'storage'" )
            ->end()
            ->scalarNode( 'binary_dir' )
                ->cannotBeEmpty()
                ->info( 'Directory where binary files (from ezbinaryfile field type) are stored. Default value is "original"' )
            ->end()
            ->booleanNode( 'legacy_mode' )
                ->info( 'Whether to use legacy mode or not. If true, will let the legacy kernel handle url aliases.' )
            ->end()
            // @deprecated since 5.3. Will be removed in 6.x.
            ->scalarNode( 'session_name' )
                ->info( 'DEPRECATED. Use session.name instead.' )
            ->end()
            ->arrayNode( 'session' )
                ->info( 'Session options. Will override options defined in Symfony framework.session.*' )
                ->children()
                    ->scalarNode( 'name' )
                        ->info( 'The session name. If you want a session name per siteaccess, use "{siteaccess_hash}" token. Will override default session name from framework.session.name' )
                        ->example( array( 'session' => array( 'name' => 'eZSESSID{siteaccess_hash}' ) ) )
                    ->end()
                    ->scalarNode( 'cookie_lifetime' )->end()
                    ->scalarNode( 'cookie_path' )->end()
                    ->scalarNode( 'cookie_domain' )->end()
                    ->booleanNode( 'cookie_secure' )->end()
                    ->booleanNode( 'cookie_httponly' )->end()
                ->end()
            ->end()
            ->scalarNode( 'index_page' )
                ->info( "The page that the index page will show. Default value is null." )
                ->example( '/Getting-Started' )
                ->end()
            ->arrayNode( 'http_cache' )
                ->info( 'Settings related to Http cache' )
                ->cannotBeEmpty()
                ->children()
                    ->arrayNode( 'purge_servers' )
                        ->info( 'Servers to use for Http PURGE (will NOT be used if ezpublish.http_cache.purge_type is "local").' )
                        ->example( array( 'http://localhost/', 'http://another.server/' ) )
                        ->requiresAtLeastOneElement()
                        ->prototype( 'scalar' )->end()
                    ->end()
                ->end()
            ->end()
            ->scalarNode( 'anonymous_user_id' )
                ->cannotBeEmpty()
                ->example( '10' )
                ->info( 'The ID of the user used for everyone who is not logged in.' )
            ->end()
            ->arrayNode( 'user' )
                ->children()
                    ->scalarNode( 'layout' )
                        ->info( 'Layout template to use for user related actions. This is most likely the base pagelayout template of your site.' )
                        ->example( array( 'layout' => 'eZDemoBundle::pagelayout.html.twig' ) )
                    ->end()
                    ->scalarNode( 'login_template' )
                        ->info( 'Template to use for login form. Defaults to EzPublishCoreBundle:security:login.html.twig' )
                        ->example( array( 'login_template' => 'AcmeTestBundle:User:login.html.twig' ) )
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Translates parsed semantic config values from $config to internal key/value pairs.
     *
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     */
    public function registerInternalConfig( array $config, ContainerBuilder $container )
    {
        $this->registerInternalConfigArray(
            'languages', $config, $container, self::UNIQUE
        );
        $this->registerInternalConfigArray( 'database', $config, $container );
        foreach ( $config['siteaccess']['list'] as $sa )
        {
            $database = $container->getParameter( "ezsettings.$sa.database" );
            if ( !empty( $database ) )
            {
                // DSN has priority over any other setting
                if ( isset( $database['dsn'] ) )
                {
                    $container->setParameter( "ezsettings.$sa.database.params", $database['dsn'] );
                }
                else
                {
                    // Renaming dbParams to parameters supported by Doctrine.
                    $database['dbname'] = $database['database_name'];
                    $database['host'] = $database['server'];
                    $driverMap = array(
                        'mysql' => 'pdo_mysql',
                        'pgsql' => 'pdo_pgsql',
                        'sqlite' => 'pdo_sqlite',
                    );
                    if ( isset( $driverMap[$database['type']] ) )
                    {
                        $database['driver'] = $driverMap[$database['type']];
                    }
                    else
                    {
                        $database['driver'] = $database['type'];
                    }
                    if ( isset( $database['socket'] ) )
                    {
                        $database['unix_socket'] = $database['socket'];
                    }
                    $database['driverOptions'] = $database['options'];
                    unset(
                        $database['database_name'],
                        $database['server'],
                        $database['type'],
                        $database['options'],
                        $database['socket'],
                        $database['server']
                    );
                    $container->setParameter( "ezsettings.$sa.database.params", $database );
                }
            }
        }
        foreach ( $config[$this->baseKey] as $sa => $settings )
        {
            if ( isset( $settings['legacy_mode'] ) )
            {
                $container->setParameter( "ezsettings.$sa.legacy_mode", $settings['legacy_mode'] );
                $container->setParameter( "ezsettings.$sa.url_alias_router", !$settings['legacy_mode'] );
            }
            if ( isset( $settings['cache_pool_name'] ) )
                $container->setParameter( "ezsettings.$sa.cache_pool_name", $settings['cache_pool_name'] );
            if ( isset( $settings['var_dir'] ) )
                $container->setParameter( "ezsettings.$sa.var_dir", $settings['var_dir'] );
            if ( isset( $settings['storage_dir'] ) )
                $container->setParameter( "ezsettings.$sa.storage_dir", $settings['storage_dir'] );
            if ( isset( $settings['binary_dir'] ) )
                $container->setParameter( "ezsettings.$sa.binary_dir", $settings['binary_dir'] );

            $this->registerInternalConfigArray( 'session', $config, $container );
            // session_name setting is deprecated in favor of session.name
            $sessionOptions = $container->hasParameter( "ezsettings.$sa.session" ) ? $container->getParameter( "ezsettings.$sa.session" ) : array();
            if ( isset( $sessionOptions['name'] ) )
            {
                $container->setParameter( "ezsettings.$sa.session_name", $sessionOptions['name'] );
            }
            // @deprecated session_name is deprecated, but if present, in addition to session.name, consider it instead (BC).
            if ( isset( $settings['session_name'] ) )
            {
                $sessionOptions['name'] = $settings['session_name'];
                $container->setParameter( "ezsettings.$sa.session_name", $settings['session_name'] );
                $container->setParameter( "ezsettings.$sa.session", $sessionOptions );
            }

            if ( isset( $settings['http_cache']['purge_servers'] ) )
                $container->setParameter( "ezsettings.$sa.http_cache.purge_servers", $settings['http_cache']['purge_servers'] );
            if ( isset( $settings['anonymous_user_id'] ) )
                $container->setParameter( "ezsettings.$sa.anonymous_user_id", $settings['anonymous_user_id'] );
            if ( isset( $settings['user']['layout'] ) )
                $container->setParameter( "ezsettings.$sa.security.base_layout", $settings['user']['layout'] );
            if ( isset( $settings['user']['login_template'] ) )
                $container->setParameter( "ezsettings.$sa.security.login_template", $settings['user']['login_template'] );
            if ( isset( $settings['index_page'] ) )
                $container->setParameter( "ezsettings.$sa.index_page", $settings['index_page'] );
        }
    }
}
