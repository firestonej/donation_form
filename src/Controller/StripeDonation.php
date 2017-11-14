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
//    $this->config = $config_factory->get('donation_form.settings');

    Stripe::setApiKey('sk_test_etUyAYyUHhjZgKUwo0UF3TrX');
  }

  /**
   * Makes a Charge call to the Stripe API.
   *
   *
   * @return String
   *   Returns status message.
   */
  public function donate(Request $request) {
    \Drupal::logger('donation_form')->notice('Charge URL hit');

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

      if ($charge->paid === TRUE) {
        drupal_set_message($this->t("Thank you. Your payment has been processed."));
      }
      else {
        drupal_set_message($this->t("We're sorry, but your payment failed! @args", ["@args" => $request->getContent(),]));
      }

      $donation = Donation::create($donation_data);
      $donation->save();

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

      drupal_set_message($this->t("Payment failed."), 'error');

      return new Response(NULL, Response::HTTP_FORBIDDEN);
    }
  }
}