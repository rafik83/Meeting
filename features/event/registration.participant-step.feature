@event @participant @registration @mail
Feature: Register with participant step
  I need to be able to register and fill information during the registration

  Scenario: Register a user in 3 steps
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays.vimeet.proximum"

    And there is a registration template
    And there is a third step on this template

    And there is a type "Fournisseur" in this event

    And the user "user_asddays_1@proximum.com" is created
    And this user is called "John" "Doe"
    And this user is a man
    And there is a sheet for this type with the title "Aanera"
    And there is a participant for this sheet and this user
    And this participant is registered

    # package with product of type "Participant"
    And there is a package "Pack participants+rdv" for this event
    And there is a product Participant called "Participant" with a price of "130" and a max quantity of 10
    And this product participant is assigned to this package
    And this package is assigned to this type

    And I am logged with "user_asddays_1@proximum.com" on front

    When I go to this page "/fr/participant/1/step/3"
    Then I should see "Organisme"
    And I should see "register.step 3/3"
    And I fill in the following:
      | Décrivez votre activité |  |
    When I press "register.finalize"
    Then I should see "This value should not be blank."
    And I fill in the following:
      | Décrivez votre activité | Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description Ceci est une description |
    When I press "register.finalize"
    Then I should be on this page "/fr/participant/1/step/3"
    And I should see "This value is too long. It should have 300 characters or less."
    And I fill in the following:
      | Décrivez votre activité | Ceci est une description |
    When I press "register.finalize"
    Then the "event.preregistered" mail should be sent to "user_asddays_1@proximum.com" from "no-reply@asddays.vimeet.proximum"
    Then the "event.preregistered" mail should be sent in bcc to "team-project@example.net" from "no-reply@asddays.vimeet.proximum"
    And I should be on this page "/fr/sheet/1"
    And I should see "sheet.welcome.title"
    And I should see "sheet.welcome.sheetContent"
    And I should see "sheet.welcome.packageContent"
