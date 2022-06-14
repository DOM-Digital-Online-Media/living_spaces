## How to code:
`npm install && npm run sass && npm run watch`

In this mode dev is able to code scss and changes will automatically be compiled.
Drupal cache would still need to be cleared.

## How to build:
`npm install && npm run sass`

### Note:
`npm run sass` command also prepares bootstrap js from node modules which may not exist in production to put it into js folder, so a recommended execution for watching has been written that way.
