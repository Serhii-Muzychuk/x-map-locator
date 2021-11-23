<?php

namespace Drupal\Tests\x_map_locator\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\User;

/**
 * Class MapLocatorSettingTest for functional test for x_map_locator
 * module settings page.
 *
 * @package Drupal\Tests\x_map_locator\Functional
 * @group x_map_locator
 *
 * @internal
 * @coversNothing
 */
class MapLocatorSettingTest extends BrowserTestBase {

  /**
   * Modules to enable for test.
   *
   * @var string[]
   *
   * @see \Drupal\Tests\BrowserTestBase::installDrupal()
   */
  public static $modules = [
    'field',
    'views_bulk_operations',
    'options',
    'x_map_locator',
  ];

  /**
   * The theme to install as the default for testing.
   *
   * @var string
   */
  protected $defaultTheme = 'stable';

  /**
   * Admin user entity.
   *
   * @var \Drupal\user\Entity\User
   */
  protected User $adminUser;

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUp(): void {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser([
      'access administration pages',
      'administer site configuration',
      'administer x map locator',
      'link to any page',
      'view the administration theme',
    ], 'test', TRUE);
  }

  /**
   * Test X Map locator module settings page and form functionality.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testSettings() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet(Url::fromRoute('x_map_locator.setting'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->buttonExists('Save configuration');
    $this->assertSession()->fieldExists('Google API Url');
    $this->assertSession()->fieldExists('Google Geocoder API Url');
    $this->assertSession()->fieldExists('Google Key API');
    $this->assertSession()->fieldExists('Zoom');
    $this->assertSession()->fieldExists('Map start position latitude');
    $this->assertSession()->fieldExists('Map start position longitude');
    $this->assertSession()->fieldExists('Hubspot Portal Id');
    $this->assertSession()->fieldExists('Hubspot Form Id');
    $this->assertSession()->fieldExists('Hints block hidden');
    $this->assertSession()->pageTextContains('MARKER TYPES.');
    $this->assertSession()->linkByHrefExists(Url::fromRoute('entity.x_map_locator_marker_type.collection')->toString());
    $this->submitForm([], 'Save configuration');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('The configuration options have been saved.');
    \Drupal::service('module_installer')->install(['x_map_locator_sales_rep']);
    $this->drupalGet(Url::fromRoute('x_map_locator.setting'));
    $this->assertSession()->fieldExists('Territory code limit');
    $this->submitForm([], 'Save configuration');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('The configuration options have been saved.');
  }

}
