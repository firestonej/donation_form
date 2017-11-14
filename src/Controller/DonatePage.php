<?php

namespace Drupal\donation_form\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provide a basic page to implement the Stripe checkout.js form.
 *
 * This form allows us to retrieve a token that is then sent over to
 * StripeDonation for processing, without having to re-create the
 * universe of basic payment processing (address, card, etc.)
 */
class DonatePage extends ControllerBase {

  /**
   * Page to kick off the donation process.
   *
   */
  public function description() {
    $markup = '<p>' . $this->t('Your support is appreciated!') . '</p>';
    $content['intro'] = [
      '#markup' => $markup,
    ];

    // @todo: Load key from settings
    $pub_key = 'pk_test_dxuRSSoOdH3cSDitfA1t96ny';

    // @todo: Allow user choices; would require some extra form magic to pass the amount to StripeDonation.
    $price = 1;
    $is_free = FALSE;

    $link_text = $this->t('Donate $@price', ['@price' => $price]);

    $stripe_form = [
      '#theme' => 'stripe_form',
      '#data' => [
        'amount' => $price * 100,
        'name' => $this->t('The ThinkShout Foundation'),
        'description' => $this->t('Won\'t someone think of the devs?'),
        'key' => $pub_key,
        'zip_code' => 'true',
        'locale' => 'auto',
        'image' => 'https://thinkshout.com/assets/images/ts_icon.jpg',
        'email' => \Drupal::currentUser()->getEmail(), // If NULL (anon) this will allow email entry on the form.
        'label' => $link_text,
      ],
      '#is_free' => $is_free,
      '#price' => $price,
      '#action' => '/donation/charge',
      '#attached' => [
        'library' => [
          'donation_form/checkout',
        ],
      ],
    ];

    // Will be rendered by stripe-form.html.twig.
    $content['stripe_form'] = $stripe_form;

    return $content;
  }

}
