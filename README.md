# misc-scripts
Miscellaneous scripts and other useful things

bluecoat_sitereview.php
-- This script will query the Blue Coat WebPulse website and return the categorization of a URL. It can be run actively against a single URL, or referencing a line break separated list. Includes throttling and error checking controls.

debian_install.sh
-- This script completes default installation activities for a Debian virtual machine running on VMWare ESXi. Works on Debian v6+.
** Update after 9/20/22 includes secure repo updates for Debian STABLE. Older versions will be upgraded automatically.

Run using: `bash <(curl -s -L http://debian.x01.us)`

okta_expire.sh
-- This script leverages the Okta API to force a user password expiration without disrupting existing sessions. Works on macOS and Linux. Requires curl and grep.
