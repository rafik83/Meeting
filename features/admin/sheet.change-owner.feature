@admin @sheet

Feature: Sheet change owner
  As an admin, I can change the owner of a sheet

  Scenario: I can change the owner of a sheet
    Given the database is purged
    And the event "Forum PHP" is created
    And there is a sheet with the title "Proximum"
    And there is a participant for this sheet
    And I am logged as admin
    When I go to this page "/fr/event/1/sheet/1"
    Then I should see "Proximum"
    And I should see "admin.sheet.details.owner_not_participant.title"
    And I should see "admin.sheet.changeOwner.link"
    When I follow "admin.sheet.changeOwner.link"
    Then I should be on "/fr/event/1/sheet/1/change/owner"
    And I select "0" from "change_owner[owner]"
    And I press "form.change_owner.children.submit.label"
    Then I should be on this page "/fr/event/1/sheet/1"
    And I should see "flash.sheet.change_owner.success"
    And I should not see "admin.sheet.details.owner_not_participant.title"
    And I should see "admin.sheet.details.owner.title"
