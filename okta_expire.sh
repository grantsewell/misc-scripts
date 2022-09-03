#!/bin/bash
# Expire Okta Password v1.0
# Expires an Okta account password immediate. User session is not terminated and temp password not assigned.
# At next login or session expiration, user will be prompted for a password reset.

OktaApiToken=YOUR_API_TOKEN #https://developer.okta.com/docs/guides/create-an-api-token/main/
OktaDomain=YOUR_OKTA_DOMAIN #https://mydomain.okta.com

# Ask which user
read -p "Enter user login/email address: " OktaUser

OktaJSON=$(curl -s -X GET \
-H "Accept: application/json" \
-H "Content-Type: application/json" \
-H "Authorization: SSWS $OktaApiToken" \
"https://$OktaDomain/api/v1/users?search=profile.login%20eq%20%22$OktaUser%22")

OktaIDURL=$(echo "$OktaJSON" | grep -Eo "(http|https)://[a-zA-Z0-9./?=_%:-]*")

curl -s -X POST \
-H "Accept: application/json" \
-H "Content-Type: application/json" \
-H "Authorization: SSWS $OktaApiToken" \
"$OktaIDURL/lifecycle/expire_password?tempPassword=false" > /dev/null

echo "Password Expired for $OktaUser"
