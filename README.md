####Installation:

**Manual install**
1. **Add and enable this module**
2. **Install the Stripe API to your site.** (run `composer require stripe/stripe-php` from docroot.)
3. **Navigate to `/donate`.** (Or rebuild the cache i.e. `drupal cr all` to get menu links.)

**Composer install** - see bottom.

####Method

I implemented the module in a few pieces. Due to intense time constraints, my approach revolved around using as much pre-existing infrastructure as possible.

First, I used Stripe's Checkout.js as a simple way of getting basic payment data. This avoided the need to build a large custom form and left the stringent validation of payment processing to Stripe. I implemented two Controllers: one front-end route to include the Checkout.js form through Twig, and one back-end route to receive the token provided by Stripe and create a Charge object using the Stripe API.

Second, I also used the console's entity generator to skeleton up a Donation entity to store donation data. Login as an administrator and navigate to `admin/content/donation` or click the provided link.

Some pre-builts and tools I used for this included [Docksal](https://github.com/docksal/docksal), the [generate:entity:content](https://docs.drupalconsole.com/en/commands/generate-entity-content.html) command, reading and testing of the [stripe_api](https://www.drupal.org/project/stripe_api) and [stripe_checkout](https://www.drupal.org/project/stripe_checkout) modules,

####Flaws

My approach has some clear flaws:
* A second form step will be needed regardless to collect any fields other than payment processing. Custom donation amounts and user data like name or address would require this step. Another option for user data would be to force registration and include more data from `\Drupal::currentUser()`.
* The Stripe PHP API is required. I partially alleviated this by including the correct composer.json file in the module.
* I was unable to _technically_ meet the AJAX validation requirement, since I used Checkout.js – but Checkout does provide some immediate user feedback.
* Minor other things I hardcoded, like no API key configuration, no use of currencies other than USD.
* I did not implement tests. _For shame!_


####Challenges

* I had some frustration with getting the Stripe dependency to be automatically provided. As mentioned above this seems to be a Drupal 8 constraint. This could be alleviated by: A) providing the module directly through composer (like `composer require drupal/donation_form` if it was included on the package list; B) including a github URL to the release of my module in the site's `composer.json` file; or C) using a Drupal site that has the composer_manager module installed. But in this case I was told to provide one module, and this was the first time I've felt so limited by composer.
* I've never used the Stripe API, and had to sign up for it and familiarize myself with it before choosing a path.
* I struggled to choose between Page controllers implementing Checkout, and a multi-step form that did all the heavy lifting on the back-end. In the end I simply felt more comfortable with the Page route, since I thought it provided a better user experience with less time. But it offers the constraints listed above. A multi-step form would be a more robust option.
* I had only a few hours to complete this task. This is real-world likelihood, so I chose not to ask for more time. Of course I could do a better job with more time, and sometimes the best thing to do is go back to the client. But in this case I thought it was prudent to provide the best work I could in the time given.

####Scratchpad

**Parsed requirements**

* Site administrator can easily install module
* User can make a donation through Stripe

(Optional)
* User can select from pre-set amounts
* User can enter a custom amount
* User is informed immediately of problems with their data
* Site administrator can see record of donations


####Composer

You can also include this module directly in your site's `composer.json`. Add this to the `repositories` object:

````json
{
  "type": "package",
  "package" : {
    "name": "firestonej/donation_form",
    "version": "0.1.0",
    "type": "drupal-module",
    "dist": {
      "type": "zip",
      "url" : "https://github.com/firestonej/donation_form/archive/0.1.0.zip"
    }
  }
}
````

Then run:

`composer require firestonej/donation_form stripe/stripe-php`

Then enable the module as usual.