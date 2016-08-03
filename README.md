# FranceConnect-Symfony
[![Latest Stable Version](https://poser.pugx.org/kleegroup/franceconnect-bundle/v/stable?format=flat-square)](https://packagist.org/packages/kleegroup/franceconnect-bundle) [![Total Downloads](https://poser.pugx.org/kleegroup/franceconnect-bundle/downloads?format=flat-square)](https://packagist.org/packages/kleegroup/franceconnect-bundle) [![License](https://poser.pugx.org/kleegroup/franceconnect-bundle/license?format=flat-square)](https://packagist.org/packages/kleegroup/franceconnect-bundle) 
## Synopsis

Symfony 3 Bundle for FranceConnect authentication.

## Dependencies

* [namshi/jose](https://github.com/namshi/jose)

## Installation

All the installation instructions are located in [documentation](Resources/doc/index.md).

## Usage

1. Add a link to the route " france_connect_login " in your template twig

    ```html
         <a href="{{ path('france_connect_login') }}" class="btnLink">
            <img src="{{ asset('bundles/franceconnect/images/FCboutons-10.png') }}"
                alt="FranceConnect button"/>
        </a>
    ```
2. Add a controller that will handle the json

    ```php
       /**
        * @param Request $request
        * @Route("/france-connect-traitement", name="app.fc.return")
        */
       public function franceConnectAction(Request $request)
       {
           $json = json_decode(urldecode($request->query->get('json')),true);
           $identity = new FCIdentity();
           $identity->hydrate($json);
           return $this->render("default/authenticated.html.twig", ['identity' => $identity]);
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