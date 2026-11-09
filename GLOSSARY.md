# Greeting

need2lift answers one kind of request with a fixed text, and answers everything else with a short failure text. These names are the ones the migration uses for that behaviour.

## Language

**Hello request**:
A request for the greeting.
_Avoid_: API call, hit

**Greeting**:
The successful reply to a hello request.
_Avoid_: message, payload, view, HelloController, helloAction

**Unknown request**:
A request that does not ask for the greeting.
_Avoid_: 404, missing route

**Dispatch failure**:
A request the application cannot complete, other than an unknown request.
_Avoid_: exception, error page
