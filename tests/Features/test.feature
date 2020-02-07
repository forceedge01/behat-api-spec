Feature:
    Given I have an api
    As a developer
    I want to test it

    Scenario:
        Given I set the following headers:
            | content-type | application/json |
            | accept       | en               |
        When I make a POST request to "User" endpoint with body:
            """

            """
        Then I expect a 200 "User" response expecting:
            """
            {
                "success": true,
                "data": [{"name": "Abdul Wahhab Qureshi"}]
            }
            """
        And an undefined step
