Feature: Patch user in database
  In order to use the application
  I need to be able to patch an existing user using the PATCH method

  Background: Reset user table before each scenario
    Given the user table is empty

  Scenario: Patch a user with valid UUID and informations
    # Patch the user
    Given I load a predictable user in database and get it's id
    And I save it into "UserID"
    When I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    And I send a "PATCH" request to "/admin/api/users/<<UserID>>" with body:
    """
    {
      "email": "user_patch_test@test.com",
      "plain_password": "blablabla"
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/json"
    And the JSON node "status" should be equal to "success"
    And the JSON node "code" should be equal to "200"
    And the JSON node "request-method" should be equal to "PATCH"
    And the JSON node "request-ressource-id" should be equal to "<<UserID>>"
    # Check user has been patched correctly
    When I add "Accept" header equal to "application/json"
    And I send a "GET" request to "/admin/api/users/<<UserID>>"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/json"
    And the JSON node "status" should be equal to "success"
    And the JSON node "code" should be equal to "200"
    And the JSON node "request-method" should be equal to "GET"
    And the JSON node "results-for" should be equal to "<<UserID>>"
    And the JSON node "results.user.email" should be equal to "user_patch_test@test.com"

  Scenario: Get 404 error on invalid UUID format
    When I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    And I send a "PATCH" request to "/admin/api/users/abcd"
    Then the response status code should be 404
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/problem+json"
    And the JSON node "status" should be equal to "error"
    And the JSON node "code" should be equal to "404"
    And the JSON node "type" should be equal to "Not Found"
    And the JSON node "reason" should be equal to "Invalid id, format must be uuid"

  Scenario: Get 404 error on not existing UUID
    When I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    And I send a "PATCH" request to "/admin/api/users/e59c4d7a-89aa-41d0-bc00-da6eee90d78c"
    Then the response status code should be 404
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/problem+json"
    And the JSON node "status" should be equal to "error"
    And the JSON node "code" should be equal to "404"
    And the JSON node "type" should be equal to "Not Found"
    And the JSON node "reason" should be equal to "The resource you have requested can't be found"

  Scenario: Get 400 error on bad content type
    When I add "Content-Type" header equal to "text/plain"
    And I add "Accept" header equal to "application/json"
    And I send a "PATCH" request to "/admin/api/users/e59c4d7a-89aa-41d0-bc00-da6eee90d78c"
    Then the response status code should be 400
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/problem+json"
    And the JSON node "status" should be equal to "error"
    And the JSON node "code" should be equal to "400"
    And the JSON node "type" should be equal to "Bad Request"
    And the JSON node "reason" should be equal to "Invalid content type, please send application/json content"

  Scenario: Patch a user with valid UUID but invalid informations
    Given I load a predictable user in database and get it's id
    And I save it into "UserID"
    When I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    And I send a "PATCH" request to "/admin/api/users/<<UserID>>" with body:
    """
    {
      "email": "user_patch_test"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/problem+json"
    And the JSON node "status" should be equal to "error"
    And the JSON node "code" should be equal to "400"
    And the JSON node "type" should be equal to "Bad Request"
    And the JSON node "reason" should be equal to "Error while validating ressource insertion/update"
    And the JSON should be valid according to the schema "features/references/POST_SCHEMA_users_error.json"