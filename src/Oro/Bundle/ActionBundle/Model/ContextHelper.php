<?php

namespace Oro\Bundle\ActionBundle\Model;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class ContextHelper
{
    const ROUTE_PARAM = 'route';
    const ENTITY_ID_PARAM = 'entityId';
    const ENTITY_CLASS_PARAM = 'entityClass';

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var RequestStack */
    protected $requestStack;

    /** @var array */
    protected $actionContexts = [];

    /** @var  PropertyAccessor */
    protected $propertyAccessor;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param RequestStack $requestStack
     */
    public function __construct(DoctrineHelper $doctrineHelper, RequestStack $requestStack = null)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->requestStack = $requestStack;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->normalizeContext(
            [
                self::ROUTE_PARAM => $this->getRequestParameter(self::ROUTE_PARAM),
                self::ENTITY_ID_PARAM => $this->getRequestParameter(self::ENTITY_ID_PARAM),
                self::ENTITY_CLASS_PARAM => $this->getRequestParameter(self::ENTITY_CLASS_PARAM),
            ]
        );
    }

    /**
     * @param array|null $context
     * @return ActionContext
     */
    public function getActionContext(array $context = null)
    {
        if (!$context) {
            $context = $this->getContext();
        } else {
            $context = $this->normalizeContext($context);
        }

        $hash = $this->generateHash($context, [self::ENTITY_CLASS_PARAM, self::ENTITY_ID_PARAM]);

        if (!array_key_exists($hash, $this->actionContexts)) {
            $entity = null;

            if ($context['entityClass']) {
                $entity = $this->getEntityReference(
                    $context[self::ENTITY_CLASS_PARAM],
                    $context[self::ENTITY_ID_PARAM]
                );
            }

            $this->actionContexts[$hash] = new ActionContext($entity ? ['data' => $entity] : []);
        }

        return $this->actionContexts[$hash];
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function getRequestParameter($name, $default = null)
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request ? $request->get($name, $default) : $default;
    }

    /**
     * @param array $context
     * @return array
     */
    protected function normalizeContext(array $context)
    {
        return array_merge(
            [
                self::ROUTE_PARAM => null,
                self::ENTITY_ID_PARAM => null,
                self::ENTITY_CLASS_PARAM => null,
            ],
            $context
        );
    }

    /**
     * @param string $entityClass
     * @param mixed $entityId
     * @return Object
     */
    protected function getEntityReference($entityClass, $entityId)
    {
        $entity = null;

        if ($this->doctrineHelper->isManageableEntity($entityClass)) {
            if ($entityId) {
                $entity = $this->doctrineHelper->getEntityReference($entityClass, $entityId);
            } else {
                $entity = $this->doctrineHelper->createEntityInstance($entityClass);
            }
        }

        return $entity;
    }

    /**
     * @param array $context
     * @param array $properties
     * @return string
     */
    protected function generateHash(array $context, array $properties)
    {
        $array = [];
        foreach ($properties as $property) {
            $array[$property] = $this->getPropertyAccessor()->getValue($context, sprintf('[%s]', $property));
        }

        return md5(json_encode(array_multisort($array), JSON_NUMERIC_CHECK));
    }

    /**
     * @return PropertyAccessor
     */
    protected function getPropertyAccessor()
    {
        if (!$this->propertyAccessor) {
            $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        return $this->propertyAccessor;
    }
}
