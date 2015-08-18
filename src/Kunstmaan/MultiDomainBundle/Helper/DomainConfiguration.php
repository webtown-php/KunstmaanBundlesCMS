<?php

namespace Kunstmaan\MultiDomainBundle\Helper;

use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Helper\DomainConfiguration as BaseDomainConfiguration;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DomainConfiguration extends BaseDomainConfiguration
{
    const OVERRIDE_HOST = '_override_host';

    /**
     * @var array
     */
    private $hosts;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->hosts   = $container->getParameter('kunstmaan_multi_domain.hosts');
        $this->session = $container->get('session');
    }

    /**
     * @return string
     */
    public function getHost()
    {
        if ($this->session->isStarted() &&
            $this->session->has(self::OVERRIDE_HOST)
        ) {
            return $this->session->get(self::OVERRIDE_HOST);
        }

        return parent::getHost();
    }

    /**
     * @return array
     */
    public function getHosts()
    {
        return array_keys($this->hosts);
    }

    /**
     * @return string
     */
    public function getDefaultLocale()
    {
        $host = $this->getHost();
        if (isset($this->hosts[$host]['default_locale'])) {
            return $this->hosts[$host]['default_locale'];
        }

        return parent::getDefaultLocale();
    }

    /**
     * @return bool
     */
    public function isMultiLanguage()
    {
        $host = $this->getHost();
        if (isset($this->hosts[$host])) {
            $hostInfo = $this->hosts[$host];

            return ('multi_lang' === $hostInfo['type']);
        }

        return parent::isMultiLanguage();
    }

    /**
     * @return array
     */
    public function getFrontendLocales()
    {
        $host = $this->getHost();
        if (isset($this->hosts[$host]['locales'])) {
            return array_keys($this->hosts[$host]['locales']);
        }

        return parent::getBackendLocales();
    }

    /**
     * @return array
     */
    public function getBackendLocales()
    {
        $host = $this->getHost();
        if (isset($this->hosts[$host]['locales'])) {
            return array_values($this->hosts[$host]['locales']);
        }

        return parent::getBackendLocales();
    }

    /**
     * @return bool
     */
    public function isMultiDomainHost()
    {
        $host = $this->getHost();

        return isset($this->hosts[$host]);
    }

    /**
     * Fetch the root node for the current host
     */
    public function getRootNode()
    {
        if (!$this->isMultiDomainHost()) {
            return parent::getRootNode();
        }

        $host         = $this->getHost();
        $internalName = $this->hosts[$host]['root'];
        $em           = $this->container->get('doctrine.orm.entity_manager');
        $nodeRepo     = $em->getRepository('KunstmaanNodeBundle:Node');
        $rootNode     = $nodeRepo->getNodeByInternalName($internalName);

        return $rootNode;
    }

    /**
     * Return (optional) extra config settings for the current host
     */
    public function getExtraData()
    {
        $host = $this->getHost();
        if (!isset($this->hosts[$host]['extra'])) {
            return parent::getExtraData();
        }

        return $this->hosts[$host]['extra'];
    }
}