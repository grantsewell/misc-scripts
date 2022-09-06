#!/bin/bash
# Title:  Expire Okta Password v1.0
# Author: Grant Sewell
# Date:   09/03/2022
# Desc:   Expires an Okta account password immediately. User session is not terminated and a temporary password is not assigned.
#         At next login or session expiration, user will be prompted to set a new password.

OktaApiToken=YOUR_API_TOKEN #https://developer.okta.com/docs/guides/create-an-api-token/main/
OktaDomain=YOUR_OKTA_DOMAIN #https://mydomain.okta.com

# Ask which user
read -p "Enter user login/email address: " OktaUser

# Query the user and retreive the base query URL
OktaJSON=$(curl -s -X GET \
-H "Accept: application/json" \
-H "Content-Type: application/json" \
-H "Authorization: SSWS $OktaApiToken" \
"https://$OktaDomain/api/v1/users?search=profile.login%20eq%20%22$OktaUser%22")

# Parse the base query URL for the target user
OktaIDURL=$(echo "$OktaJSON" | grep -Eo "(http|https)://[a-zA-Z0-9./?=_%:-]*")

# Expire the password
curl -s -X POST \
-H "Accept: application/json" \
-H "Content-Type: application/json" \
-H "Authorization: SSWS $OktaApiToken" \
"$OktaIDURL/lifecycle/expire_password?tempPassword=false" > /dev/null

# Display confirmation, exit script
echo "Password Expired for $OktaUser"
exit 0
