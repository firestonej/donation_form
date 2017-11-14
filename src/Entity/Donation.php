<?php

namespace Drupal\donation_form\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Donation entity.
 *
 * @ingroup donation_form
 *
 * @ContentEntityType(
 *   id = "donation",
 *   label = @Translation("Donation"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\donation_form\DonationListBuilder",
 *
 *     "form" = {
 *       "default" = "Drupal\donation_form\Form\DonationForm",
 *       "edit" = "Drupal\donation_form\Form\DonationForm",
 *       "delete" = "Drupal\donation_form\Form\DonationDeleteForm",
 *     },
 *     "access" = "Drupal\donation_form\DonationAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\donation_form\DonationHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "donation",
 *   admin_permission = "administer content",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "payment_email",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/donation/{donation}",
 *     "edit-form" = "/admin/content/donation/{donation}/edit",
 *     "delete-form" = "/admin/content/donation/{donation}/delete",
 *     "collection" = "/admin/content/donation",
 *   },
 *   field_ui_base_route = "donation.settings"
 * )
 */
class Donation extends ContentEntityBase implements DonationInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('payment_email')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('payment_email', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Donation entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Payment status according to Stripe.
    $fields['payment_status'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Payment Status'))
      ->setDescription(t('The status of the donation payment.'))
      ->setReadOnly(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Donation amount.
    $fields['payment_amount'] = BaseFieldDefinition::create('float')
      ->setLabel(t('Amount'))
      ->setDescription(t('The amount donated.'))
      ->setReadOnly(TRUE)
      ->setSettings([
      ])
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number',
        'weight' => -6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => -6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Will allow future sorting of donations, provides inclusivity of anonymous users, and satisfies the entity's
    // "name" requirement. (These entities are automatically created, so "name" is pointless.)
    $fields['payment_email'] = BaseFieldDefinition::create('email')
      ->setLabel(t('Email'))
      ->setDescription(t('The email associated with the payment (for anonymous users)'))
      ->setReadOnly(TRUE)
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -7,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Include the raw JSON. Unwise in production environments; should be sanitized into fields or encrypted.
    $fields['data'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data'))
      ->setDescription(t('Raw payment data from Stripe'))
      ->setReadOnly(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'textarea',
        'weight' => 10,
      ])
      ->setDisplayOptions('form', [
        'type' => 'textarea',
        'weight' => 10,
      ]);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Donation is published.'))
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
