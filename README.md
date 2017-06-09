<a href="https://www.twilio.com">
  <img src="https://static0.twilio.com/marketing/bundles/marketing/img/logos/wordmark-red.svg" alt="Twilio" width="250" />
</a>

# Advanced Call Forwarding with Twilio and PHP

[![Build Status](https://travis-ci.org/TwilioDevEd/call-forwarding-php.svg?branch=master)](https://travis-ci.org/TwilioDevEd/call-forwarding-php)

Learn how to use
[Twilio](https://www.twilio.com/docs/tutorials/walkthrough/call-forwarding-php)
to forward a series of phone calls to your state senators.

## Local Development
This project is built using PHP v5.6, and [SQLite](https://sqlite.org/index.html).

1. Clone this repository, and `cd` into it.

   ```bash
   git clone https://github.com/TwilioDevEd/call-forwarding-php.git && \
   cd call-forwarding-php
   ```

1. Install the dependencies with [Composer](https://getcomposer.org/).

   ```bash
   composer install --dev
   ```

1. Run the migrations.

   ```bash
   make migrate
   ```

   This will load `senators.json` and US zip codes into your SQLite database,
   in the root directory.

   **Please note:** Our senators dataset is likely outdated, and we've mapped
   senators to placeholder phone numbers that are set up with Twilio to read
   a message and hang up.

1. Expose your application to the internet using
   [ngrok](https://www.twilio.com/blog/2015/09/6-awesome-reasons-to-use-ngrok-when-testing-webhooks.html).
   In a separate terminal session, start ngrok with:

   ```bash
   ngrok http 8080
   ```

   Once you have started ngrok, update your TwiML application's voice URL
   setting to use your ngrok hostname. It will look something like this in
   your Twilio [console](https://www.twilio.com/console/phone-numbers/):

   ```
   https://d06f533b.ngrok.io/callcongress/welcome
   ```

1. Start your development server.

   ```bash
   make start
   ```

   Once ngrok is running, open up your browser and go to your ngrok URL.

### Run tests

To run tests first you need to populate the test db

 ```bash
make migrate_test
```

After that you can run tests by simple writing

```bash
make test
```

## Meta

* No warranty expressed or implied. Software is as is. Diggity.
* [MIT License](http://www.opensource.org/licenses/mit-license.html)
* Lovingly crafted by Twilio Developer Education.
