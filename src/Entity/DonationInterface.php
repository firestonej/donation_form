<?php

namespace Drupal\donation_form\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Donation entities.
 *
 * @ingroup donation_form
 */
interface DonationInterface extends  ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Donation name.
   *
   * @return string
   *   Name of the Donation.
   */
  public function getName();

  /**
   * Sets the Donation name.
   *
   * @param string $name
   *   The Donation name.
   *
   * @return \Drupal\donation_form\Entity\DonationInterface
   *   The called Donation entity.
   */
  public function setName($name);

  /**
   * Gets the Donation creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Donation.
   */
  public function getCreatedTime();

  /**
   * Sets the Donation creation timestamp.
   *
   * @param int $timestamp
   *   The Donation creation timestamp.
   *
   * @return \Drupal\donation_form\Entity\DonationInterface
   *   The called Donation entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Donation published status indicator.
   *
   * Unpublished Donation are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Donation is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Donation.
   *
   * @param bool $published
   *   TRUE to set this Donation to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\donation_form\Entity\DonationInterface
   *   The called Donation entity.
   */
  public function setPublished($published);

}
