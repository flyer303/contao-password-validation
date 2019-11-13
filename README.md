terminal42/contao-password-validation
=====================================

A Contao 4 bundle that validates user passwords against your password policy.


## Features

- Validate a password against your organization policies
- Force members to do a password-change


## Installation

Choose the installation method that matches your workflow!


### Installation via Contao Manager

Search for `terminal42/contao-password-validation` in the Contao Manager and add it to your installation. Finally,
update the packages.

### Manual installation

Add a composer dependency for this bundle. Therefore, change in the project root and run the following:

```bash
composer require terminal42/contao-password-validation
```

Depending on your environment, the command can differ, i.e. starting with `php composer.phar …` if you do not have 
composer installed globally.

Then, update the database via the Contao install tool.


## Configuration

### Password validation

Add the following configuration parameters to your `app/config/config.yml`:  
(Skip options that you do not need)

```yml
terminal42_password_validation:
  Contao\FrontendUser:
    min_length: 10
    max_length: 20
    require:
      uppercase: 1
      lowercase: 1
      numbers: 1
      other: 1
    other_chars: "+*ç%&/()=?"
    invalid_attempts: 3
    password_history: 10
    change_days: 90
    haveibeenpwned: 1
  Contao\BackendUser:
    min_length: 10
    haveibeenpwned: 1
```

Parameter | Purpose
--------- | -------
`invalid_attempts`: | Disable the user. Requires an admin to enable the account. Create a notification with type "account_disabled" which will be sent out to the admin and/or user.
`password_history`: | Keep track of the latest `n` passwords, and force the users not to choose one of their recent passwords.
`change_days`: | Ask the user to change their password after certain days.
`haveibeenpwned`: | Check the user password against known data breaches reported to [';--have i been pwned?](https://haveibeenpwned.com/). The configuration allows you to specify an integer to define the [minimum number of data breaches](https://haveibeenpwned.com/API/v3#SearchingPwnedPasswordsByRange) the password needs to occur in to fail password validation.

### Password-change

1. Create a "password-change" page and place a password-change module on it. Select this page as password-change page in
the page root.
2. You can now force members to change their passwords by ticking the corresponding checkbox in the member edit-mask.

### Notifications

#### Account disabled

The bundle triggers a notification whenever an account gets disabled (only when using `invalid_attempts` options). This allows you to inform both website admin and account holder.

To create such notification, you can create a notification of type "Account disabled" in the notification center. This notification is chosen automatically for both back and front end users. If you only have one notification of this type, no further action is needed.

However, you have the possibility to select a dedicated notification:
- To use a dedicated notification for users and members, set the `nc_account_disabled` parameter in the above mentioned config tree. Just set the notification ID as parameter value.
- To use a dedicated notification for members on a certain page, you can select a notification in the page root config.

## Add your own password validator

You can add your own validation rule, e.g. a dictionary check.

Create a class that implements `PasswordValidatorInterface`. Then, create and tag a corresponding service.

```
  app.password_validation.validator.dictionary:
    class: App\PasswordValidation\Validator\Dictionary
    tags:
      - { name: terminal42_password_validation.validator, alias: dictionary }
```


## License

This bundle is released under the [MIT license](LICENSE)
