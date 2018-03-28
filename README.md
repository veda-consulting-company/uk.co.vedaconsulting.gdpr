# GDPR #

### Overview ###

Extension to support General Data Protection Regulation

### Installation ###

* Install the extension manually in CiviCRM. More details [here](http://wiki.civicrm.org/confluence/display/CRMDOC/Extensions#Extensions-Installinganewextension) about installing extensions in CiviCRM.
* Add GDPR settings (Navigate to Contacts >> GDPR Dashboard or navigate to civicrm/gdpr/settings)

### Usage ###

* A new tab 'GDPR' in contact summary will display group subscription log for the contact, as well as the last time they accepted the site Data Policy and updated their Communications Preferences.
* 'Forget Me' button in GDPR tab, which performs the below action.
  * Anonymize/Update contact's last name based on GDPR settings.
  * Delete contact's email/address/phone/IM/website.
  * Cancel all active memberships and update to 'GDPR Cancelled' status using staus override.
* Custom search 'Search Group Subscription by Date Range' which can be access from GDPR Dashboard.
* Access list of contacts who have not had any activity for a set period of days from GDPR Dashboard and perform action on the contacts.
* Sitewide Data Policy acceptance can be configured from within GDPR Settings.
* Event settings have a new tab to set Terms and Conditions which are added to the registration form.
* A Communications Preferences page at civicrm/gdpr/comms-prefs/update allows contacts to update their channels and group subscriptions. The settings for this can be reached from the GDPR Dashboard. There are tokens and an action link available to generate personalized links (with checksum) to the Communications Preferences page.

### Support ###

support (at) vedaconsulting.co.uk

~
~
