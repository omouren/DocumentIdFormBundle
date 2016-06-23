# DocumentIdFormBundle
Provides the form type "document_id"
Based on the work of [Gregwar : FormBundle](https://github.com/Gregwar/FormBundle)

Installation
============

`composer require omouren/document-id-form-bundle`.

Register the bundle in the application kernel :

```php
<?php
// app/AppKernel.php
//...
public function registerBundles()
{
    $bundles = array(
        ...
        new Omouren\DocumentIdFormBundle\OmourenDocumentIdFormBundle(),
        ...
    );
...
```

Add the following after the twig block to the configuration :

    # app/config/config.yml
    # Twig Configuration
    twig:
        ...
        form_themes:
            - 'OmourenDocumentIdFormBundle::document_id_type.html.twig'

Usage
=====

The document_id is a field that contains an document id, this assumes you set up javascripts or any UI logics to fill it programmatically.

The usage look like the document field type one, except that the query returns one unique result. One example :

```php
<?php
//...
$builder
    ->add('city', 'document_id', [
        'class' => 'Project\Entity\City',
        'property' => 'id',
    ])
    ;
```

Here `->findOneBy(['id' => $value])` will be used.

You can also chose to show the field, by passing the `hidden` option to `false`:

```php
<?php
//...
$builder
    ->add('city', 'document_id', array(
        'class' => 'Project\Entity\City',
        'property' => 'id',
        'hidden' => false,
        'label' => 'Enter the City id'
    ))
    ;
```

Using the `property` option, you can also use another identifier than the primary key:

```php
<?php
//...
$builder
    ->add('recipient', 'document_id', array(
        'class' => 'Project\Entity\User',
        'hidden' => false,
        'property' => 'login',
        'label' => 'Recipient login'
    ))
    ;
```
