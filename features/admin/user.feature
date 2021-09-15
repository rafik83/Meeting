@admin @user
Feature:
  I need to be able to see the users by events and their details information

  Scenario: I can see a list of users
    Given the database is purged
    And the event "ASD Days" is created
    And the user "user_asddays_1@proximum.com" is created
    And this user is called "John" "Doe"
    And there is a type "Fournisseur" in this event
    And this user is declared in this event
    And there is a sheet for this type with the title "Aanera"
    And there is a participant for this sheet and this user
    And the user "user_asddays_2@proximum.com" is created
    And this user is declared in this event
    And there is a sheet for this type with the title "Hello World Company"
    And there is a participant for this sheet and this user
    And the user "user_asddays_3@proximum.com" is created
    And this user is a woman
    And this user is called "Julie" "Martin"
    And this user position is "Community Manager"
    And there is a type "Investisseur" in this event
    And this user is declared in this event
    And there is a sheet for this type with the title "World Company Inc"
    And there is a participant for this sheet and this user
    And there is a type "Donneur d'ordre" in this event
    And the user "user_asddays_4@proximum.com" is created
    And this user is declared in this event
    And I am logged as admin
    And I am on this page "/fr/event"
    When I go to this page "/fr/event/1/users"
    Then I should see "user_asddays_1@proximum.com"
    And I should see "user_asddays_2@proximum.com"
    And I should see "user_asddays_3@proximum.com"

  Scenario: I can filter users by sheet type
    Given I am logged as admin
    And I am on this page "/fr/event"
    And I go to this page "/fr/event/1/users"
    And I should see "user_asddays_1@proximum.com"
    And I should see "user_asddays_2@proximum.com"
    And I should see "user_asddays_3@proximum.com"
    And I should not see "user_asddays_4@proximum.com"
    When I follow "Investisseur"
    Then I should see "user_asddays_3@proximum.com"
    And I should not see "user_asddays_1@proximum.com"
    And I should not see "user_asddays_2@proximum.com"
    And I should not see "user_asddays_4@proximum.com"
    When I follow "Fournisseur"
    Then I should see "user_asddays_1@proximum.com"
    And I should see "user_asddays_2@proximum.com"
    And I should not see "user_asddays_3@proximum.com"
    And I should not see "user_asddays_4@proximum.com"
    When I follow "Donneur d'ordre"
    Then I should see "admin.zero-result"

  Scenario: I can filter users by type and participation
    Given I am logged as admin
    And I am on this page "/fr/event"
    And I go to this page "/fr/event/1/users"
    And I should see "user_asddays_1@proximum.com"
    And I should see "user_asddays_2@proximum.com"
    And I should see "user_asddays_3@proximum.com"
    And I should not see "user_asddays_4@proximum.com"
    When I follow "Donneur d'ordre"
    And I follow "admin.users.withoutSheet"
    Then I should see "user_asddays_4@proximum.com"
    But I should not see "user_asddays_1@proximum.com"
    But I should not see "user_asddays_2@proximum.com"
    But I should not see "user_asddays_3@proximum.com"
    When I go to this page "/fr/event/1/users?participation=withoutSheet&type=3&text=user_asddays_2"
    Then I should see "admin.zero-result"

  Scenario: I can filter users by participation with sheet or without sheet
    Given I am logged as admin
    And I am on this page "/fr/event"
    And I go to this page "/fr/event/1/users"
    When I follow "admin.users.withSheet"
    Then I should see "form.user_filter.children.participation.label: admin.users.withSheet"
    And I should see "user_asddays_1@proximum.com"
    And I should see "user_asddays_2@proximum.com"
    And I should see "user_asddays_3@proximum.com"
    But I should not see "user_asddays_4@proximum.com"
    When I follow "admin.users.withoutSheet"
    Then I should see "form.user_filter.children.participation.label: admin.users.withoutSheet"
    And I should see "user_asddays_4@proximum.com"
    But I should not see "user_asddays_1@proximum.com"
    And I should not see "user_asddays_2@proximum.com"
    And I should not see "user_asddays_3@proximum.com"

  Scenario: I can search user by name or email
    Given I am logged as admin
    And I am on this page "/fr/event"
    When I go to this page "/fr/event/1/users?text=john&participation=withSheet"
    Then I should see "user_asddays_1@proximum.com"
    But I should not see "user_asddays_2@proximum.com"
    And I should not see "user_asddays_3@proximum.com"
    And I should not see "user_asddays_4@proximum.com"
    When I go to this page "/fr/event/1/users?text=asddays_2&participation=withSheet"
    Then I should see "user_asddays_2@proximum.com"
    But I should not see "user_asddays_1@proximum.com"
    And I should not see "user_asddays_3@proximum.com"
    And I should not see "user_asddays_4@proximum.com"

  Scenario: I can see details information from an user
    Given I am logged as admin
    And I am on this page "/fr/event"
    And I go to this page "/fr/event/1/users"
    And I follow "Investisseur"
    And I should see "user_asddays_3@proximum.com"
    When I follow "admin.users.details"
    Then I should see "gender.woman"
    And I should see "Julie Martin"
    And I should see "Community Manager"
    And I should see "user_asddays_3@proximum.com"
    And I should see "ASD Days" in the ".eventElement" element
    When I follow "ASD Days"
    Then I should be on this page "/fr/event/1/sheet/3"
