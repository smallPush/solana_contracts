<?php

namespace Drupal\solana_contracts\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\solana_contracts\Entity\Contract;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for the admin dashboard.
 */
class AdminController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs an AdminController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, FormBuilderInterface $form_builder) {
    $this->entityTypeManager = $entity_type_manager;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('form_builder')
    );
  }

  /**
   * Displays the admin dashboard.
   */
  public function dashboard() {
    $contracts = $this->entityTypeManager->getStorage('contract')->loadMultiple();

    $build = [
      '#markup' => '<h2>Contracts</h2>',
    ];

    $build['contracts_table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Title'),
        $this->t('Status'),
        $this->t('Operations'),
      ],
    ];

    foreach ($contracts as $contract) {
      $build['contracts_table'][$contract->id()] = [
        'title' => ['#markup' => $contract->label()],
        'status' => ['#markup' => $contract->get('contract_status')->value],
        'operations' => $this->formBuilder->getForm('\Drupal\solana_contracts\Form\AdminContractStatusForm', $contract),
      ];
    }

    return $build;
  }
}
