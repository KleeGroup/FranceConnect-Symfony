FranceConnect-Symfony
=====================================

[![Latest Stable Version](https://poser.pugx.org/kleegroup/franceconnect-bundle/v/stable?format=flat-square)](https://packagist.org/packages/kleegroup/franceconnect-bundle) [![Total Downloads](https://poser.pugx.org/kleegroup/franceconnect-bundle/downloads?format=flat-square)](https://packagist.org/packages/kleegroup/franceconnect-bundle) [![License](https://poser.pugx.org/kleegroup/franceconnect-bundle/license?format=flat-square)](https://packagist.org/packages/kleegroup/franceconnect-bundle) 
# Synopsis

Symfony 3 Bundle for FranceConnect authentication.

# Dependencies

* [namshi/jose](https://github.com/namshi/jose): Utilisé pour la vérification du JWT
* [Mashape/unirest-php](https://github.com/Mashape/unirest-php) utilisé pour les appels REST

# Installation

All the installation instructions are located in [documentation](Resources/doc/).
The installation is in two steps:
* [Add FranceConnectBundle to composer.json](Resources/doc/installation.md)
* [Configure FranceConnectBundle](Resources/doc/configuration.md) 

# Usage

1. Add a link to the route " france_connect_login " in your template twig

    ```html
         <a href="{{ path('france_connect_login') }}" class="btnLink">
            <img src="{{ asset('bundles/franceconnect/images/FCboutons-10.png') }}"
                alt="FranceConnect button"/>
        </a>
    ```
2. Add a controller that will handle the response

    ```php
       /**
        * @param Request $request
        * @Route("/france-connect-traitement", name="app.fc.return")
        * @Security("is_granted('IS_AUTHENTICATED_FRANCE_CONNECT')")
        */
       public function franceConnectAction(Request $request)
       {
           $token = $this->get('security.token_storage')->getToken();
           $token->getIdentity(); // json array provided by FranceConnect 
           [...]
       }
    ```

3. Add FranceConnect script in your template
    ```html
        {% block javascripts %}
            <script src="http://fcp.integ01.dev-franceconnect.fr/js/franceconnect.js"></script>
        {% endblock %}
    ```
        
4. Add FranceConnect block in your template
    ```html
        <div id="fconnect-profile" data-fc-logout-url="{{ url('france_connect_logout') }}">
            <a href="#">
                {{- identity.givenName ~ ' ' ~ identity.familyName|upper -}}
            </a>
        </div>
    ```

## License

This bundle is under the MIT license. 