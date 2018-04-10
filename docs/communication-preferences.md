# Communication Prefrences

## Consent {:#Consent}
One of the biggest talking points around GDPR has been the tightening up of the definition of consent. This now reads:

> any freely given, specific, informed and unambiguous indication of the Data Subject's wishes by which he or she, by a statement or a clear affirmative action, signifies agreement.....

GDPR also says that in any statement the request for consent must be

> presented in a manner which is clearly distinguishable from other matters....using clear and plain language

So no automatic opting in during competitions or incentives and no longer implicitly gaining consent. Consent must now be granular.

Another clarficiation is any processing which is based on consent must be backed up by the ability of the processor to prove you have consent. There is no guidance yet on what this means, but as a minimum one might suppose that you would need to keep a record of when and how (e.g. by ticking a box) people consented and what the have consented to (ideally by reference to the full statement on the data capture form, for example).

## Communication Preferences configuration {:#Configuration}

In order to help organisations adhere to the regulations, the CiviCRM GDPR extension provides a combined communication page.

The following features are provided by the extension

* Configurable Communication Preference Page
* Activity generated when a user submits
* Combine groups and opt out into a single form
* Data Policy agreement tick box and Activity

In order to configure the communication preferences settings carry out the following;

1. **Contacts > GDPR Dashboard**
1. **Click the communication preferences link**

### General {:##Configuration}

Once clicked, you'll be presented with the communications preferences configuration page.

![Communication Preferences Main](/images/communication-preferences-main)

* **Page title** The page title and form title to be displayed when a supporter visits the communication preferences page
* **Introduction** Text to be displayed at the top of the communication preferences page
* **Include Profile** Data to be verified when the supporter is presented with the communication preferences page.

For example, the following settings

![Communication Preferences Main](/images/communication-preferences-main-example)

Would result in the Communications Preferences page displaying as follows

![Supporter Communication Preferences](/images/communication-preferences-page-example-1)

### Communicatin Channels

### Group Subscription

### Thank You Page



1. **Click the communication preferences link**
1. Find, download, and install *both* of these extensions:
    * **CiviVolunteer**
    * **Angular Profiles**

        !!! caution ""
            CiviVolunteer requires this additional extension, Angular Profiles, in order to function properly.

1. If necessary, click Enable after the extension has been downloaded and installed. When the extension is enabled:

    * CiviVolunteer should show up as green-highlighted.
    * The option to **Disable** it will be present.


## Discovering features after installing {:#discovery}

After installing, CiviVolunteer's features can be found in the following places:

* A 'Volunteers' menu item
* A 'Volunteers' tab within the configuration for each event
* A 'Volunteer Report' report template


## Permissions {:#permissions}

CiviVolunteer adds a handful of new permissions which should be configured within your CMS before using.

## Removing

If you no longer wish to use CiviVolunteer, you may disable it, or uninstall it.

* **Disable** - will turn off CiviVolunteer's features, but preserve any data that you have created with it. If you re-enable CiviVolunteer later, you'll be back where you left off. 
* **Uninstall** - can be done after disabling, and will completely remove all traces of CiviVolunteer, including the data created with it. If you re-install CiviVolunteer later, you'll be back to square one, before you ever installed it.

