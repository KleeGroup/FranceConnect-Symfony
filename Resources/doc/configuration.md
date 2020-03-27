FranceConnectBundle Configuration Reference
=====================================

# Liste des paramètres

All available configuration options are listed below with their default values.

``` yaml
france_connect:
    #Ids
    client_id: 'my_client_id'
    client_secret: 'my_client_secret'
    #FranceConnect base URL
    provider_base_url: 'https://fcp.integ01.dev-franceconnect.fr/api/v1/'
    #route name for logout
    logout_value: 'app_default_default'
    result_value: 'app_default_franceconnect'
    proxy_host: '192.20.12.5'
    proxy_port: 3128
    # data
    scopes:
        - 'openid'
        - 'profile'
    providers_keys:
        - 'main'
        - 'secured_area'
    
```

# Configuration globale
* *client_id*: identifier provided by FranceConnect
* *client_secret*: secret identifier provided by FranceConnect
* *provider_base_url*: FranceConnect API URL. <strong>Must be change in production environment </b>
* *logout_value*: Route for redirecting user after logout
* *result_value*: Route for redirecting user after login on FranceConnect
* *scopes*: scopes  requested from FranceConnect. (Cf [documentation FranceConnect](https://franceconnect.gouv.fr/fournisseur-service#identite-pivot))
* *providers_keys* list of firewalls names. The token will be injected on these firewall


# Configuration du proxy
``` yaml
france_connect:
    [...]
    proxy_host: '192.20.12.5'
    proxy_port: 3128
    [...]
```
Parameters *proxy_port* et *proxy_host* are optionals.
If the proxy is set, it will be use for API calls.

