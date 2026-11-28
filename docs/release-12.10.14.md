# u5CMS 12.10.14

Opening a saved search destination with a legacy Unicode escape now avoids a PHP deprecation in the content renderer. The previous renderer passed an extra character to the hexadecimal converter. The default error mask hid the diagnostic. Ordinary page rendering and browser highlighting continue to use the existing query. No database or configuration change is required.
