# JspitTemplate - Smart PHP Template Class

JspitTemplate is a small and fast template system for PHP.

## Features
- pure HTML templates 
- integrated escaping
- Filters for formatting, escaping, user defined 
- small, only one file with 12kByte
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


## Doc
http://jspit.de/tools/classdoc.php?class=JspitTemplate 

## Demo and Test
http://jspit.de/check/phpcheck.jspittemplate.php


