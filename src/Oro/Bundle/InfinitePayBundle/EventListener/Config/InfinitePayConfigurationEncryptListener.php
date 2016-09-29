<?php

namespace Oro\Bundle\InfinitePayBundle\EventListener\Config;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Event\ConfigGetEvent;
use Oro\Bundle\ConfigBundle\Event\ConfigSettingsUpdateEvent;
use Oro\Bundle\InfinitePayBundle\DependencyInjection\Configuration;
use Oro\Bundle\InfinitePayBundle\DependencyInjection\OroInfinitePayExtension;
use Oro\Bundle\SecurityBundle\Encoder\Mcrypt;

class InfinitePayConfigurationEncryptListener
{
    /** @var MCrypt */
    protected $encoder;

    /**
     * @param Mcrypt $encoder
     */
    public function __construct(Mcrypt $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param ConfigGetEvent $event
     */
    public function loadConfig(ConfigGetEvent $event)
    {
        $key = $event->getKey();

        if (!$this->isRequiredEncrypt($key)) {
            return;
        }

        $value = $this->getDirectValueFromEvent($event);

        // Load from default configuration. Data is not encrypted.
        if (null === $value) {
            return;
        }

        $value = $this->encoder->decryptData($value);

        $this->setDirectValueToEvent($event, $value);
    }

    /**
     * @param ConfigSettingsUpdateEvent $event
     */
    public function beforeSave(ConfigSettingsUpdateEvent $event)
    {
        $settings = $event->getSettings();

        foreach ($settings as $configKey => $setting) {
            if (!$this->isRequiredEncrypt($configKey)) {
                continue;
            }

            $settings[$configKey]['value'] = $this->encoder->encryptData($setting['value']);
        }

        $event->setSettings($settings);
    }

    /**
     * @param string $configFullKey Config model name
     *
     * @return bool
     */
    protected function isRequiredEncrypt($configFullKey)
    {
        list($extensionAlias, $configKey) = $this->parseConfigKey($configFullKey);

        if ($extensionAlias !== $this->getExtensionAlias()) {
            return false;
        }

        return in_array($configKey, $this->getConfigKeysToEncrypt(), true);
    }

    /**
     * @param string $configFullKey
     *
     * @return array An array with 2 elements: [extensionAlias, configKey]
     */
    protected function parseConfigKey($configFullKey)
    {
        return explode(ConfigManager::SECTION_MODEL_SEPARATOR, (string) $configFullKey, 2);
    }

    /**
     * Get direct value of config.
     *
     * @param ConfigGetEvent $event
     *
     * @return mixed
     */
    protected function getDirectValueFromEvent(ConfigGetEvent $event)
    {
        $eventValue = $event->getValue();

        return $eventValue !== null && $event->isFull() ? $eventValue['value'] : $eventValue;
    }

    /**
     * Set direct value to event.
     *
     * @param ConfigGetEvent $event
     * @param mixed          $value
     */
    protected function setDirectValueToEvent(ConfigGetEvent $event, $value)
    {
        $eventValue = $event->getValue();
        if ($event->isFull()) {
            $eventValue['value'] = $value;
        } else {
            $eventValue = $value;
        }

        $event->setValue($eventValue);
    }

    /**
     * Keys to encrypt.
     *
     * @return array
     */
    protected function getConfigKeysToEncrypt()
    {
        return [
            Configuration::INFINITEPAY_PASSWORD_KEY,
            Configuration::INFINITPAY_SECRET_KEY,
        ];
    }

    /**
     * @return string
     */
    protected function getExtensionAlias()
    {
        return OroInfinitePayExtension::ALIAS;
    }
}
