# Turbine Workflow

These tools help automate the development workflow.

## How to use in your project
* add to your composer.json:
  ```
  "repositories": [  
   {
        "type": "git",
        "url": "https://git.votum-media.net/turbine/workflow.git"
   }
  ]
  
* run `composer require turbine/workflow`
* add to your Makefile 
  ```
  -include vendor/turbine/workflow/src/makefiles/Makefile
* run `make book-time`

## Library development

To setup run
`make setup`

to see other available commands run
`make`
