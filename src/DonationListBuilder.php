<?php

namespace Drupal\donation_form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Donation entities.
 *
 * @ingroup donation_form
 */
class DonationListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Donation ID');
    $header['payment_email'] = $this->t('Email');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\donation_form\Entity\Donation */
    $row['id'] = $entity->id();
    $row['payment_email'] = Link::createFromRoute(
      $entity->label(),
      'entity.donation.edit_form',
      ['donation' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
