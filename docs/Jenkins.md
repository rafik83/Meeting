# Jenkins

- [Overview](#overview)
- [Job](#job)
- [Create a job build in Jenkins](#Create-a-job-build-in-Jenkins)
- [Callback](#callback)

## Overview

- Vimeet ➡️ [Create a build in Jenkins](#Create-a-job-build-in-Jenkins) 
- ➡️ Jenkins run the job
- ➡️ At the end of the job, Jenkins post the result to Vimeet via the [callback url](#callback): [admin url]/planner/callback
    
## Job

Jobs are saved in this Github repository: [proximum/jenkins-config](https://github.com/proximum/jenkins-config/tree/master/jobs).
We do not use the repository to add new job but the Jenkins UI available at http://10.11.0.95:8080/ (see the 1password for credentials).

https://github.com/proximum/jenkins-config/blob/master/jobs/OptaPlanner_PROD_run/config.xml

## Create a job build in Jenkins

Production (front1 and front2) and preproduction environments share the same instance of Jenkins.
It is not obvious but the URI `http://optaplanner:8080` is not OptaPlanner, but Jenkins.

For calling Jenkins, we need these credentials:

    # app/config/parameters.yml
    jenkins_user: *****
    jenkins_password: ****
    
See the 1password for credentials.
There are two jobs for depending on the environment: `OptaPlanner_PROD_run` or `OptaPlanner_PREPROD_run`.

For calling OptaPlanner Jenkins job:
    
    # app/config/parameters.yml
    planner_files_path: /var/www/proximum-vimeet.project.local/htdocs/shared/planner
    planner_trusted_name: OptaPlanner_PROD_run
    planner_command: 'curl -v -X POST http://optaplanner:8080/job/%%planner_trusted_name%%/build --user %%jenkinsUser%%:%%jenkinsPassword%% --data-urlencode json=''{"parameter": [{"name":"INPUT", "value":"%%filename%%"}]}'''

For calling Batch Sheet printing Jenkins job:

    # app/config/parameters.yml    
    jenkins_phantomjs_sheet_pdf_print_build_name: vimeet_print_pdf_PROD
    jenkins_command: 'curl -v -X POST http://optaplanner:8080/job/%%buildName%%/build --user %%jenkinsUser%%:%%jenkinsPassword%% --data-urlencode json=''{"parameter": %%jenkinsParameters%%}'''

## Callback

For Jenkins callback, there are opened routes:

    # app/config/routing.yml
    planner_callback:
        path: /planner/callback
        host: '%admin_domain%'
        defaults:
            _controller: AdminBundle:Planner\Callback:index
        methods: [POST]
    
    jenkins_callback_sheet_print_pdf:
        path: /jenkins/callback/phantomjs/sheet/print-pdf
        host: '%admin_domain%'
        defaults:
            _controller: AdminBundle:ThirdParty\Jenkins\Callback:printSheetBuildCallback
        methods: [POST]

And the Symfony firewall let Jenkins call these routes:

    # app/config/security.yml
    firewalls:
        planner:
            pattern: '^/planner/callback'
            host: '%admin_domain%'
            security: false
        jenkins:
            pattern: '^/jenkins/callback'
            host: '%admin_domain%'
            security: false

A sample of the payload received from Jenkins is described in this class:
[src/Application/ThirdParty/Jenkins/AbstractSetStatus.php](../src/Application/ThirdParty/Jenkins/AbstractSetStatus.php)
