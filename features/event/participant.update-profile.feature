@event
@participant
Feature: Update participant profile
  As a participant, I need to be able to update my profile

  Scenario: I can update the participant profile
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays-2016.vimeet.proximum"
    And there is a type "Fournisseur" in this event
    And there is a registration template

    And the user "user_asddays_1@proximum.com" is created
    And this user is called "John" "Doe"
    And this user is a man
    And there is a sheet for this type with the title "Aanera"
    And there is a participant for this sheet and this user
    And this participant is registered

    And I am logged with "user_asddays_1@proximum.com" on front

    When I go to this page "/fr"
    Then I should be on this page "/fr/sheet/1"
    And I should see "JD"
    And I should see "John DOE"
    Then I follow "JD"
    And I should be on this page "/fr/account/sheet/1/participant/1"
    Then I follow "common.update"
    And I should be on this page "/fr/account/sheet/1/participant/1/profile"
    And I should see "account.update.profile.title"
    Then I fill in the following:
      | Prénom | Yeb    |
      | Nom    | Yupont |
    And I check the "gender.man" radio
    And I attach the file "dummy-image-test.jpg" to "profile[dd66008e][file]"
    And I press "common.validate"
    Then I should be on this page "/fr/account/sheet/1/participant/1"
    And I should see "Yeb YUPONT"

  Scenario: I can update the participant avatar
    Given there is an event with domain "asddays-2016.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    And I go to this page "/fr/account/sheet/1/participant/1"
    Then I follow "account.profile.updateAvatar"
    And I should be on this page "/fr/account/sheet/1/participant/1/avatar/cb66008e"
    And I should see "account.update.avatar.title"
    And I attach the file "dummy-image-test.jpg" to "avatar[cb66008e][file]"
    And I press "common.validate"
    Then I should be on this page "/fr/account/sheet/1/participant/1"
    And I should not see "YY"

  Scenario: I can update the participant company
    Given there is an event with domain "asddays-2016.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I go to this page "/fr/account/sheet/1/company"
    Then I should see "account.update.company.title"
    And I fill in the following:
      | Nom (Société / Organisme)       | Elao                     |
      | Ville                           | Paris                    |
      | company[adc97e8d][item][first]  | turnover2                |
    And I attach the file "dummy-image-test.jpg" to "company[dd66008e][file]"
    And I press "common.validate"
    Then I should be on this page "/fr/sheet/1"
    And I go to this page "/fr/account/sheet/1/participant/1"
    And I should see "Elao"
    And I should see "Paris"
