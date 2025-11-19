<?php

namespace Drupal\solana_contracts\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Cache\CacheBackendInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * API Controller for Solana Contracts.
 */
class ApiController extends ControllerBase {

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Constructs an ApiController object.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   */
  public function __construct(CacheBackendInterface $cache) {
    $this->cache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('cache.default')
    );
  }

  /**
   * Returns all contracts and their status.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function getContracts() {
    $cid = 'solana_contracts:api:contracts';
    if ($cache = $this->cache->get($cid)) {
      return new JsonResponse($cache->data);
    }

    $storage = $this->entityTypeManager()->getStorage('contract');
    $contracts = $storage->loadMultiple();
    $data = [];

    foreach ($contracts as $contract) {
      /** @var \Drupal\solana_contracts\Entity\Contract $contract */
      $data[] = [
        'id' => $contract->id(),
        'title' => $contract->label(),
        'description' => $contract->get('description')->value,
        'status' => $contract->get('contract_status')->value,
        'party_a' => $contract->get('party_a')->target_id,
        'party_b' => $contract->get('party_b')->target_id,
        'hash' => $contract->get('hash')->value,
        'created' => $contract->get('created')->value,
        'expires' => $contract->get('expires')->value,
      ];
    }

    // Cache the data permanently, invalidating when contracts are modified.
    $this->cache->set($cid, $data, CacheBackendInterface::CACHE_PERMANENT, ['contract_list']);

    return new JsonResponse($data);
  }
}
