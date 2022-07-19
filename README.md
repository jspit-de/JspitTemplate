# JspitTemplate - Smart PHP Template Class

JspitTemplate is a small and fast template system for PHP.

## Features
- pure HTML templates 
- integrated escaping
- Filters for formatting, escaping, user defined 
- small, only one file with 15kByte
- Independent (Linux,Window, PHP >= V7.0)

## How to use it

Easy, just download and include the class file and and make outputs wherever you want.

### Simple example

index.tpl.html
```html
<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="UTF-8" />
    <title>{{ title ?? 'Demo JspitTemplate'}}</title>
  </head>
  <body>
    <h3>{{ h3 }}</h3>
    {{content}}
  </body>
</html>
```

demo_jspittemplate_simple.php
```php
require __DIR__.'/JspitTemplate.php';

header('Content-Type: text/html; charset=UTF-8');

echo JspitTemplate::create(__DIR__.'/index.tpl.html')
  ->assign([
      'h3' => 'JspitTemplate Simple Test',
      'content' => '<Hallo World>',
   ])
;
```

HTML:
```html
<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="UTF-8" />
    <title>Demo JspitTemplate</title>
  </head>
  <body>
    <h3>JspitTemplate Simple Test</h3>
    &lt;Hallo World&gt;
  </body>
</html>
```

## Placeholder

A simple placeholder is just a name within two curly brackets.

```html
{{ h3 }}
```
The placeholders are replaced by application variables using the assign method with an array as a parameter. The array consists of one or more elements of the form 'placeholder' => 'value'.

```php
  ->assign([
     'h3' => 'JspitTemplate Simple Test',
   ])
```

Placeholder may have attributes or elements. 
Use a dot (.) to access attributes of a variable (items of a PHP array or properties of a PHP object).

```html
{{ foo.bar }}
```

Behind ?? optional default values can be used. A default value must be a fixed string. This string is always escaped.

```html
{{ foo.bar ?? 'default' }}
```

Filters can be placed behind the placeholders. Filters are marked by a | .

```html
{{ foo.bar|filter ?? 'default' }}
```

## Filters

Placeholder can be modified by filters. Filters are separated from the placeholder by a pipe symbol ( | ). Multiple filters can be chained. The output of one filter is applied to the next.

### raw

The raw filter means that this variable is not masked. Without a raw filter, the variables are masked with htmlspecialchars().
If the variable contains html that should be interpreted by the browser, the filter html (alias for raw) or raw must be used.

```html
{{ htmlcontent|raw }}
```

### url

The url filter encodes a given string as URL segment with rawurlencode() or an array as query string with http_build_query().

```html
{{ par|url }}
```

### format

The format filter returns the assigned variable like sprintf()/vsprintf().

```html
{{ var|format('%03d') }}
```

### date

Formats a string, an object from the DateTimeInterface or an integer time stamp to a date.

```html
{{date|date(" d.m.Y")}}
```

### case

The filter case selects a string that corresponds to the value converted to an integer. The individual strings are separated by the first character in the chain.

```html
{{ var|case('#zero#one#two#') }}
```

If the value 1 is assigned to var in the example, the expression 'one' results. A empty string is the result if there is no corresponding case.

### set

If var is set and assigned and not NULL, the value is replaced with the fixed string used as the parameter for set.

```html
{{var|set('var is set') ?? 'var not set'}}
```

### blank

The filter blank does the same as set("").

### selected

selected is a special filter for forms. It enables the selected property to be set to restore the selection after the form has been sent. The standard value must always be set here.

```html
{{post.sel|selected('p2') ?? ' '}}
```

The result is "selected" if the element "sel" from the array post is equal to "p2". For all other cases the result is an empty string. The standard value must always be set here.

### checked

checked is a special filter for checkboxes in forms. 

```html
{{post.chk|checked ?? ''}}
```

The result is "checked" if the "chk" element is present in the post array. In all other cases the result is an empty string.

Custom filters can be added using the addStaticUserFunction() and addUserFunction() methods. Filters that are not defined always return the unchanged value and do not cause any errors.

### each

Simple loops can be implemented with the filter 'each'.

```html
<ul>
  {{htmlList|each("<li>#val#</li>")|raw}}
</ul>
```

htmlList must be assigned an array. The placeholder #val# is replaced with the value and #key' with the key from the array.

```php
$html = JspitTemplate::createFromString($template)
  ->render(['htmlList' => ['Coffee','Tea','Milk']])
;
```

HTML:
```html
<ul>
<li>Coffee</li>
<li>Tea</li>
<li>Milk</li>
</ul>
```



## Doc
http://jspit.de/tools/classdoc.php?class=JspitTemplate 

## Demo and Test
http://jspit.de/check/phpcheck.jspittemplate.php


