Feature:
    Given I have an api
    As a developer
    I want to test it

    Scenario: 200 user response, authentic ira
        Given I set the following headers:
            | content-type | application/json |
            | accept       | en               |
        When I make a GET request to the "User" endpoint
        Then I expect a 200 "User" response
        And the response should match the snapshot
        And I expect the following content in the JSON response 'address.jug' key:
            """
            "15"
            """

    Scenario: 500 exception response
        When I set the placeholder "message" to "message+is+bad"
        And I make a GET request to the "User" endpoint with query string "exception=1&error={{message}}&errorCode=503"
        Then the response should match the snapshot
        And I expect a 500 "User" response

    Scenario: 201 Get request'si in spade
        When I make a GET request to the "User" endpoint with query string "test=true"
        Then the response should match the snapshot
        And I expect a 201 "User" response

    Scenario: 200 POST request to create user
        When I set the placeholder "postcode" to "B23 7QQ"
        And I make a POST request to the "User" endpoint with body:
            """
            {"name": "Wahab Qureshi", "postcode": "{{postcode}}"}
            """
        Then I expect a 200 "User" response
        And the response should match the snapshot

    Scenario: Check empty response to work
        Given I use version "1" of the API
        When I make a GET request to the "User" endpoint with query string "empty=true"
        Then the response should be empty
        