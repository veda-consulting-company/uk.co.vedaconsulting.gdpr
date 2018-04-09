# GDPR

## Overview

The [GDPR extension](https://github.com/veda-consulting/uk.co.vedaconsulting.gdpr “GDPR extension") aims to enable charities/organisations to manage their supporters in a GDPR compliant manner. GDPR in itself does not introduce many new requirements however it does introduce a number of new obligations on organisations that hold and use data about individuals.

### Features

* Allow you to record the data protection officer for your organisation* A new tab 'GDPR' in contact summary will display group subscription log for the contact* Custom search 'Search Group Subscription by Date Range' which can be access fromGDPR Dashboard* Access list of contacts who have not had any activity for a set period of days from GDPRDashboard* The ability to carry out an action on those contacts who have not had any activity* Ability to force acceptance of data policy/terms and conditions when a contact logs inand recording this as an activity against the contact with a copy of the terms andconditions agreed to. This is currently Drupal specific.* The right to be forgotten, allowing users of CivicRM to easily anonymise a contactrecord, hiding any person details but keeping the financial and other history. The actionalso exists as an API and therefore can be bolted into other processes.* User friendly communication preferences, moving to explicitly worded opt inmechanisms.* Communication preference to include medium per group. Currently CiviCRM supportsinclude or exclude from a group but it does not allow for the selection of the communication medium that should be used for example happy to receive email newsletters but please don’t send me any other emails.* Inclusion of two new tokens which automatically include checksum and link to the communication preferences page* Include a terms and conditions acceptance for events if configured* Ability to include profile fields on the communication preferences page, allowing users toensure other information, such as the name and phone number, for the contact is also valid



## Other resources

* [GitHub repository](https://github.com/veda-consulting/uk.co.vedaconsulting.gdpr.git)
* [Release downloads](https://civicrm.org/extensions/gdpr) (within CiviCRM.org's extensions directory)
* TO DO[Issue tracking](https://issues.civicrm.org/jira/browse/VOL) (in a Jira project)
* TO DO [Q&A on StackExchange](http://civicrm.stackexchange.com/questions/tagged/civivolunteer) (with the `civivolunteer` tag)

## Requirements

* TO DO CiviCRM 4.4 or higher
* The [Angular Profiles](https://civicrm.org/extensions/angular-profile-utilities) extension must also be installed and enabled

## Known Issues

* TO DO Before 4.7.21, extension permissions did not work properly in Joomla (see [CRM-12059](https://issues.civicrm.org/jira/browse/CRM-12059)). CiviCRM would recognize extension-defined permissions but not give site administrators any way to grant them to users.

## Future plans

* Recording audit information when a contact is exported* Allowing all exports to be produced with passwords if produced with the MS ExcelExtension.* Include terms and conditions acceptance during membership sign up* Ensure Scheduled reminders have a setting to exclude those contacts who have no bulkemails set if the scheduled reminder is deemed to be marketing oriented as a posed totransactional.* Allow communications preference options to be controlled by the groups the contactbelongs to, this will allow members to view more groups than non members as anexample* Include a more prominent blockWe'd also like to take this opportunity to thank Paul Ticher ( http://www.paulticher.com/data-protection ) for coming on board with this project as a consultant
