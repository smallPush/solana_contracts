<?php

namespace Drupal\solana_contracts\Tests\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\solana_contracts\ContractAccessControlHandler;

/**
 * Tests the ContractAccessControlHandler.
 *
 * @group solana_contracts
 */
class ContractAccessControlHandlerTest extends UnitTestCase {

  /**
   * The access control handler.
   *
   * @var \Drupal\solana_contracts\ContractAccessControlHandler
   */
  protected $accessControlHandler;

  /**
   * The mocked entity type.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityType;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entityType = $this->createMock(EntityTypeInterface::class);
    $this->accessControlHandler = new TestContractAccessControlHandler($this->entityType);

    // Mock the cache context manager because AccessResult uses it.
    // However, in UnitTestCase, we usually need to set up the container.
    $container = new ContainerBuilder();
    $cache_contexts_manager = $this->getMockBuilder('Drupal\Core\Cache\Context\CacheContextsManager')
      ->disableOriginalConstructor()
      ->getMock();
    $cache_contexts_manager->method('assertValidTokens')->willReturn(TRUE);
    
    $container->set('cache_contexts_manager', $cache_contexts_manager);
    \Drupal::setContainer($container);
  }

  /**
   * Tests access for admin.
   */
  public function testAdminAccess() {
    $account = $this->createMock(AccountInterface::class);
    $account->expects($this->any())
      ->method('hasPermission')
      ->with('administer contract entities')
      ->willReturn(TRUE);

    $entity = $this->createMock(EntityInterface::class);

    $result = $this->accessControlHandler->checkAccess($entity, 'view', $account);
    $this->assertTrue($result->isAllowed());
  }

  /**
   * Tests view access for parties.
   */
  public function testViewAccessForParty() {
    $account = $this->createMock(AccountInterface::class);
    $account->method('id')->willReturn(10);
    $account->expects($this->any())
      ->method('hasPermission')
      ->will($this->returnValueMap([
        ['administer contract entities', FALSE],
        ['view own contract entities', TRUE],
      ]));

    // Mock entity.
    $entity = $this->getMockBuilder('Drupal\solana_contracts\Entity\Contract')
      ->disableOriginalConstructor()
      ->getMock();
    
    // Create a mock for the field item list.
    $party_a_item = new \stdClass();
    $party_a_item->target_id = 10;
    
    $party_b_item = new \stdClass();
    $party_b_item->target_id = 20;

    $entity->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap([
        ['party_a', $party_a_item],
        ['party_b', $party_b_item],
      ]));

    $result = $this->accessControlHandler->checkAccess($entity, 'view', $account);
    $this->assertTrue($result->isAllowed());
  }
  
  /**
   * Tests deny access for non-party.
   */
  public function testViewAccessForNonParty() {
    $account = $this->createMock(AccountInterface::class);
    $account->method('id')->willReturn(30);
    $account->expects($this->any())
      ->method('hasPermission')
      ->will($this->returnValueMap([
        ['administer contract entities', FALSE],
        ['view own contract entities', TRUE],
      ]));

    // Mock entity.
    $entity = $this->getMockBuilder('Drupal\solana_contracts\Entity\Contract')
      ->disableOriginalConstructor()
      ->getMock();
    
    $party_a_item = new \stdClass();
    $party_a_item->target_id = 10;
    
    $party_b_item = new \stdClass();
    $party_b_item->target_id = 20;

    $entity->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap([
        ['party_a', $party_a_item],
        ['party_b', $party_b_item],
      ]));

    $result = $this->accessControlHandler->checkAccess($entity, 'view', $account);
    $this->assertTrue($result->isNeutral()); // Default deny/neutral
  }
}

/**
 * Expose protected method for testing.
 */
class TestContractAccessControlHandler extends ContractAccessControlHandler {
  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    return parent::checkAccess($entity, $operation, $account);
  }
}
