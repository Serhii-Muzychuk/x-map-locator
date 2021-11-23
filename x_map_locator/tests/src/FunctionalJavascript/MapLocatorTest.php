<?php

namespace Drupal\Tests\x_map_locator\FunctionalJavascript;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Class MapLocatorTest.
 *   Test for Map locator functionality.
 *
 * @package Drupal\Tests\x_map_locator\FunctionalJavascript
 */
class MapLocatorTest extends WebDriverTestBase {
  use StringTranslationTrait;

  /**
   * Modules to enable for this test.
   *
   * @var string[]
   */
  public static $modules = [
    'system',
    'field',
    'node',
    'file',
    'views',
    'options',
    'block',
    'user',
    'views_bulk_operations',
    'x_map_locator',
    'x_map_locator_test',
  ];

  /**
   * Loupe builder block machine name.
   */
  public const BLOCK_ID = 'x_map_locator_search_block';

  /**
   * @var string
   */
  protected $defaultTheme = 'stable';

  /**
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * {@inheritDoc}
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUp(): void {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser([
      'access content',
      'add x_map_locator_doctor entity',
      'administer x map locator',
      'import x_map_locator_doctor',
      'view the administration theme',
      'link to any page',
    ], 'test', TRUE);
  }

  /**
   * Testing Map locator.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  protected function doctorsImport() {
    $this->drupalLogin($this->adminUser);
    $page = $this->getSession()->getPage();
    $modulePath = drupal_get_path('module', 'x_map_locator');
    $filePath = $modulePath . '/tests/fixtures/doctors.csv';

    $url = Url::fromRoute('x_map_locator_doctor.import')->toString();
    $this->drupalGet($url);
    // Upload, then Submit.
    $page->attachFileToField('files[file]', \Drupal::service('file_system')->realpath($filePath));
    $this->assertSession()->waitForButton('Remove');
    $page->pressButton('Import');
    $this->assertSession()->waitForText('Number of doctor processed by batch: 3');
    $this->assertSession()->pageTextContains('Number of doctor processed by batch: 3');
    $this->assertSession()->pageTextContains('James');
    $this->assertSession()->pageTextContains('Liu');
    $this->assertSession()->pageTextContains('Peter');
    $this->assertSession()->pageTextContains('Dueckman');
    $this->assertSession()->pageTextContains('Anne');
    $this->assertSession()->pageTextContains('Compton');
  }

  /**
   * Test Block appearing on page.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  protected function mapBlockTest() {
    $this->drupalLogin($this->adminUser);
    // Set block to block layout.
    $this->drupalGet('admin/structure/block/add/' . self::BLOCK_ID . '/' . $this->defaultTheme);
    $edit = [
      'region' => 'content',
      'settings[label]' => 'Map Locator',
      'settings[label_display]' => TRUE,
    ];
    $this->submitForm($edit, $this->t('Save block'));
    $this->getSession()->getPage()->hasContent('The block configuration has been saved.');
    $this->drupalGet('<front>');
    // Check element with map on page.
    $this->assertSession()->elementExists('css', '.map-wrapper');
  }

  /**
   * Test default search.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  protected function defaultSearchTest() {
    $this->doctorsImport();
    $this->mapBlockTest();
    $page = $this->getSession()->getPage();
    $page->fillField('address', '98007');
    $page->selectFieldOption('radius', '20');
    $page->pressButton('edit-submit');
    $this->assertSession()->waitForElement('css', '.container--item-address');
    $this->assertSession()->elementExists('css', '.container--item-address');
    $this->assertSession()->elementExists('css', '.info--doc-type--address');
    $this->assertSession()->pageTextContains('A SMILING HEART DENTISTRY');
    $this->assertSession()->pageTextContains('15419 NE 20TH ST STE 103');
    $this->assertSession()->pageTextContains('Schedule Appointment');
  }

  /**
   * Testing Scheduled Appointment.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testScheduledAppointment() {
    $this->defaultSearchTest();
    $page = $this->getSession()->getPage();
    $this->assertSession()->elementExists('css', '.hs-form-activated-btn')->click();
    $this->assertSession()->buttonExists('Request Appointment');
    $page->fillField('firstname', 'Andrey');
    $page->fillField('lastname', 'Test');
    $page->fillField('email', 'test@test.com');
    $page->fillField('phone', 'Test');
    $this->assertSession()->elementExists('css', '.hs-form-booleancheckbox-display')->click();
    $page->selectFieldOption('preferred_meeting_time_', 'Early Morning [8am-10am]');
    $page->pressButton('Request Appointment');
  }

  /**
   * Test Advanced search.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testAdvancedSearch() {
    $this->doctorsImport();
    $this->mapBlockTest();
    $page = $this->getSession()->getPage();
    // Check advanced search(Location).
    $page->clickLink('Advanced Search');
    $page->selectFieldOption('location_form[state]', 'WA');
    $page->fillField('location_form[address]', '15419 NE 20TH ST');
    $page->fillField('location_form[suite]', '103');
    $page->fillField('location_form[city]', 'BELLEVUE');
    $page->fillField('location_form[zipcode]', '98007');
    $page->pressButton('edit-submit--2');
    $this->assertSession()->waitForElement('css', '.container--item-address');
    $this->assertSession()->elementExists('css', '.container--item-address');
    $this->assertSession()->elementExists('css', '.info--doc-type--address');
    $this->assertSession()->pageTextContains('A SMILING HEART DENTISTRY');
    $this->assertSession()->pageTextContains('15419 NE 20TH ST STE 103');
    $this->assertSession()->pageTextContains('Schedule Appointment');
    // Check advanced search(Doctor).
    $page->clickLink('Advanced Search');
    $this->assertSession()->elementExists('css', '[for="edit-search-by-doctor"]')->click();
    $page->selectFieldOption('doctor_form[state]', 'WA');
    $page->fillField('doctor_form[name]', 'James Liu');
    $page->pressButton('edit-submit--2');
    $this->assertSession()->waitForText('15419 NE 20TH ST STE 103');
    $this->assertSession()->elementExists('css', '.container--item-title');
    $this->assertSession()->elementExists('css', '.container--item-address');
    $this->assertSession()->elementExists('css', '.info--doc-type--address');
    $this->assertSession()->pageTextContains('15419 NE 20TH ST STE 103');
    $this->assertSession()->pageTextContains('A SMILING HEART DENTISTRY');
    $this->assertSession()->pageTextContains('Schedule Appointment');
  }

  /**
   * Test for Hints block on map.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testHintsBlock() {
    $this->drupalLogin($this->adminUser);
    $page = $this->getSession()->getPage();
    $modulePath = drupal_get_path('module', 'x_map_locator');
    $filePath = $modulePath . '/tests/fixtures/Frame 809.svg';
    $fileSystem = \Drupal::service('file_system');
    $fileSystem->mkdir($targetDir = 'public://' . $this->randomMachineName());
    $retrievedFile = file_save_data($filePath, $targetDir);
    $marker = \Drupal::entityTypeManager()->getStorage('x_map_locator_marker_type')->create([
      'label' => 'Test marker',
      'id' => 'test_marker',
      'description' => 'Test marker description',
      'weight' => '1',
      'icon' => [
        'target_id' => $retrievedFile->id(),
      ],
      'internal_icon' => [
        'target_id' => $retrievedFile->id(),
      ],
    ]);
    $marker->save();
    // Change hints block display.
    $this->drupalGet(Url::fromRoute('x_map_locator.setting'));
    $this->assertSession()->fieldExists('Hints block hidden');
    $this->submitForm(['hints_hide' => 0], 'Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved.');
    // Check block on front page.
    $this->mapBlockTest();
    $this->assertSession()->elementExists('css', '.action--description')->click();
    $page->hasContent('Test marker');
    $page->hasContent('Test marker description');
  }

}
