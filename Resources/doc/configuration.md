FranceConnectBundle Configuration Reference
=====================================

All available configuration options are listed below with their default values.

``` yaml
france_connect:
    #Ids
    client_id: 'b8a8fdf9fe4e6f469086e825c21aed3116b9cc3eafe90a4c553678c92bdc9835'
    client_secret: 'f2fa587128d3fa75167a79327bdd4ebaf5db6b60aeadd8ea173631879697100b'
    #FranceConnect base URL
    provider_base_url: 'https://fcp.integ01.dev-franceconnect.fr/api/v1/'
    #route name for logout
    post_logout_route: 'app_default_default'
    result_route: 'app_default_franceconnect'
    proxy_host: '172.20.0.9:3128'
    # data
    scopes:
        - 'openid'
        - 'profile'
    
```
