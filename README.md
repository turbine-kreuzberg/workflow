# Turbine Workflow
[![coverage report](https://git.turbinekreuzberg.io/turbine/workflow/badges/main/coverage.svg)](https://git.turbinekreuzberg.io/turbine/workflow/-/commits/main)

These tools help automate the development workflow.

## How to use in your project
* add to your composer.json:
  ```
  "repositories": [  
   {
        "type": "git",
        "url": "https://git.turbinekreuzberg.io/turbine/workflow.git"
   }
  ]
  
* run `composer require turbine/workflow --dev`
* include in your makefile `include vendor/turbine/workflow/src/makefiles/Makefile`
* add to your .env 
```
JIRA_USERNAME=maxmusterman
JIRA_PASSWORD=password
JIRA_FAVOURITE_TICKETS=TXB-2,TXB-9
#Add your GitLab Personal Access Token to enable workflow automations
#Create your token with API scope here: https://git.turbinekreuzberg.io/-/profile/personal_access_tokens
#The scope of api should be checked
GITLAB_PERSONAL_ACCESS_TOKEN=access-token
```
* run `make workflow-setup`

* run `make book-time`

## Library development

To setup run
`make setup`

to see other available commands run
`make`
