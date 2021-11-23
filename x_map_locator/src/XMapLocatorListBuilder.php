<?php

namespace Drupal\x_map_locator;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @TechDoc("Defines a class to build a listing of doctor entity.")
 *
 * @see \Drupal\x_map_locator\Entity\XMapLocatorDoctor
 */
class XMapLocatorListBuilder extends EntityListBuilder {

  /**
   * @TechDoc ("Common interface for the language manager service.")
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * @TechDoc("Constructs a new XMapLocatorListBuilder object.")
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, LanguageManagerInterface $languageManager) {
    parent::__construct($entity_type, $storage);
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('language_manager')
    );
  }

  /**
   * @TechDoc("Set current language in operation url.")
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return array
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    // Get current language.
    $currentLanguage = $this->languageManager->getCurrentLanguage();
    foreach (array_keys($operations) as $operation) {
      $urlLang = $operation['url']->getOption('language');
      // Check current language.
      if ($currentLanguage->getId() !== $urlLang->getId()) {
        // Set current language in operation url.
        $operation['url']->setOption('language', $currentLanguage);
      }
    }
    return $operations;
  }

}
