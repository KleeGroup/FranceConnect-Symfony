FranceConnectBundle Configuration Reference
=====================================

All available configuration options are listed below with their default values.

``` yaml
# app/config/config.yml
france_connect:
    # Identifiers supplied by FranceConnect.
    client_id: '11111'
    client_secret: '111111'
    
    # FranceConnect base URL
    provider_base_url: 'https://fcp.integ01.dev-franceconnect.fr/api/v1/'
    
    # Callback URL provided to FranceConnect
    callback_url: 'http://127.0.0.1:8000/france-connect/callback'
    
    # Logout URL
    post_logout_redirect_uri : 'http://127.0.0.1:8000/home'
    
    # Data to recover
    # this parameter is optional
    scopes:
        - 'openid'
        - 'profile'
        
    # The route name on which the treatment of json be made.
    result_route: 'app.retour_fc'
```
