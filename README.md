# Smooth Re-authentication Plugin for GLPI

In GLPI 12, there is a new feature that asks users to reauthenticate (re-enter their password or TOTP code) when trying to access certain protected sections like the settings.
After reauthentication, there is a grace period (15 minutes) during which they can perform actions without being prompted again.

This feature is designed to protect against cases where a user with admin privileges walks away from their device and an attacker tries operating the account on their behalf.

There are two potential UX issues with this:
1. If a user tries accessing a protected area and they need to log in again because their session expired, they would be prompted to authenticate immediately after they log in.
2. If a user is performing several privileged actions, the grace period could run out mid-way and disrupt the user's workflow.

This plugin aims to solve these issues by:
1. Granting a 1 minute grace period at login.
2. Each time a non-AJAX request (only looks at full page requests/form submits) is performed when within a grace period, the grace period is reset. This means it changes from a strict 15 minute grace period to being 15 minutes after the user's last action which is similar to how GitHub handles this kind of feature.

**It is important to acknowledge that this does slightly reduce the security of the reauthentication feature, but it is a trade-off for better user experience.**

**The GLPI reauthentication feature, with or without this plugin, should never be considered as an absolute protection measure against unauthorized access.
Device and OS-level protections like automatic lock screens should be considered as the primary protection against unauthorized access.
The reauthentication feature is just a secondary measure.**
