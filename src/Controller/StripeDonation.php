<?php

namespace Drupal\donation_form\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\donation_form\Entity\Donation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Stripe\Stripe;

/**
 * Class StripeDonation
 *
 * @package Drupal\donation_form
 */
Class StripeDonation extends ControllerBase {

  /**
   * Constructor.
   */
  public function __construct() {
    // @todo: Load key from settings
    Stripe::setApiKey('sk_test_etUyAYyUHhjZgKUwo0UF3TrX');
  }

  /**
   * Makes a Charge call to the Stripe API.
   *
   * @return String
   *   Returns status message.
   */
  public function donate(Request $request) {

    // Checkout.js request passed to our handler.
    $token = $request->get('stripeToken');

    if (!$token) {
      throw new \Exception("Required data is missing!");
    }

    try {
      $user = \Drupal::currentUser()->getAccount();

      $charge = \Stripe\Charge::create(
        [
          // Convert to cents.
          "amount" => 100,
          "source" => $token,
          "description" => $this->t('Donation made by @user', ['@user' => $user->getAccountName()]),
          'currency' => 'USD',
          "metadata" => [
            'uid' => $user->id(),
          ],
        ]
      );

      // Prep entity for logging.
      $donation_data = [
        'uid' => $user->id(),
        'payment_status' => $charge->status,
        'payment_amount' => $charge->amount,
        'payment_email' => $charge->source->name,
        'data' => Json::encode([
          'charge' => $charge,
          'request' => $request
          ])
      ];

      $donation = Donation::create($donation_data);
      $donation->save();

      // Inform the user and watchdog of donation status.
      if ($charge->paid === TRUE) {
        drupal_set_message($this->t("Thank you. Your payment has been processed."));
        \Drupal::logger('donation_form')->notice('New donation made by @user for @amount', [
          '@user' => $user->getAccountName(),
          '@amount' => $charge->amount
        ]);
      }
      else {
        drupal_set_message($this->t("We're sorry, but your payment failed! @args", ["@args" => $request->getContent()]));
        \Drupal::logger('donation_form')->notice('Donation attempt by @user FAILED! <br/> @failure', [
          '@user' => $user->getAccountName(),
          '@failure' => $charge->failure_message
        ]);
      }

      return $this->redirect('donation_form.donate');
    }
    catch (\Exception $e) {
      \Drupal::logger('donation_form')->error('Error: @error <br /> @args', [
        '@args' => Json::encode([
          'token' => $token,
          'request' => $request,
        ]),
        '@error' => $e->getMessage(),
      ]);

      // @todo: More graceful handling, inform user of what to do next, etc.
      drupal_set_message($this->t("Payment failed."), 'error');
      return $this->redirect('donation_form.donate');
    }
  }
}