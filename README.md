C33sSimpleContentBundle
===================

Propel-driven content blocks right from your Twig templates. This is intended to be used with small to medium-sized websites,
where the original content is hard-coded in templates instead of fixtures for faster development.

Just wrap your HTML, text or Markdown content in some twig filters and you are ready to go! 

For now this bundle depends on [`cedriclombardot/admingenerator-generator-bundle`](https://packagist.org/packages/cedriclombardot/admingenerator-generator-bundle) for content editing.
I'm planning to separate this in the future.

*THIS IS WORK IN PROGRESS! USE AT YOUR OWN RISK!*

Installation
-------------

Require [`c33s/simple-content-bundle`](https://packagist.org/packages/c33s/simple-content-bundle) in your `composer.json` file:

```js
{
    "require": {
        "c33s/simple-content-bundle": "0.11.*",
    }
}
```

Register the bundle in `app/AppKernel.php`:

```php

    // app/AppKernel.php

    public function registerBundles()
    {
        return array(
            // ...

            new C33s\SimpleContentBundle\C33sSimpleContentBundle(),
        );
    }

```

Override options in your config.yml:

```yml

# app/config/config.yml

c33s_simple_content:
    # Set to true for automatic locale fallback
    use_locale_fallback:        false

```

Make sure the %locales% parameter is defined:

```yml

# app/config/parameters.yml

parameters:
    locales: ['en', 'de']

```

Usage
-----

Use the twig filters delivered with this bundle to automatically add content from your templates.

``` jinja+html
{# Just wrap your text inside the filter #}
<h1>{% filter c33s_content_line('home.title') %}Welcome to my website{% endfilter %}</h1>

{# You can force a specific locale #}
<h1>
    {% filter c33s_content_line('home.title', 'en') %}Welcome to my website{% endfilter %}
    {% filter c33s_content_line('home.title', 'de') %}Willkommen auf meiner Webseite{% endfilter %}
</h1>

{% filter c33s_content_markdown('home.welcome.text1') %}
This text will be _rendered_ using markdown.

Isn't it great?
{% endfilter %}

{% filter c33s_content_text('home.welcome.text2') %}
This text will only convert nl2br, but nothing else.

<strong>These tags won't have any effect</strong>
{% endfilter %}

{% filter c33s_content_html('home.welcome.text3') %}
Raw HTML content is possible, too.

<strong>This is strong.</strong>
{% endfilter %}

{# There is also a filter where the content type can be passed as an argument #}
{% filter c33s_content('home.title', 'line') %}Welcome to my website{% endfilter %}

```
