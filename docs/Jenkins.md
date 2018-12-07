# Jenkins

For Jenkins callback, there are opened routes in `app/config/routing.yml`:

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

And the Symfony firewall let Jenkins call these routes in `app/config/security.yml`:

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
