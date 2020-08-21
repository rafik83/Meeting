@event
@happening
@webinar
@meeting
@chat

Feature: I can chat

  Scenario: I can list a webinar chat messages
    Given the database is purged
    And the event "BestOfWeb" is created
    And the user "hello@example.net" is created
    And there is a sheet
    And there is a participant for this sheet and this user
    And I am logged with this user
    And there is a webinar in this event
    When I send a POST request to "http://super-event.vimeet.proximum/fr/chat/happening/1/add" with body:
      """
          {"content": "Hello, how are you today?"}
      """
    Then print the corresponding curl command
    And the JSON should be equal to:
      """
      {
          "status": "ok"
      }
      """
    When I send a GET request to "http://super-event.vimeet.proximum/fr/chat/happening/1/list"
    And the JSON should be equal to:
      """
      [
          {
              "id": 1,
              "content": "Hello, how are you today?",
              "createdAt": {
                  "date": "2020-03-16 10:35:00.000000",
                  "timezone_type": 3,
                  "timezone": "UTC"
              },
              "formattedCreatedAt": "11:35:00"
          }

      ]
      """
