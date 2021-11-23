<?php

namespace Drupal\x_map_locator\Form;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\x_map_locator\Entity\XMapLocatorDoctor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @TechDoc("Provides an form for import doctor entyties from csv file").
 */
class XMapLocatorDoctorImportForm extends FormBase {
  /**
   * Batch Builder.
   *
   * @var \Drupal\Core\Batch\BatchBuilder
   */
  protected $batchBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = new static();
    $instance->batchBuilder = new BatchBuilder();
    $instance->locatorManager = $container->get('x_map_locator.locator_manager');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->doctorStorage = $container->get('entity_type.manager')->getStorage('x_map_locator_doctor');
    $instance->fileStorage = $container->get('entity_type.manager')->getStorage('file');
    $instance->messenger = $container->get('messenger');

    return $instance;
  }

  /**
   * @TechDoc("Provides get form id method.")
   *
   * {@inheritDoc}.
   */
  public function getFormId(): string {
    return 'map_doctor_import_form';
  }

  /**
   * @TechDoc("Form constructor.")
   *
   * @param array $form
   *   An associative array containing the structure of the form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form
   *
   * @return array
   *   The form structure
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('File'),
      '#description' => $this->t('Upload CSV file'),
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
        'file_validate_size' => [25600000],
      ],
      '#upload_location' => 'public://csv-import/',
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
    ];
    $form['actions']['delete'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete All'),
      '#submit' => ['::deleteHandler'],
    ];

    return $form;
  }

  /**
   * @TechDoc("Cancel form action.")
   *
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('view.doctors_list.page_1');
  }

  /**
   * @TechDoc("Delete all XMapLocatorDoctor form handler.")
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function deleteHandler(array &$form, FormStateInterface $form_state) {
    $doctorEntities = $this->doctorStorage->loadMultiple();
    if (!empty($doctorEntities)) {
      $this->batchBuilder
        ->setTitle($this->t('Processing'))
        ->setInitMessage($this->t('Initializing.'))
        ->setProgressMessage($this->t('Completed @current of @total.'))
        ->setErrorMessage($this->t('An error has occurred.'));
      $this->batchBuilder->setFile(drupal_get_path('module', 'x_map_locator') . '/src/Form/XMapLocatorDoctorImportForm.php');
      $this->batchBuilder->addOperation([
        $this,
        'processItems',
      ], [$doctorEntities, 'delete', 20]);
      $this->batchBuilder->setFinishCallback([$this, 'finished']);
      batch_set($this->batchBuilder->toArray());
    }
    $form_state->setRedirectUrl(Url::fromRoute('view.doctors_list.page_1'));
  }

  /**
   * @TechDoc("Form import from CSV submission handler.")
   *
   * @param array $form
   *   An associative array containing the structure of the form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $fileId = $form_state->getValue('file');
    if (!empty($fileId)) {
      $file = $this->fileStorage->load($fileId[0]);
      $uri = $file->getFileUri();
      if (($handle = fopen($uri, 'r'))) {
        $rows = array_map('str_getcsv', file($uri));
        $header = array_shift($rows);
        $data = [];
        foreach ($rows as $row) {
          $data[] = array_combine($header, $row);
        }
        fclose($handle);
      }
      if (!empty($data)) {
        $this->batchBuilder
          ->setTitle($this->t('Processing'))
          ->setInitMessage($this->t('Initializing.'))
          ->setProgressMessage($this->t('Completed @current of @total.'))
          ->setErrorMessage($this->t('An error has occurred.'));
        $this->batchBuilder->setFile(drupal_get_path('module', 'x_map_locator') . '/src/Form/XMapLocatorDoctorImportForm.php');
        $this->batchBuilder->addOperation([
          $this,
          'processItems',
        ], [$data, 'create', 20]);
        $this->batchBuilder->setFinishCallback([$this, 'finished']);
        batch_set($this->batchBuilder->toArray());
      }
    }
    $form_state->setRedirectUrl(Url::fromRoute('view.doctors_list.page_1'));
  }

  /**
   * @TechDoc("Processor for batch operations.")
   *
   * @param $items
   * @param $action
   * @param $limit
   * @param array $context
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function processItems($items, $action, $limit, array &$context) {
    // Set default progress values.
    if (empty($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = count($items);
    }

    // Save items to array which will be changed during processing.
    if (empty($context['sandbox']['items'])) {
      $context['sandbox']['items'] = $items;
    }
    $counter = 0;
    if (!empty($context['sandbox']['items'])) {
      // Remove already processed items.
      if ($context['sandbox']['progress'] != 0) {
        array_splice($context['sandbox']['items'], 0, $limit);
      }
      foreach ($context['sandbox']['items'] as $item) {
        if ($counter != $limit) {
          if ($action == 'create') {
            $this->processCreateItem($item);
          }
          elseif ($action == 'delete') {
            $this->processDeleteItem($item);
          }
          ++$counter;
          ++$context['sandbox']['progress'];
          $context['message'] = $this->t('Now processing doctor :progress of :count', [
            ':progress' => $context['sandbox']['progress'],
            ':count' => $context['sandbox']['max'],
          ]);

          // Increment total processed item values. Will be used in finished
          // callback.
          $context['results']['processed'] = $context['sandbox']['progress'];
        }
      }
    }

    // If not finished all tasks, we count percentage of process. 1 = 100%.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
    else {
      $context['finished'] = 1;
    }
  }

  /**
   * @TechDoc("Process create XMapLocatorDoctor single item.")
   *
   * @param array $docData
   */
  public function processCreateItem(array $docData) {
    if (
      !empty($docData)
      && !empty($custNo = $docData['CUST NO'])
      && !empty($address1 = $docData['ADDRESS 1'])
      && !empty($zip = $docData['ZIP'])
      && (strlen($zip) >= 4)
      && !empty($city = $docData['CITY'])
      && !empty($area = $docData['ST'])
      && !empty($country = $docData['COUNTRY'])
    ) {
      $firstName = $docData['First Name'] ?? '';
      $lastName = $docData['Last Name'] ?? '';
      $practiceName = $docData['Practice Name'] ?? '';
      if (empty($practiceName)) {
        if (
          !empty($firstName)
          && !empty($lastName)
        ) {
          $practiceName = $docData['Name Prefix'] . ' ' . $firstName . ' ' . $lastName;
        }
      }
      if (strlen($zip) == 4) {
        $zip = '0' . $zip;
      }

      $status = TRUE;

      if (!empty($docData['STATUS']) && $docData['STATUS'] !== 'Active') {
        $status = FALSE;
      }

      $entityValues = [
        'label' => $practiceName,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'practice' => $practiceName,
        'qualification' => 'damon_premier_provider',
        'zip' => trim($zip),
        'city' => $city,
        'customer_id' => $custNo,
        'address1' => $address1,
        'address2' => $docData['ADDRESS 2'] ?? '',
        'country' => $country,
        'status' => $status,
        'name_prefix' => $docData['Name Prefix'],
        'email' => !empty($docData['EMAIL']) ? $docData['EMAIL'] : '',
        'phone' => $docData['PHONE #'] ?? '',
        'area' => $area,
        'website' => $docData['Website'] ?? '',
        'schedule_appointment' => !empty($docData['EMAIL']) ? 1 : 0,
      ];
      $additionalValues = [
        $city,
        $area,
        $zip,
        $country,
      ];
      $address = $this->locatorManager->getFullAddress(
        $entityValues['address1'],
        $entityValues['address2']
      );
      $location = $this->locatorManager->getLocationByAddress(
        $address,
        $additionalValues
      );
      if (
        !empty($location)
        && !empty($lat = $location['lat'])
        && !empty($long = $location['lng'])
      ) {
        $doctor_query = $this->doctorStorage->getQuery();
        $doctor_query->condition('address1', $entityValues['address1']);
        $doctor_query->condition('first_name', $entityValues['first_name']);
        $doctor_query->condition('last_name', $entityValues['last_name']);
        $results_ids = $doctor_query->execute();

        if (empty($results_ids)) {
          $entityValues['location_latitude'] = $lat;
          $entityValues['location_longitude'] = $long;
          $entity = $this->doctorStorage->create($entityValues);
          $entity->save();
        }
      }
    }
    else {
      $this->messenger->addWarning('Item did not import, one or more of the required fields empty, please check your import file');
    }
  }

  /**
   * @TechDoc("Process delete XMapLocatorDoctor single item.")
   *
   * @param object $deleteData
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function processDeleteItem(object $deleteData) {
    if (!empty($deleteData) && $deleteData instanceof XMapLocatorDoctor) {
      $deleteData->delete();
    }
  }

  /**
   * @TechDoc("Finished callback for the batch.")
   *
   * @param mixed $success
   * @param mixed $results
   * @param mixed $operations
   */
  public function finished($success, $results, $operations) {
    $message = $this->t('Number of doctor processed by batch: @count', [
      '@count' => $results['processed'],
    ]);
    if (!empty($success)) {
      $this->messenger()
        ->addStatus($message);
    }
    else {
      $this->messenger()
        ->addError($this->t('The doctor entity was not created'));
    }
  }

}
