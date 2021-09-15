@event @agenda @catalog @tip

  Feature: Tip message on front
    As a participant I can see tip message when they are set

  Scenario: I can see tip message on catalog and meeting request management
    Given the database is purged
    And the event "Best of web" is created
    And there is a type in this event
    And there is a sheet for this type with the title "SheetA"
    And elastica is populate
    And the user "user@example.net" is created
    And there is a participant for this sheet and this user
    And the catalog is open since "2016-10-10 10:00:00"
    And there is a rule for this type and this event
    And this sheet is in catalog
    And the tip "Awesome tip" is created for the type of this sheet
    And the "fr" title translation of this tip is "Awesome tip"
    And this tip is affected on the catalog
    And this tip is affected on the meeting request management
    And I am logged with "user@example.net" on event "http://super-event.vimeet.proximum"
    And I go to this page "/fr/catalog"
    Then I should see "Awesome tip"
    And I go to this page "/fr/sheet/1/meeting/request"
    Then I should see "Awesome tip"
