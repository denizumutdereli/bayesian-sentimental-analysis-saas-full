<?php

/*
  |--------------------------------------------------------------------------
  | Register The Artisan Commands
  |--------------------------------------------------------------------------
  |
  | Each available Artisan command must be registered with the console so
  | that it is available to be called. We'll register every command so
  | the console gets access to each of the command object instances.
  |
 */

//Artisan::add(new UpdateApiKeysCommand());

Artisan::add(new deleteAccountsScheduler);
Artisan::add(new stopPitchingAccountsScheduler);
Artisan::add(new invoiceScheduler);
Artisan::add(new queriesIndex);
Artisan::add(new queriesTrigger);
Artisan::add(new endeksa);

