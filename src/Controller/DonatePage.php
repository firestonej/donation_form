<?php

namespace Drupal\donation_form\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Page to kick off the donation process.
 */
class DonatePage extends ControllerBase {

  /**
   * Lists the examples provided by form_example.
   */
  public function description() {
    $markup = '<p>' . $this->t('Your support is appreciated!') . '</p>';

    $content['intro'] = [
      '#markup' => $markup,
    ];

    // @todo: Load from settings
    $pub_key = 'pk_test_dxuRSSoOdH3cSDitfA1t96ny';

    $price = 2;

    $is_free = FALSE;
    $link_text = $this->t('Donate $@price', ['@price' => $price]);

//    ksm(Url::fromRoute('donation_form.stripe_donation'));

    $stripe_form = [
      '#theme' => 'stripe_form',
      '#data' => [
        // Price is specified in cents.
        'amount' => $price * 100,
        'name' => $this->t('ThinkShout'),
        'description' => $this->t('Donations made by ThinkShout applicants'),
        'key' => $pub_key,
        'zip_code' => 'true',
        'locale' => 'auto',
        'image' => 'https://stripe.com/img/documentation/checkout/marketplace.png',
        'email' => \Drupal::currentUser()->getEmail(),
        'label' => $link_text,
      ],
      '#is_free' => $is_free,
      '#price' => $price,
//        '#entity_id' => $item->getEntity()->id()
      '#action' => '/donation/charge',
      '#attached' => [
        'library' => [
          'donation_form/checkout',
        ],
      ],
    ];

    $content['stripe_form'] = $stripe_form;

    return $content;
  }

}
