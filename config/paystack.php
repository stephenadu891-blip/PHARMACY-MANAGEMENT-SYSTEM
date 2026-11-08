<?php
// Set this to your Paystack SECRET key from the Paystack Dashboard.
// For local testing use a test secret key. Never expose this key in browser JavaScript or commit a live key.
define("PAYSTACK_SECRET_KEY", getenv("iteg4") ?: "sk_test_REPLACE_WITH_YOUR_PAYSTACK_TEST_SECRET_KEY");
define("PAYSTACK_CALLBACK_URL", "http://localhost/pharmacy_management/paystack_callback.php");
