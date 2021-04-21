# AlgebraKIT batch shortcode

Use this shortcode in wordpress to add multiple exercises to a page and initialize them in batch.

## Setup
1. Copy the `shortcode.php` and `widgetLoader.js` to your wordpress theme's directory.
2. Find the `functions.php` and add the following line:
`include(get_template_directory()."/shortcode.php");`

## Usage
1. A single `akit-settings` shortcode must be included on the page where you want to use AlgebraKIT An example of this shortcode is shown below. It has the following attributes:
    - `api-key`: The only required attribute. The API key created in the management console. No default value.
    - `env`: can be `prod`, `staging` or `local`. Default is `prod`.
    - `theme`: Default is `akit`.
    Example:
    `[akit-settings api-key="paste.your.api.key.here" env="prod" theme="akit"][/akit-settings]`
2. For each exercise you want to include on the page, add an `akit-exercise` shortcode with an attibute `exercise-id` containing the id of the exercise that should be shown here
    Example:
    `[akit-exercise exercise-id="aabb1122-cc33-dd44-ee55-fffff67890"][/akit-exercise]`

---

**AlgebraKiT**<br>
Official Website: (https://algebrakit-learning.com)<br>
Documentation: (https://docs.algebrakit-learning.com)