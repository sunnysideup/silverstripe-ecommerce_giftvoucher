# Upgrade Guide: Silverstripe CMS 6 for ecommerce_giftvoucher

This document outlines the necessary steps and breaking changes required to upgrade the `ecommerce_giftvoucher` module to be compatible with Silverstripe CMS 6.

## ⚠️ BREAKING CHANGE: Project Dependencies

Your project's `composer.json` must be updated to meet the new requirements for this module.

- **`sunnysideup/ecommerce`**: Now requires version `^33.0` (up from `5.x-dev`). This is a significant update; review the core e-commerce module's changelog for details.
- **`silverstripe/recipe-cms`**: Now requires version `^6.0` (up from `^4.0 || ^5.0`).

```json
"require": {
    "sunnysideup/ecommerce": "^33.0",
    "silverstripe/recipe-cms": "^6.0"
}
```

## ⚠️ BREAKING CHANGE: Configuration

The mechanism for remapping class names during database builds has changed. Update your YAML configuration files.

- **`SilverStripe\ORM\DatabaseAdmin`**: This class is deprecated and has been replaced by `SilverStripe\Dev\DbBuild`.

Update your `.yml` configuration as follows:

```yaml
# Before
SilverStripe\ORM\DatabaseAdmin:
  classname_value_remapping:
    # ...

# After
SilverStripe\Dev\DbBuild:
  classname_value_remapping:
    # ...
```

## 🚨 CRITICAL REVIEW REQUIRED / RISKY: API Changes

Several APIs have been updated, which may require significant changes to your project's custom code.

### `GiftVoucherProductPage.php`

This class has undergone substantial changes to align with Silverstripe 6 conventions.

- **`$icon` property removed**: The static `$icon` property has been replaced with `$cms_icon`.
- **`$description` property renamed**: The static `$description` property is now `$class_description`.
- **Method Overrides**: PHP attributes (`#[Override]`) have been added to many methods. While this is not a breaking change in itself, it signals stricter adherence to parent class APIs.
- **`getCMSFields()` Simplification**: Logic for removing fields like `Weight`, `Price`, `Model` has been removed. This is now handled by the new `scaffold_cms_fields_settings` static array.
- **`getSettingsFields()` Logic Change**: The logic for controlling menu and search visibility has been modified. The `ShowInMenus` and `ShowInSearch` fields are now removed directly, and their values are set to `false` if `AlwaysHideFromSearchAndMenus` is true. Review this if you have custom visibility logic.
- **🚨 RISKY: Repeated `$scaffold_cms_fields_settings` definitions**: **The `GiftVoucherProductPage.php` file contains multiple, conflicting definitions for the `$scaffold_cms_fields_settings` static array. This appears to be an error from an incomplete merge or refactoring. You must consolidate these into a single, correct definition to avoid unpredictable behavior in the CMS.**

### `GiftVoucherProductPageController.php`

- **Validator Class Changed**: `SilverStripe\Forms\RequiredFields` is deprecated and has been replaced with `SilverStripe\Forms\Validation\RequiredFieldsValidator`. Update any custom forms that use this validator.

### `Model/GiftVoucherProductPageProductOrderItem.php`

- **`#[Override]` Attributes**: PHP attributes have been added to all overridden methods (`i18n_singular_name`, `plural_name`, `getUnitPrice`, etc.). This enforces stricter inheritance but should not break existing functionality.

