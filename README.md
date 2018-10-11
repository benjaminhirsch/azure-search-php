# Microsoft Azure Search Service for php
[![Latest Stable Version](https://poser.pugx.org/benjaminhirsch/php-azure-search/v/stable)](https://packagist.org/packages/benjaminhirsch/php-azure-search)
[![Build Status](https://travis-ci.com/benjaminhirsch/azure-search-php.svg?branch=master)](https://travis-ci.org/benjaminhirsch/azure-search-php)
[![Coverage Status](https://coveralls.io/repos/github/benjaminhirsch/azure-search-php/badge.svg?branch=master)](https://coveralls.io/github/benjaminhirsch/azure-search-php?branch=master)
[![Total Downloads](https://poser.pugx.org/benjaminhirsch/php-azure-search/downloads)](https://packagist.org/packages/benjaminhirsch/php-azure-search)
[![License](https://poser.pugx.org/benjaminhirsch/php-azure-search/license)](https://packagist.org/packages/benjaminhirsch/php-azure-search)

`benjaminhirsch/php-azure-search` is a simple php toolbox to interact with the Microsoft Azure Search Service REST API.

**Features:**
- Create, update and delete indexes including suggesters and corsOptions
- Create, update and delete all type of fields including collections
- List indexes
- Get index statistics
- Add, update and delete documents
- Search documents
- Get live suggestions
- Count documents

 **Upcomming Features**
 * Add scoring profiles

## Installation
The easiest way to get started is to install `benjaminhirsch/php-azure-search` via composer.
```bash
$ composer require benjaminhirsch/php-azure-search
```
---

### Initalize
You get your credentials `$azure_url`, `$azure_admin_key` and `$azure_version` in your Microsoft Azure portal under "Search Services".
```php
$azuresearch = new BenjaminHirsch\Azure\Search\Service(azure_url, azure_admin_key, azure_version);
```

### Create a Index
At first you have to create a index `BenjaminHirsch\Azure\Search\Index` in which you have to store your documents later. Your index can be filled with as many fields as you want. Adding a suggester is optional but required if you want to use live search (suggestions).

```php
$index = new BenjaminHirsch\Azure\Search\Index('name of your index');
$index->addField(new BenjaminHirsch\Azure\Search\Index\Field('field name 1', BenjaminHirsch\Azure\Search\Index\Field::TYPE_STRING, true))
       ->addField(new BenjaminHirsch\Azure\Search\Index\Field('field name 2', BenjaminHirsch\Azure\Search\Index\Field::TYPE_STRING))
       ->addSuggesters(new BenjaminHirsch\Azure\Search\Index\Suggest('livesearch', ['field name(s)']));

$azuresearch->createIndex($index);
```

### Delete a index
Deletes the complete index from Azure. Deleting a index also deletes the documents stored in the index.
```php
$azuresearch->deleteIndex('name of the index to delete');
```

### Upload documents
After you have created a index, you are ready to fill the index with your data. Maximum array size per request (1000).
```php
$data['value'][] = [
    '@search.action' => BenjaminHirsch\Azure\Search\Index::ACTION_UPLOAD,
    'field name 1' => <your value for field name 1>,
    'field name 2' => <your value for field name 2>
];

$azuresearch->uploadToIndex('name of your index', $data);
```

### Live search (suggestions)
```php
$azuresearch->suggestions('name of your index', 'your term', 'livesearch')
```

### Search documents
```php
$azuresearch->search('name of your index', 'your term');
```