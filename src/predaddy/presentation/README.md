Paginator components
--------------------

It is a common thing to use paginated results on the user interface. The `presentation` package provides you some useful
classes and interfaces. You can integrate them into your MVC framework to be able to feed them with data coming from the
request. This `Pageable` object can be used in your repository or in any other data provider object as a parameter which can return a `Page` object.
A short example:

```php
$page = 2;
$size = 10;
$sort = Sort::create('name', Direction::$DESC);
$request = new PageRequest($page, $size, $sort);

/* @var $page Page */
$page = $userFinder->getPage($request);
```

In the above example the `$page` object stores the users and a lot of information to be able to create a paginator on the UI.
